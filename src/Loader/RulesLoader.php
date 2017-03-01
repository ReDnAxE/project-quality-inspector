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
    const APPLICATION_ALL = 'all';

    /**
     * @param string $configFile
     * @param string $applicationType
     * @param string $basePath
     * @return bool|RuleFilterIterator
     */
    public function load($configFile, $applicationType, $baseDir)
    {
        try {
            $configs = Yaml::parse(file_get_contents($configFile));
        } catch (ParseException $e) {
            printf("Unable to parse the YAML string in file %s: %s", $configFile, $e->getMessage());

            return false;
        }

        $existingRules = $this->getExistingRules();

        $config = [];
        if (isset($configs[$applicationType])) {
            $config = $configs[$applicationType];
        }

        if (isset($configs[$this::APPLICATION_ALL])) {
            $config = array_merge_recursive($config, $configs[$this::APPLICATION_ALL]);
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