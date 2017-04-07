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

/**
 * Class ExpectationFailedException
 *
 * @package ProjectQualityInspector\Exception
 */
class ExpectationFailedException extends \RuntimeException
{

    /**
     * @var mixed
     */
    protected $subject;

    /**
     * @var string
     */
    protected $reason;

    /**
     * ExpectationFailedException constructor.
     *
     * @param mixed $subject
     * @param string $message
     * @param string $reason
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($subject, $message, $reason = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->subject = $subject;
        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }
}