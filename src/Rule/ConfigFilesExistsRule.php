<?php
/*
 * This file is part of project-quality-inspector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityInspector\Rule;

use ProjectQualityInspector\Exception\ExpectationFailedException;

/**
 * Class ConfigFilesRuleInterface
 *
 * @package ProjectQualityInspector\Rule
 */
class ConfigFilesExistsRule extends AbstractRule
{
    public function __construct(array $config, $baseDir)
    {
        parent::__construct($config, $baseDir);
    }

    /**
     * @inheritdoc
     */
    public function evaluate()
    {
        $expectationsFailedExceptions = [];

        foreach ($this->config as $fileConf) {
            try {
                $this->expectsFileExists($fileConf, $this->baseDir);
            } catch (ExpectationFailedException $e) {
                $expectationsFailedExceptions[] = $e;
            }
        }

        if (count($expectationsFailedExceptions)) {
            $this->throwRuleViolationException($expectationsFailedExceptions);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getGroups()
    {
        return array_merge(parent::getGroups(), ['config']);
    }

    /**
     * @param $fileConf
     * @param $baseDir
     */
    protected function expectsFileExists($fileConf, $baseDir)
    {
        $fileName = $this->getValue($fileConf);
        $reason = $this->getReason($fileConf);

        $filePath = $baseDir . DIRECTORY_SEPARATOR . $fileName;

        if ($fileConf[0] == '!') {
            $fileName = ltrim($fileName, '!');
            $filePath = $baseDir . DIRECTORY_SEPARATOR . $fileName;
            $this->globShouldNotFind($filePath, $reason);
        } else {
            $this->globShouldFind($filePath, $reason);
        }
    }

    /**
     * @param $filePath
     * @param $reason
     *
     * @throws ExpectationFailedException
     */
    protected function globShouldFind($filePath, $reason)
    {
        $message = sprintf('file "%s" should exists', $filePath);

        if (!count(glob($filePath))) {
            throw new ExpectationFailedException($filePath, $message, $reason);
        }
    }

    /**
     * @param $filePath
     * @param $reason
     *
     * @throws ExpectationFailedException
     */
    protected function globShouldNotFind($filePath, $reason)
    {
        $message = sprintf('file "%s" should not exists', $filePath);

        if (count(glob($filePath))) {
            throw new ExpectationFailedException($filePath, $message, $reason);
        }
    }
}