<?php
/*
 * This file is part of project-quality-detector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityDetector\Exception;

use ProjectQualityDetector\Rule\RuleInterface;
use Exception;

/**
 * Class RuleViolationException
 *
*@package ProjectQualityDetector\Exception
 */
class RuleViolationException extends \RuntimeException
{

    /**
     * @var RuleInterface
     */
    protected $rule;

    /**
     * RuleViolationException constructor.
     *
     * @param RuleInterface $rule
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(RuleInterface $rule, $message = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($rule->getRuleName() . ': ' . $message, $code, $previous);

        $this->rule = $rule;
    }
}