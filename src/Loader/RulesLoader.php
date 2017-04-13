<?php
/*
 * This file is part of project-quality-inspector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityInspector\Loader;

use ProjectQualityInspector\Iterator\RuleFilterIterator;
use ProjectQualityInspector\Rule\ComposerConfigRule;
use ProjectQualityInspector\Rule\FilesRule;
use ProjectQualityInspector\Rule\GitRule;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class RulesLoader
 *
 * @package ProjectQualityInspector\Loader
 */
class RulesLoader
{
    const COMMON_RULES = 'common';

    /**
     * @param string $configFile
     * @param string $applicationType
     * @param string $baseDir
     * @param string $baseDir
     * @return array $ruleNames
     *
     * @throws \InvalidArgumentException
     */
    public function load($configFile, $applicationType, $baseDir, array $ruleNames = [])
    {
        $configs = $this->parseFileContent($configFile);
        $existingRules = $this->getExistingRules();

        if (!isset($configs[$applicationType])) {
            throw new \InvalidArgumentException(sprintf('application type "%s" does not exists in config file.', $applicationType));
        }

        $config = $configs[$applicationType];

        if (isset($configs[$this::COMMON_RULES])) {
            $config = array_merge_recursive($config, $configs[$this::COMMON_RULES]); //TODO: test if array_merge do not only concatenate arrays and sub arrays
        }

        $rules = new \ArrayIterator();
        foreach ($config as $ruleName => $ruleConfig) {
            if (key_exists($ruleName, $existingRules)) { //TODO delete existingRules, and try to instanciate class with sanitize names in CamelCases
                $rules[] = new $existingRules[$ruleName]($ruleConfig['config'], $baseDir);
            }
        }

        return new RuleFilterIterator($rules, $ruleNames);
    }

    /**
     * @param $configFile
     * @return array
     */
    protected function parseFileContent($configFile)
    {
        if (!file_exists($configFile)) {
            throw new \InvalidArgumentException(sprintf('config file "%s" not found.', $configFile));
        }

        try {
            $configs = Yaml::parse(file_get_contents($configFile)); //TODO: change this deprecated method call
        } catch (ParseException $e) {
            throw new \InvalidArgumentException(sprintf("unable to parse the YAML string in file %s: %s", $configFile, $e->getMessage()));
        }

        return $configs;
    }

    /**
     * @return array
     */
    protected function getExistingRules()
    {
        return [
            call_user_func(FilesRule::class . '::getRuleName') => FilesRule::class,
            call_user_func(ComposerConfigRule::class . '::getRuleName') => ComposerConfigRule::class,
            call_user_func(GitRule::class . '::getRuleName') => GitRule::class,
        ];
    }
}