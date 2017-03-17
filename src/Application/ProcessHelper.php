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

class ProcessHelper
{
    /**
     * @param string $command
     * @param string $baseDir
     * @param boolean $allowErrorExitCode
     *
     * @return array
     *
     * @throws \RuntimeException
     */
    public static function execute($command, $baseDir, $allowErrorExitCode = false)
    {
        $command = 'cd ' . escapeshellarg($baseDir) . '; ' . $command . ' 2>&1';

        if (DIRECTORY_SEPARATOR == '/') {
            $command = 'LC_ALL=en_US.UTF-8 ' . $command;
        }
        exec($command, $output, $returnValue);
        if ($returnValue !== 0 && !$allowErrorExitCode) {
            throw new \RuntimeException(implode("\r\n", $output));
        }
        return $output;
    }
}