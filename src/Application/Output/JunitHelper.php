<?php
/*
 * This file is part of project-quality-inspector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityInspector\Application\Output;

use ProjectQualityInspector\Exception\RuleViolationException;
use ProjectQualityInspector\Iterator\RuleFilterIterator;

class JunitHelper
{
    /**
     * @param  RuleInterface[]  $rules
     * @param  string $junitFile
     */
    public static function generateJunitFile(RuleFilterIterator $rules, $junitFile)
    {
        $xml = new \DOMDocument('1.0', 'utf-8');
        $testSuites = $xml->createElement("testsuites");

        foreach ($rules as $rule) {
            $testSuites->appendChild(static::createTestSuite($rule::getRuleName(), $rule->getAssertions(), $xml));
        }

      $testSuites->setAttribute('name', 'pqi');
      $testSuites->setAttribute('errors', static::sumDomChildsAttribute($testSuites, 'errors'));
      $testSuites->setAttribute('failures', static::sumDomChildsAttribute($testSuites, 'failures'));
      $testSuites->setAttribute('time', static::sumDomChildsAttribute($testSuites, 'time'));

        $xml->appendChild($testSuites);

        file_put_contents($junitFile, $xml->saveXML());
    }

    /**
     * @param  array $tests
     * @param  DOMDocument $xml
     * @return DOMDocument
     */
    private static function createTestSuite($name, array $tests, \DOMDocument $xml)
    {
        $testSuite = static::createElement('testsuite', [
            'name' => $name,
            'tests' => count($tests),
            'failures' => static::sumArraysKey('failures', $tests, 'sum'),
            'errors' => static::sumArraysKey('errors', $tests, 'sum'),
            'time' => static::sumArraysKey('time', $tests)
        ], $xml);

        foreach ($tests as $test) {
            $testSuite->appendChild(static::createTestCase($test, $xml));
        }

        return $testSuite;
    }

    private static function createTestCase(array $test, \DOMDocument $xml)
    {
        $testCase = $xml->createElement('testcase');
        $testCase->setAttribute('name', $test['name']);
        $testCase->setAttribute('assertions', $test['assertions']);
        $testCase->setAttribute('classname', $test['classname']);
        $testCase->setAttribute('status', $test['status']);
        $testCase->setAttribute('time', $test['time']);

        if ($test['failures']['sum'] > 0) {
            foreach ($test['failures']['list']  as $failure) {
                $testCase->appendChild(static::createElement('failure', ['type' => $failure['type']], $xml, $failure['message']));
            }
        }

        if ($test['errors']['sum'] > 0) {
            foreach ($test['errors']['list'] as $error) {
                $testCase->appendChild(static::createElement('error', ['type' => $error['type']], $xml, $failure['message']));
            }
        }

        return $testCase;
    }

    /**
     * @param  string $tagName
     * @param  array $attributes
     * @param  \DOMDocument $xml
     * @param  string $value
     * @return \DOMDocument
     */
    private static function createElement($tagName, array $attributes, \DOMDocument $xml, $value = null)
    {
        $element = $xml->createElement($tagName, $value);
        
        foreach ($attributes as $key => $value) {
            $element->setAttribute($key, $value);
        }

        return $element;
    }

    /**
     * @param  string $key
     * @param  array  $arrays
     * @param  string  $subKey
     * @return integer
     */
    private static function sumArraysKey($key, array $arrays, $subKey = null)
    {
        $sum = 0;

        foreach ($arrays as $array) {
            $sum += ($subKey) ? $array[$key][$subKey] : $array[$key];
        }

        return $sum;
    }

    /**
     * @param  \DOMElement $element
     * @param  string $attribute
     * @return integer
     */
    private static function sumDomChildsAttribute(\DOMElement $element, $attribute)
    {
        $sum = 0;
        foreach ($element->childNodes as $childElement) {
            $sum += (int) $childElement->getAttribute($attribute);
        }

        return $sum;
    }
}