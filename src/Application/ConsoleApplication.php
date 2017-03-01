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

use Symfony\Component\Console\Application;
use ProjectQualityDetector\Command\MainCommand;

/**
 * A Quality runner for the Command Line Interface (CLI)
 * PHP SAPI Module.
 */
class ConsoleApplication
{
    public static function main()
    {
        $command = new static;

        return $command->run();
    }

    /**
     * @return int
     */
    public function run()
    {
        $application = new Application('echo', '1.0.0');
        $command = new MainCommand();
        $application->add($command);
        $application->setDefaultCommand($command->getName(), true);

        return $application->run();
    }
}