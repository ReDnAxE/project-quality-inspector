<?php
/*
 * This file is part of carma-quality-detector.
 *
 * (c) Alexandre GESLIN <alexandre.geslin@external.grdf.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CarmaQualityDetector\Application;

use Symfony\Component\Console\Application;
use CarmaQualityDetector\Command\MainCommand;

/**
 * A Quality runner for the Command Line Interface (CLI)
 * PHP SAPI Module.
 */
class Command
{
    public static function main()
    {
        $command = new static;

        return $command->run([]);
    }

    /**
     * @param array $argv
     *
     * @return int
     */
    public function run(array $argv)
    {
        $application = new Application('echo', '1.0.0');
        $command = new MainCommand();
        $application->add($command);
        $application->setDefaultCommand($command->getName(), true);

        return $application->run();
    }
}