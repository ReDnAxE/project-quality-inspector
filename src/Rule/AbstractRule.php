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
use ProjectQualityInspector\Exception\ExpectationFailedException;

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
     * @var array
     */
    protected $assertions = [];

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
     * @return array
     */
    public function getAssertions()
    {
        return $this->assertions;
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

    /**
     * @param string  $name
     * @param array  $failure
     * @param integer $assertions
     * @param integer $time
     * @param array  $error
     */
    protected function addAssertion($name, array $failures = [], $assertions = 1, $time = 0, array $errors = [])
    {
        $this->assertions[] = [
            'name' => $name,
            'assertions' => $assertions,
            'time' => $time,
            'failures' => [
                'sum' => count($failures),
                'list' => ((count($failures)) ? [['message' => $failures[0]['message'], 'type' => $failures[0]['type']]] : []) //TODO all failures
            ],
            'errors' => [
                'sum' => count($errors),
                'list' => ((count($error)) ? [['message' => $errors[0]['message'], 'type' => $errors[0]['type']]] : []) //TODO all errors
            ],
        ];
    }
}