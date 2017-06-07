<?php
/*
 * This file is part of project-quality-inspector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityInspector\Command;

use ProjectQualityInspector\Application\Output\UIHelper;
use ProjectQualityInspector\Application\Output\JunitHelper;
use ProjectQualityInspector\Application\Output\HtmlReportHelper;
use ProjectQualityInspector\Exception\RuleViolationException;
use ProjectQualityInspector\Loader\RulesLoader;
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
    const SUCCESS_EXIT   = 0;
    const FAILURE_EXIT   = 1;

    protected function configure()
    {
        $this->setName('run')
            ->setDescription('The Project quality tool')
            ->addArgument('applicationType', InputArgument::REQUIRED, 'The application type (symfony or angularjs, etc...)')
            ->addOption('rules', '-r', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'select rules to load')
            ->addOption('baseDir', '-b', InputOption::VALUE_REQUIRED, 'Change the base directory of application')
            ->addOption('configFile', '-c', InputOption::VALUE_REQUIRED, 'Change the base directory of application')
            ->addOption('junitFile', '-j', InputOption::VALUE_REQUIRED, 'Generate a JUnit file')
            ->addOption('htmlFile', '-f', InputOption::VALUE_REQUIRED, 'Generate a HTML file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $exitCode = $this::SUCCESS_EXIT;

        $applicationType = $input->getArgument('applicationType');
        $baseDir = $this->resolveBaseDirOption($input);

        //TODO: check if absolute baseDir
        $configFile = $input->getOption('configFile') ? getcwd() . '/' . $input->getOption('configFile') : $this->findConfigFile();
        //TODO: check if absolute baseDir
        $junitFile = $input->getOption('junitFile') ? getcwd() . '/' . $input->getOption('junitFile') : null;
        $htmlFile = $input->getOption('htmlFile') ? getcwd() . '/' . $input->getOption('htmlFile') : null;

        $rulesLoader = new RulesLoader();

        try {
            $rules = $rulesLoader->load($configFile, $applicationType, $baseDir, $input->getOption('rules'));
        } catch (\InvalidArgumentException $e) {
            UIHelper::displayException($e, $output);

            return $this::FAILURE_EXIT;
        }

        UIHelper::displayStartingBlock($output, $configFile, $this->getApplication()->getVersion());

        foreach ($rules as $rule) {
            try {
                $rule->evaluate();
                UIHelper::displayRuleSuccess($rule, $output);
            } catch (RuleViolationException $e) {
                UIHelper::displayRuleViolation($e, $output);
            } catch (\InvalidArgumentException $e) {
                UIHelper::displayException($e, $output);
            }
        }

        if ($junitFile) {
            JunitHelper::generateJunitFile($rules, $junitFile);
        }

        if ($htmlFile) {
            HtmlReportHelper::generateHtmlFile($rules, $htmlFile);
        }

        return $exitCode;
    }

    /**
     * @return string
     */
    protected function findConfigFile()
    {
        $configsToSearch = [
            getcwd() . '/pqi.yml',
            __DIR__ . '/../../pqi.yml'
        ];

        return (file_exists($configsToSearch[0])) ? $configsToSearch[0] : $configsToSearch[1];
    }

    /**
     * @param InputInterface $input
     * @return string
     */
    protected function resolveBaseDirOption(InputInterface $input)
    { 
        //TODO: check if absolute baseDir
        $baseDir = $input->getOption('baseDir') ? getcwd() . DIRECTORY_SEPARATOR . $input->getOption('baseDir') : getcwd();

        return $baseDir;
    }
}