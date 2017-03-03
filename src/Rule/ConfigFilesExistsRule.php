<?php
/*
 * This file is part of project-quality-detector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityDetector\Rule;

use ProjectQualityDetector\Exception\ExpectationFailedException;

/**
 * Class ConfigFilesRuleInterface
 *
 * @package ProjectQualityDetector\Rule
 */
class ConfigFilesExistsRule extends AbstractRule
{
    public function __construct(array $config, $basePath)
    {
        parent::__construct($config, $basePath);
    }

    /**
     * @inheritdoc
     */
    public function evaluate()
    {
        $expectationsFailedExceptions = [];
        foreach ($this->config as $fileConf) {
            try {
                $this->expectsFileExists($fileConf);
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
     * @param string|array $fileConf
     */
    protected function expectsFileExists($fileConf)
    {
        $fileName = $fileConf;
        $reason = '';

        if (is_array($fileConf)) {
            $fileName = $fileConf['filename'];
            $reason = $fileConf['reason'];
        }

        $message = sprintf('file "%s" does not exists', $fileName);

        if (!file_exists($fileName)) {
            throw new ExpectationFailedException($fileName, $message, $reason);
        }
    }
}