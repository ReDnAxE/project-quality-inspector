<?php
/*
 * This file is part of project-quality-detector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityDetector\Loader;

use ProjectQualityDetector\Iterator\RuleFilterIterator;
use ProjectQualityDetector\Rule\ConfigFilesExistsRule;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class RulesLoader
 *
 * @package ProjectQualityDetector\Loader
 */
class RulesLoader
{
    const COMMON_RULES = 'common';

    /**
     * @param string $configFile
     * @param string $applicationType
     * @param string $baseDir
     * @return RuleFilterIterator
     *
     * @throws \InvalidArgumentException
     */
    public function load($configFile, $applicationType, $baseDir)
    {
        if (!file_exists($configFile)) {
            throw new \InvalidArgumentException(sprintf('config file "%s" not found.', $configFile));
        }

        try {
            $configs = Yaml::parse(file_get_contents($configFile));
        } catch (ParseException $e) {
            throw new \InvalidArgumentException(sprintf("unable to parse the YAML string in file %s: %s", $configFile, $e->getMessage()));
        }

        $existingRules = $this->getExistingRules();

        if (!isset($configs[$applicationType])) {
            throw new \InvalidArgumentException(sprintf('application type "%s" does not exists in config file.', $applicationType));
        }

        $config = $configs[$applicationType];

        if (isset($configs[$this::COMMON_RULES])) {
            $config = array_merge_recursive($config, $configs[$this::COMMON_RULES]);
        }

        $rules = new \ArrayIterator();
        foreach ($config as $ruleName => $ruleConfig) {
            if (key_exists($ruleName, $existingRules)) {
                $rules[] = new $existingRules[$ruleName]($ruleConfig, $baseDir);
            }
        }

        return new RuleFilterIterator($rules, []);
    }

    /**
     * @return array
     */
    protected function getExistingRules()
    {
        return [
            call_user_func(ConfigFilesExistsRule::class . '::getRuleName') => ConfigFilesExistsRule::class
        ];
    }
}