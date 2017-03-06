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

use ProjectQualityInspector\Exception\ExpectationFailedException;

/**
 * Class ComposerConfigRule
 *
 * @package ProjectQualityInspector\Rule
 */
class ComposerConfigRule extends AbstractRule
{
    public function __construct(array $config, $baseDir)
    {
        parent::__construct($config, $baseDir);
    }

    /**
     * @inheritdoc
     */
    public function evaluate()
    {
        $expectationsFailedExceptions = [];
        $composerConfig = $this->getComposerConfig();
        $requirements = $composerConfig['require'];

        if ($composerConfig['require-dev']) {
            $requirements = array_merge($composerConfig['require'], $composerConfig['require-dev']);
        }

        foreach ($this->config['packages'] as $package) {
            try {
                $this->expectsPackagePresence($package, $requirements);

                if ($this->config['disallow-wildcard-versioning']) {
                    $this->expectsRequirementsHasNoWildCard($requirements);
                }
            } catch (ExpectationFailedException $e) {
                $expectationsFailedExceptions[] = $e;
            }
        }

        if (count($expectationsFailedExceptions)) {
            $this->throwRuleViolationException($expectationsFailedExceptions);
        }
    }

    /**
     * @inheritdoc
     */
    public static function getGroups()
    {
        return array_merge(parent::getGroups(), ['config']);
    }

    /**
     * @param array $requirements
     *
     * @throws ExpectationFailedException
     */
    protected function expectsRequirementsHasNoWildCard(array $requirements)
    {
        foreach ($requirements as $requirement => $version) {
            $message = sprintf('Requirement "%s" should contains at least major version. Version "%s" is not authorized', $requirement, $version);
            if (!preg_match('/\\d/', $version)) {
                throw new ExpectationFailedException($requirement, $message);
            }
        }
    }

    /**
     * @param string|array $raw
     * @param array $requirements
     */
    protected function expectsPackagePresence($raw, $requirements)
    {
        $package = $this->getValue($raw);
        $reason = $this->getReason($raw);

        if ($package[0] == '!') {
            $package = ltrim($package, '!');
            $this->packageShouldNotExists($package, $requirements, $reason);
        } else {
            $this->packageShouldExists($package, $requirements, $reason);
        }
    }

    /**
     * @param $package
     * @param $requirements
     * @param $reason
     */
    protected function packageShouldExists($package, $requirements, $reason)
    {
        $message = sprintf('Package "%s" should be installed', $package);

        if (!key_exists($package, $requirements)) {
            throw new ExpectationFailedException($package, $message, $reason);
        }
    }

    /**
     * @param $package
     * @param $requirements
     * @param $reason
     */
    protected function packageShouldNotExists($package, $requirements, $reason)
    {
        $message = sprintf('Package "%s" should not be used', $package);

        if (key_exists($package, $requirements)) {
            throw new ExpectationFailedException($package, $message, $reason);
        }
    }

    /**
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function getComposerConfig()
    {
        $configFile = $this->baseDir . DIRECTORY_SEPARATOR . $this->config['file'];
        if (!file_exists($configFile)) {
            throw new \InvalidArgumentException(sprintf('config file "%s" not found.', $configFile));
        }

        return json_decode(file_get_contents($configFile), true);
    }
}