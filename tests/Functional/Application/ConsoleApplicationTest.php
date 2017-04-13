<?php
/*
 * This file is part of project-quality-inspector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityInspector\Application;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

class ConsoleApplicationTest extends TestCase
{
    /**
     * Symfony\Component\Console\Application
     */
    protected $application;

    /**
     * ApplicationTester
     */
    protected $applicationTester;

    public static function setUpBeforeClass()
    {
        @rename(__DIR__ . '/../../Fixtures/FakeProject/git', __DIR__ . '/../../Fixtures/FakeProject/.git');
    }

    public static function setUpAfterClass()
    {
        @rename(__DIR__ . '/../../Fixtures/FakeProject/.git', __DIR__ . '/../../Fixtures/FakeProject/git');
    }

    protected function setUp()
    {
        $consoleApplication = new ConsoleApplication();
        $this->application = $consoleApplication->getApplication();
        $this->application->setAutoExit(false);
        $this->applicationTester = new ApplicationTester($this->application);
    }

    public function testFileRule()
    {
        $this->applicationTester->run([
            'applicationType' => 'symfony',
            '--rules' => ['files-rule'],
            '--baseDir' => 'tests/Fixtures/FakeProject',
            '--configFile' => 'tests/Fixtures/FakeProject/pqi.yml'
        ], []);
        $display = $this->applicationTester->getDisplay();
        $results = explode(PHP_EOL, $display);

        $this->assertEquals(1, $this->applicationTester->getStatusCode());
        $this->assertEquals(8, count($results));
        $this->assertStringStartsWith(sprintf('Starting Project Quality Inspector v%s', $this->application->getVersion()), $results[0]);
        $this->assertEquals('files-rule: KO', $results[1]);
        $this->assertStringEndsWith('web/app_*.php" should not exists.', $results[2]);
        $this->assertStringEndsWith('phpunit.xml" should exists. Reason: This file is required for testing code', $results[3]);
        $this->assertStringEndsWith('docker-compose-prod.yml" should contains "version: \'3\'" string.', $results[4]);
        $this->assertStringEndsWith('README.md" should not contains "Standard Edition" string. Reason: You should personalize the README.md file', $results[5]);
        $this->assertStringEndsWith('.gitignore" should exists.', $results[6]);
        $this->assertEmpty($results[7]);
    }

    public function testComposerRule()
    {
        $this->applicationTester->run([
            'applicationType' => 'symfony',
            '--rules' => ['composer-config-rule'],
            '--baseDir' => 'tests/Fixtures/FakeProject',
            '--configFile' => 'tests/Fixtures/FakeProject/pqi.yml'
        ], []);
        $display = $this->applicationTester->getDisplay();
        $results = explode(PHP_EOL, $display);

        $this->assertEquals(1, $this->applicationTester->getStatusCode());
        $this->assertEquals(6, count($results));
        $this->assertStringStartsWith(sprintf('Starting Project Quality Inspector v%s', $this->application->getVersion()), $results[0]);
        $this->assertEquals('composer-config-rule: KO', $results[1]);
        $this->assertStringEndsWith('Package "symfony/phpunit-bridge" should be installed.', $results[2]);
        $this->assertStringEndsWith('Package "bruli/php-git-hooks" should be installed.', $results[3]);
        $this->assertStringEndsWith('Requirement "ext-dom" should contains at least major explicit version. Version "*" is not authorized.', $results[4]);
        $this->assertEmpty($results[5]);
    }

    public function testGitRule()
    {
        $this->applicationTester->run([
            'applicationType' => 'symfony',
            '--rules' => ['git-rule'],
            '--baseDir' => 'tests/Fixtures/FakeProject',
            '--configFile' => 'tests/Fixtures/FakeProject/pqi.yml'
        ], []);
        $display = $this->applicationTester->getDisplay();
        $results = explode(PHP_EOL, $display);

        print_r($results);

        $this->assertEquals(0, $this->applicationTester->getStatusCode());
    }
}