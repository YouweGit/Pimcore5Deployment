<?php

namespace Pimcore5\DeploymentBundle\Command;

use Pimcore\Console\AbstractCommand;
use Pimcore\Console\Dumper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pimcore5\DeploymentBundle\Config\Config;

class ClitestCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('pc5deploy:clitest')
            ->setDescription('cli test Awesome command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // dump
        $this->dump("Isn't that awesome?");

        // add newlines through flags
        $this->dump("Dump #2", Dumper::NEWLINE_BEFORE | Dumper::NEWLINE_AFTER);

        // only dump in verbose mode
        $this->dumpVerbose("Dump verbose", Dumper::NEWLINE_BEFORE);

        echo ' == Config test: == ' . var_export($this->getContainer()->getParameter('pimcore5_deployment'),1). ' == ';


    }
}
