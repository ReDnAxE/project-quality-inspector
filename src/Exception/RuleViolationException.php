<?php
/*
 * This file is part of project-quality-inspector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityInspector\Exception;

use ProjectQualityInspector\Rule\RuleInterface;
use Exception;

/**
 * Class RuleViolationException
 *
*@package ProjectQualityInspector\Exception
 */
class RuleViolationException extends \RuntimeException
{

    /**
     * @var RuleInterface
     */
    protected $rule;

    /**
     * @var ExpectationFailedException[]
     */
    protected $expectationFailedExceptions;

    /**
     * RuleViolationException constructor.
     *
     * @param RuleInterface $rule
     * @param array $expectationFailedExceptions
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(RuleInterface $rule, array $expectationFailedExceptions, $code = 0, Exception $previous = null)
    {
        parent::__construct($rule::getRuleName() . ': KO', $code, $previous);

        $this->rule = $rule;
        $this->expectationFailedExceptions = $expectationFailedExceptions;
    }

    /**
     * @return RuleInterface
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @return ExpectationFailedException[]
     */
    public function getExpectationFailedExceptions()
    {
        return $this->expectationFailedExceptions;
    }
}