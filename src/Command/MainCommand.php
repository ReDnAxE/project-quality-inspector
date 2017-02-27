<?php
/*
 * This file is part of carma-quality-detector.
 *
 * (c) Alexandre GESLIN <alexandre.geslin@external.grdf.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CarmaQualityDetector\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Main command
 */
class MainCommand extends Command
{
    protected function configure()
    {
        $this->setName('CARMA quality tool')
            ->setDescription('The CARMA quality tool');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('test');
    }
}