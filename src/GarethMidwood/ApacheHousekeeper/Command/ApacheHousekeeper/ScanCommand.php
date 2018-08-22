<?php

namespace GarethMidwood\ApacheHousekeeper\Command\ApacheHousekeeper;

use GarethMidwood\ApacheHousekeeper\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class ScanCommand extends BaseCommand
{
    private $sitesRunning;
    private $sitesStopped;

    protected function configure()
    {
        $this->setName('scan');
        $this->setDescription('Scans setup and returns info on sites that are running - and those that aren\'t');
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

        $availablePath = $this->_config->get('available-path', '/etc/apache2/sites-available');
        $enabledPath = $this->_config->get('enabled-path', '/etc/apache2/sites-enabled');
        $configFileExtension = $this->_config->get('config-extension', 'conf');

        $this->sitesAvailable = $this->getSitesByDirectory($availablePath, $configFileExtension);
        $this->sitesRunning = $this->getSitesByDirectory($enabledPath, $configFileExtension);
        $this->sitesStopped = array_diff($this->sitesAvailable, $this->sitesRunning);

        $this->populateAccessDatesForRunningSites();
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

        echo "Cut off date is: " . $cutoffDateTime->format("F d Y H:i:s.") . PHP_EOL;

        foreach($this->sitesRunning as $site) {
            $config = file_get_contents($site);

            preg_match_all('/DocumentRoot ["\']*(?<path>.+)["\']*/', $config, $pathMatches);
            preg_match_all('/DirectoryIndex ["\']*(?<path>[A-Za-z0-9 -.]+)["\']*/', $config, $directoryIndexMatches);

            $indexes = [];

            if (count($directoryIndexMatches['path'])) {
                foreach($directoryIndexMatches['path'] as $directoryIndexes) {
                    $indexes = array_merge($indexes, explode(' ', $directoryIndexes));
                }
            } else {
                // default to just index.php
                $indexes = ['index.php'];
            }

            foreach($pathMatches['path'] as $path) {
                $foundAFile = false;

                foreach($indexes as $index) {
                    $filename = $path . DIRECTORY_SEPARATOR . $index;

                    if (file_exists($filename)) {
                        // check the last access date of the file and decide whether it's being used
                        $foundAFile = true;
                        $accessedTime = fileatime($filename);
                        $accessedDateTime = new \DateTime();
                        $accessedDateTime->setTimestamp($accessedTime);
                        $interval = date_diff($accessedDateTime, $nowDateTime);

                        if ($interval->format('%a') > $cutoffDays) {
                            echo "$filename was not accessed recently. Last access was " . $interval->format('%a') . ' days ago' . PHP_EOL;
                        }
                    }
                }

                if (!$foundAFile) {
                    echo "Could not find files [" . implode(',', $indexes) . "] in $path" . PHP_EOL;
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
}
