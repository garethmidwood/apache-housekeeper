<?php

namespace GarethMidwood\ApacheHousekeeper\Command\ApacheHousekeeper;

use GarethMidwood\ApacheHousekeeper\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        // TODO: Scan directory and return results in array
        $this->sitesRunning = ['ait.uat.creode.co.uk', 'cqf.uat.creode.co.uk', 'farmatrust.uat.creode.co.uk'];
        $this->sitesStopped = ['allroundercricket.uat.creode.co.uk', 'basis.uat.creode.co.uk'];
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
