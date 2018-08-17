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
        $finder->files()->name('*.' . $configFileExtension)->in($path);

        foreach ($finder as $file) {
            // filename, with the extension removed
            $results[] = substr(
                $file->getRelativePathname(),
                0,
                -(strlen($configFileExtension)+1)
            );
        }

        return $results;
    }

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
