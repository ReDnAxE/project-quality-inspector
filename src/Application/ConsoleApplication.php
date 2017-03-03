<?php
/*
 * This file is part of project-quality-inspector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityInspector\Application;

use Symfony\Component\Console\Application;
use ProjectQualityInspector\Command\MainCommand;

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
        $application = new Application('pqi', '1.0.1');
        $command = new MainCommand();
        $application->add($command);
        $application->setDefaultCommand($command->getName(), true);

        return $application->run();
    }
}