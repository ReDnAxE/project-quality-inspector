<?php
/*
 * This file is part of project-quality-detector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityDetector\Rule;

/**
 * Interface RuleInterface
 *
 * @package ProjectQualityDetector\Rule
 */
interface RuleInterface
{
    /**
     * RuleInterface constructor.
     *
     * @param array $config
     * @param string $baseDir
     */
    public function __construct(array $config, $baseDir);

    /**
     * @return mixed
     */
    public function evaluate();

    /**
     * @return array
     */
    public function getConfig();

    /**
     * @return string
     */
    public function getExplaination();

    /**
     * @param $explaination
     */
    public function setExplaination($explaination);

    /**
     * @return array
     */
    public static function getGroups();

    /**
     * @return string
     */
    public static function getRuleName();
}