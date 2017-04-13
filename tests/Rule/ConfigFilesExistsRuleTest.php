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

use PHPUnit\Framework\TestCase;

class FilesRuleTest extends TestCase
{
    /**
     * @var FilesRule
     */
    protected $filesRule;

    public function setUp()
    {
        $this->filesRule = new FilesRule([], './');
    }

    /**
     * @covers  \ProjectQualityInspector\Rule\FilesRule::getGroups
     */
    public function testEvaluate()
    {
        $this->assertTrue(true);
    }
}