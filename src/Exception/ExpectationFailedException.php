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
 * Class ExpectationFailedException
 *
 * @package ProjectQualityDetector\Exception
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
    public function __construct($subject, $message, $reason = "", $code = 0, Exception $previous = null)
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