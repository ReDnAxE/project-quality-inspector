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

use ProjectQualityInspector\Exception\RuleViolationException;

/**
 * Class AbstractRule
 *
 * @package ProjectQualityInspector\Rule
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
     * @inheritdoc
     */
    public function __construct(array $config, $baseDir)
    {
        $this->config = $config;
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
     * @param array|string $raw
     * @return array|string
     */
    protected function getValue($raw)
    {
        if (is_array($raw) && isset($raw['value'])) {
            return $raw['value'];
        }

        return $raw;
    }

    /**
     * @param array|string $raw
     * @return array|string|null
     */
    protected function getReason($raw)
    {
        if (is_array($raw) && isset($raw['reason'])) {
            return $raw['reason'];
        }

        return '';
    }

    /**
     * @param array $expectationFailedExceptions
     *
     * @throws RuleViolationException
     */
    protected function throwRuleViolationException(array $expectationFailedExceptions)
    {
        throw new RuleViolationException($this, $expectationFailedExceptions);
    }
}