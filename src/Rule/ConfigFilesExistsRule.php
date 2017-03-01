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

use ProjectQualityDetector\Exception\RuleViolationException;

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
        $filesNotExists = [];
        foreach ($this->config as $file) {
            if (!file_exists($file)) {
                $filesNotExists[] = $file;
            }
        }

        if (count($filesNotExists)) {
            throw new RuleViolationException($this, sprintf('File(s) does not exists in project : %s', implode(', ', $filesNotExists)));
        }
    }

    /**
     * @inheritdoc
     */
    public static function getGroups()
    {
        return array_merge(parent::getGroups(), ['config']);
    }
}