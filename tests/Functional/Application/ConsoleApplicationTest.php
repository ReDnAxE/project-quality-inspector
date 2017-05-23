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

    protected $isSymfony2 = null;

    public static function setUpBeforeClass()
    {
        ProcessHelper::execute('rm -rf .git', __DIR__ . '/../../Fixtures/FakeProject');
        ProcessHelper::execute('cp -rp git .git', __DIR__ . '/../../Fixtures/FakeProject');
    }

    public static function tearDownAfterClass()
    {
        ProcessHelper::execute('rm -rf .git', __DIR__ . '/../../Fixtures/FakeProject');
    }

    protected function setUp()
    {
        $consoleApplication = new ConsoleApplication();
        $this->application = $consoleApplication->getApplication();
        $this->application->setAutoExit(false);
        $this->applicationTester = new ApplicationTester($this->application);

        if ($this->isSymfony2 === null) {
            $this->isSymfony2 = $this->isSymfonyConsole2();
        }
    }

    public function testFileRule()
    {
        $input = [];

        if ($this->isSymfony2) {
            $input = ['command' => 'run'];
        }

        $input = $input + [
            'applicationType' => 'symfony',
            '--rules' => ['files-rule'],
            '--baseDir' => 'tests/Fixtures/FakeProject',
            '--configFile' => 'tests/Fixtures/FakeProject/pqi.yml'
        ];


        $this->applicationTester->run($input);
        $display = $this->applicationTester->getDisplay();
        $results = explode(PHP_EOL, $display);

        $this->assertEquals(1, $this->applicationTester->getStatusCode());
        $this->assertEquals(9, count($results));
        $this->assertStringStartsWith(sprintf('Starting Project Quality Inspector v%s', $this->application->getVersion()), $results[0]);
        $this->assertEquals('files-rule: KO', $results[1]);
        $this->assertStringEndsWith('web/app_*.php" should not exists.', $results[2]);
        $this->assertStringEndsWith('phpunit.xml" should exists. Reason: This file is required for testing code', $results[3]);
        $this->assertStringEndsWith('docker-compose-prod.yml" should contain "version: \'3\'" string.', $results[4]);
        $this->assertStringEndsWith('README.md" should not contain "Standard Edition" string. Reason: You should personalize the README.md file', $results[5]);
        $this->assertStringEndsWith('tests/" should contain "test" string.', $results[6]);
        $this->assertStringEndsWith('.gitignore" should exists.', $results[7]);
        $this->assertEmpty($results[8]);
    }

    public function testComposerRule()
    {
        $input = [];

        if ($this->isSymfony2) {
            $input = ['command' => 'run'];
        }

        $input = $input + [
            'applicationType' => 'symfony',
            '--rules' => ['composer-config-rule'],
            '--baseDir' => 'tests/Fixtures/FakeProject',
            '--configFile' => 'tests/Fixtures/FakeProject/pqi.yml'
        ];

        $this->applicationTester->run($input);
        $display = $this->applicationTester->getDisplay();
        $results = explode(PHP_EOL, $display);

        $this->assertEquals(1, $this->applicationTester->getStatusCode());
        $this->assertEquals(7, count($results));
        $this->assertStringStartsWith(sprintf('Starting Project Quality Inspector v%s', $this->application->getVersion()), $results[0]);
        $this->assertEquals('composer-config-rule: KO', $results[1]);
        $this->assertStringEndsWith('Package "symfony/phpunit-bridge" should be installed.', $results[2]);
        $this->assertStringEndsWith('Package "bruli/php-git-hooks" should be installed.', $results[3]);
        $this->assertStringEndsWith('Installed package "symfony/console v3.2.6" should satisfies this expected semver "^1.1.1".', $results[4]);
        $this->assertStringEndsWith('Requirement "ext-dom" should contains at least major explicit version. Version "*" is not authorized.', $results[5]);
        $this->assertEmpty($results[6]);
    }

    public function testGitRule()
    {
        $input = [];

        if ($this->isSymfony2) {
            $input = ['command' => 'run'];
        }

        $input = $input + [
            'applicationType' => 'symfony',
            '--rules' => ['git-rule'],
            '--baseDir' => 'tests/Fixtures/FakeProject',
            '--configFile' => 'tests/Fixtures/FakeProject/pqi.yml'
        ];

        $this->applicationTester->run($input);
        $display = $this->applicationTester->getDisplay();
        $results = explode(PHP_EOL, $display);

        $this->assertEquals(0, $this->applicationTester->getStatusCode());

        $this->markTestIncomplete();
    }

    protected function isSymfonyConsole2()
    {
        $result = ProcessHelper::execute('./pqi run', __DIR__ . '/../../../', true);

        return (strpos($result[0], 'Error: application type "run" does not exists in config file') === false) ? true : false;
    }
}