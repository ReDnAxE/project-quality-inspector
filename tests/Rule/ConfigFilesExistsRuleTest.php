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

class ConfigFilesExistsRuleTest extends TestCase
{
    /**
     * @covers  \ProjectQualityInspector\Rule\ConfigFilesExistsRule::getGroups
     */
    public function testGetGroups()
    {
        $groups = ConfigFilesExistsRule::getGroups();
        $this->assertInternalType('array', $groups);
        $this->assertEquals($groups, ['config']);
    }
}