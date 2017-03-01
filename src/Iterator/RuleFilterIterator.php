<?php
/*
 * This file is part of project-quality-detector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityDetector\Iterator;

/**
 * Class RuleFilterIterator
 *
 * @package ProjectQualityDetector\Iterator
 */
class RuleFilterIterator extends \FilterIterator
{
    private $groups;

    public function __construct(\Iterator $iterator , $groups)
    {
        parent::__construct($iterator);
        $this->groups = $groups;
    }

    public function accept()
    {
        /*$user = $this->getInnerIterator()->current();
        if( strcasecmp($user['name'],$this->groups) == 0) {
            return false;
        }*/
        return true;
    }
}