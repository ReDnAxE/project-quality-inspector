<?php
/*
 * This file is part of project-quality-detector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityDetector\Application;

use ProjectQualityDetector\Exception\RuleViolationException;
use ProjectQualityDetector\Rule\RuleInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UIHelper
{
    /**
     * @param OutputInterface $output
     * @param string $configFile
     */
    public static function displayStartingBlock(OutputInterface $output, $configFile)
    {
        $output->writeln(sprintf('<question>Starting Project Quality Detector with config file "%s"</question>', $configFile));
    }

    /**
     * @param RuleInterface $rule
     * @param OutputInterface $output
     */
    public static function displayRuleSuccess(RuleInterface $rule, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>%s: OK</info>', $rule::getRuleName()));
    }

    /**
     * @param RuleViolationException $e
     * @param OutputInterface $output
     */
    public static function displayRuleViolation(RuleViolationException $e, OutputInterface $output)
    {
        $rule = $e->getRule();
        $output->writeln(sprintf('<error>%s: KO</error>', $rule::getRuleName()));

        foreach ($e->getExpectationFailedExceptions() as $expectationFailedException) {
            $reason = ($expectationFailedException->getReason()) ? sprintf(' Reason: %s', $expectationFailedException->getReason()): '';
            $output->writeln(sprintf('<comment>Expectation failed: %s.%s</comment>', $expectationFailedException->getMessage(), $reason));
        }
    }

    /**
     * @param \Exception $e
     * @param OutputInterface $output
     */
    public static function displayException(\Exception $e, OutputInterface $output)
    {
        $output->writeln(sprintf('<error>Error: %s</error>', $e->getMessage()));
    }
}