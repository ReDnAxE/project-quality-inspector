<?php
/*
 * This file is part of project-quality-inspector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityInspector\Iterator;

/**
 * Class RuleFilterIterator
 *
 * @package ProjectQualityInspector\Iterator
 */
class RuleFilterIterator extends \FilterIterator
{
    /**
     * @var array
     */
    private $ruleNames;

    /**
     * @param \Iterator $iterator
     * @param array   $ruleNames
     */
    public function __construct(\Iterator $iterator , array $ruleNames = [])
    {
        parent::__construct($iterator);
        $this->ruleNames = $ruleNames;
    }

    public function accept()
    {
        if (count($this->ruleNames)) {
            $current = $this->getInnerIterator()->current();
            return in_array($current::getRuleName(), $this->ruleNames);
        }

        return true;
    }
}