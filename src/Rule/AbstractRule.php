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
 * Class AbstractRule
 *
 * @package ProjectQualityDetector\Rule
 */
abstract class AbstractRule implements RuleInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $baseDir;

    /**
     * @var array
     */
    protected $failures;

    /**
     * @var string
     */
    protected $explaination;

    /**
     * @inheritdoc
     */
    public function __construct(array $config, $baseDir)
    {
        $this->config = $config;
        $this->failures = array();
        $this->baseDir = $baseDir;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @inheritdoc
     */
    public function getExplaination()
    {
        return $this->explaination;
    }

    /**
     * @inheritdoc
     */
    public function setExplaination($explaination)
    {
        $this->explaination = $explaination;
    }

    /**
     * @inheritdoc
     */
    public static function getGroups()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getRuleName()
    {
        $path = explode('\\', static::class);
        $name = array_pop($path);
        $name = preg_replace('#([A-Z])#', '-$1', lcfirst($name));
        $name = strtolower($name);

        return $name;
    }

    /**
     * @return array
     */
    protected function getFailuresDescription()
    {
        return $this->failures;
    }

    /**
     * @param array $expectationFailedExceptions
     */
    protected function throwRuleViolationException(array $expectationFailedExceptions)
    {
        throw new RuleViolationException($this, $expectationFailedExceptions);
    }
}