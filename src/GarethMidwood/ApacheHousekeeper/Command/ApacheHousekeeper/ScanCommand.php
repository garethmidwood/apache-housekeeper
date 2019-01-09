<?php

namespace GarethMidwood\ApacheHousekeeper\Command\ApacheHousekeeper;

use GarethMidwood\ApacheHousekeeper\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ScanCommand extends BaseCommand
{
    private $sitesAvailable;
    private $sitesRunning;
    private $sitesStopped;
    private $log = [];

    protected function configure()
    {
        $this->setName('scan');
        $this->setDescription('Scans setup and disables sites that have not been access (may require sudo access)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {   
        $this->scanDirectory();
        $this->respond();
    }

    private function scanDirectory()
    {
        $this->sitesRunning = [];
        $this->sitesAvailable = [];
        $this->sitesStopped = [];
        $this->log = [];

        $availablePath = $this->_config->get('available-path', '/etc/apache2/sites-available');
        $enabledPath = $this->_config->get('enabled-path', '/etc/apache2/sites-enabled');
        $configFileExtension = $this->_config->get('config-extension', 'conf');

        $this->sitesAvailable = $this->getSitesByDirectory($availablePath, $configFileExtension);
        $this->sitesRunning = $this->getSitesByDirectory($enabledPath, $configFileExtension);
        $this->sitesStopped = array_diff($this->sitesAvailable, $this->sitesRunning);

        $this->populateAccessDatesForRunningSites();

        $this->writeLog();
    }

    /**
     * Returns a list of the config files in the specified directory
     * @param string $path 
     * @param string $configFileExtension 
     * @return array
     */
    private function getSitesByDirectory($path, $configFileExtension)
    {
        $results = [];
        $finder = new Finder();
        $finder->files()->name('*.' . $configFileExtension)->in($path)->depth('== 0');

        foreach ($finder as $file) {
            // filename, with the extension removed
            $results[] = $file->getRealPath();
        }

        return $results;
    }

    /**
     * Finds access dates for each of the running sites in the scan results
     * @param string $path
     * @return void
     */
    private function populateAccessDatesForRunningSites() 
    {
        $cutoffDays = $this->_config->get('cutoff', '30');
        $cutoffDateTime = new \DateTime();
        $cutoffDateTime->setTimestamp(strtotime("-$cutoffDays day"));

        $nowDateTime = new \DateTime();

        $this->log("Cut off date is: " . $cutoffDateTime->format("F d Y H:i:s."));

        foreach($this->sitesRunning as $index => $site) {
            $config = file_get_contents($site);
            $sitename = basename($site);

            // TODO: Can this include error logs too?
            preg_match_all('/CustomLog ["\']*(?<path>[A-Za-z0-9 -.\/]+) [combined]*["\']*/', $config, $logMatches);

            $indexes = [];

            foreach($logMatches['path'] as $path) {
                if (!file_exists($path)) {
                    $this->log("Could not find $path for site $sitename");
                    continue;
                }

                // check the last access date of the file and decide whether it's being used
                $accessedTime = fileatime($path);
                $accessedDateTime = new \DateTime();
                $accessedDateTime->setTimestamp($accessedTime);
                $interval = date_diff($accessedDateTime, $nowDateTime);

                $lastAccessed = $interval->format('%a');

                if ($lastAccessed > $cutoffDays) {
                    $this->log("DISABLE: $sitename $path was NOT accessed recently. Last access was $lastAccessed days ago");

                    exec('a2dissite ' . $sitename);
                    unset($this->sitesRunning[$index]);
                    $this->sitesStopped[] = $site;
                } else {
                    $this->log("OK: $sitename $path was accessed recently. Last access was $lastAccessed days ago");
                }
            }
        }
    }

    /**
     * Send a successful response
     * @return void
     */
    private function respond() 
    {
        $this->sendSuccessResponse([
            'message' => 'Scan complete',
            'sites' => [
                'running' => $this->sitesRunning,
                'stopped' => $this->sitesStopped
            ]
        ]);
    }

    /**
     * Logs a message
     * @param string $message 
     * @return void
     */
    private function log($message)
    {
        $this->log[] = $message;
    }

    /**
     * Writes the log to file
     * @return void
     */
    private function writeLog()
    {
        $str = implode(PHP_EOL, $this->log);
        file_put_contents('scan.log', $str);
    }
}
