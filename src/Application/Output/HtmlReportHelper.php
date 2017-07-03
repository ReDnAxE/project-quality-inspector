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

class HtmlReportHelper
{
    /**
     * @var string
     */
    const PAGE_HEADER = <<<EOT
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <title>Test Documentation</title>
        <style>
            body {
                text-rendering: optimizeLegibility;
                font-variant-ligatures: common-ligatures;
                font-kerning: normal;
                margin-left: 2em;
                font-family: Verdana, Arial, sans-serif;
            }
            h2 {
                font-family: Tahoma, Helvetica, Arial;
                font-size: 1em;
            }
            ul {
                list-style: none;
                margin-bottom: 1em;
            }
            table {
                border-collapse: collapse;
                width: 100%;
            }
            table, th, td {
               border: 1px solid black;
               font-size: 0.95em;
            }
            th {
                height: 2em;
                text-align: center;
                background-color:#4CAF50;
                color:white;
            }
            td {
                padding: 0.4em;
            }
            tr:nth-child(even) {
                background-color: #f2f2f2
            }
        </style>
    </head>
    <body>
EOT;

    /**
     * @var string
     */
    const PAGE_FOOTER = <<<EOT
    </body>
</html>
EOT;

    /**
     * @param  RuleInterface[]  $rules
     * @param  string $htmlFile
     */
    public static function generateHtmlFile(RuleFilterIterator $rules, $htmlFile)
    {
        $html = self::PAGE_HEADER . '<div>
        <h1>PQI</h1>';

        foreach ($rules as $rule) {
            $html .= static::createTestSuite($rule::getRuleName(), $rule->getAssertions());
        }

        $html .= '</div>' . self::PAGE_FOOTER;

        file_put_contents($htmlFile, $html);
    }

    /**
     * @param  string $name
     * @param  array $tests
     * @return string
     */
    private static function createTestSuite($name, array $tests)
    {
        $testSuite = '<h2> '.strtoupper($name).', tests: '.count($tests).' failures: '.static::sumArraysKey('failures', $tests, 'sum').', errors: '.static::sumArraysKey('errors', $tests, 'sum').', time: '.static::sumArraysKey('time', $tests).'</h2>';

        $testSuite .= '<table class="testsuite">
        <tr>
            <th>Type</th>
            <th>message</th>
        </tr>';

        foreach ($tests as $test) {
            $testSuite .= static::createTestCase($test);
        }

        $testSuite .= '</table>';

        return $testSuite;
    }

    /**
     * @param  array $test
     * @return string
     */
    private static function createTestCase(array $test)
    {
        $testCase = '';

        if ($test['failures']['sum'] > 0) {
            foreach ($test['failures']['list']  as $failure) {
                $testCase .= '<tr class="failure">
                    <td>'.$failure['type'].'</td>
                    <td>'.self::sanitizeTags($failure['message']).'</td>
                </tr>';
            }
        }

        if ($test['errors']['sum'] > 0) {
            foreach ($test['errors']['list'] as $error) {
                $testCase .= '<tr class="error">
                    <td>'.$error['type'].'</td>
                    <td>'.self::sanitizeTags($error['message']).'</td>
                </tr>';
            }
        }

        return $testCase;
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

    private static function sanitizeTags($string)
    {
        $string = str_replace('<fg=green>', '<span style="color: green;">', $string);
        $string = str_replace('</>', '</span>', $string);
        $string = preg_replace('/\t+/', '<br />', $string);

        return $string;
    }
}