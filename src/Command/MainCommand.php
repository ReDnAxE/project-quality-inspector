<?php
/*
 * This file is part of project-quality-detector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityDetector\Command;

use ProjectQualityDetector\Exception\RuleViolationException;
use ProjectQualityDetector\Loader\RulesLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Main command
 */
class MainCommand extends Command
{
    protected function configure()
    {
        $this->setName('pqd')
            ->setDescription('The Project quality tool')
            ->addArgument('applicationType', InputArgument::REQUIRED, 'The application type (symfony or angularjs, etc...)')
            ->addOption('baseDir', '-b', InputOption::VALUE_REQUIRED, 'Change the base directory of application')
            ->addOption('configFile', '-c', InputOption::VALUE_REQUIRED, 'Change the base directory of application');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $applicationType = $input->getArgument('applicationType');
        $baseDir = $input->getOption('baseDir') ? : getcwd(); //TODO: check
        $configFile = $input->getOption('configFile') ? $input->getOption('configFile') : $this->getConfigFile($baseDir); //TODO: check

        $rulesLoader = new RulesLoader();
        $rules = $rulesLoader->load($configFile, $applicationType, $baseDir);

        foreach ($rules as $rule) {
            try {
                $rule->evaluate();
            } catch (RuleViolationException $e) {
                //$this->fail($other, $description);
                echo $e->getMessage();
            }
        }

        return 0;
    }

    /**
     * @param $baseDir
     * @return string
     */
    protected function getConfigFile($baseDir)
    {
        $configsToSearch = [
            $baseDir . '/pqd.yml',
            __DIR__ . '/pqd.yml'
        ];

        return (file_exists($configsToSearch[0])) ? $configsToSearch[0] : $configsToSearch[1];
    }
}