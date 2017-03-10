<?php
/*
 * This file is part of project-quality-inspector.
 *
 * (c) Alexandre GESLIN <alexandre@gesl.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ProjectQualityInspector\Rule;

use ProjectQualityInspector\Application\ProcessHelper;
use ProjectQualityInspector\Exception\ExpectationFailedException;

/**
 * Class GitRule
 *
 * @package ProjectQualityInspector\Rule
 */
class GitRule extends AbstractRule
{
    private $commitFormat = '%H|%ci|%cr|%an';

    public function __construct(array $config, $baseDir)
    {
        parent::__construct($config, $baseDir);
    }

    /**
     * @inheritdoc
     */
    public function evaluate()
    {
        $expectationsFailedExceptions = [];
        $stableBranches = $this->getStableBranches();

        try {
            $this->expectsNoMergedBranches($stableBranches, $this->config['threshold-too-many-merged-branches']);
        } catch (ExpectationFailedException $e) {
            $expectationsFailedExceptions[] = $e;
        }

        $notMergedBranches = $this->listMergedOrNotMergedBranches($stableBranches, false);

        try {
            print_r($notMergedBranches);
            //TODO: for each no merged branches, expectsBranchNotTooOld, expectsBranchNotTooBehind
        } catch (ExpectationFailedException $e) {
            $expectationsFailedExceptions[] = $e;
        }

        if (count($expectationsFailedExceptions)) {
            $this->throwRuleViolationException($expectationsFailedExceptions);
        }
    }

    /**
     * @param array $stableBranches
     * @param int $threshold
     *
     * @throws ExpectationFailedException
     */
    public function expectsNoMergedBranches(array $stableBranches, $threshold)
    {
        if ($mergedBranches = $this->listMergedOrNotMergedBranches($stableBranches, true)) {
            if (count($mergedBranches) >= $threshold) {
                $message = sprintf('there is too much remaining merged branches (%s) : %s', count($mergedBranches), $this->stringifyCommitArrays($mergedBranches));
                throw new ExpectationFailedException($mergedBranches, $message);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public static function getGroups()
    {
        return array_merge(parent::getGroups(), ['git']);
    }

    /**
     * Get merged/not merged branches compared to $stablesBranches.
     * If there is multiple stable branches, and $merged = true : list branches that are merged in at least one of the stable branches
     * If there is multiple stable branches, and $merged = false : list branches that are not merged for any of the stable branches
     *
     * @param array $stableBranches
     * @param bool $merged
     * @return array
     */
    private function listMergedOrNotMergedBranches(array $stableBranches, $merged = true)
    {
        $branches = [];
        $mergedOption = ($merged) ? '--merged' : '--no-merged';

        foreach ($stableBranches as $stableBranch) {
            $result = ProcessHelper::execute(sprintf('for branch in `git branch -r %s %s | grep -ve "%s"`; do echo `git show --format="%s" $branch | head -n 1`\|$branch; done | sort -r', $mergedOption, $stableBranch, $this->getStableBranchesRegex(), $this->commitFormat), $this->baseDir);

            $branches[$stableBranch] = $result;
        }

        if (count($branches) >= 2) {
            $branches = ($merged) ? array_unique(call_user_func_array('array_merge', $branches)) : call_user_func_array('array_intersect', $branches);
        } else {
            $branches = $branches[$stableBranches[0]];
        }

        $branches = array_map(function ($element) {
                return explode('|', $element);
            }, $branches);

        return $branches;
    }

    /**
     * Get numbers of commits after common ancestor for $branchLeft and $branchRight (ex: $branchLeft => 2,  $branchRight => 5)
     *
     * @param $branchLeft
     * @param $branchRight
     * @return array
     */
    private function getLeftRightAheadCommitsCountAfterMergeBase($branchLeft, $branchRight)
    {
        $result = ProcessHelper::execute(sprintf('git rev-list --left-right --count %s...%s', $branchLeft, $branchRight), $this->baseDir);
        print_r($result);
        //TODO

        return $result;
    }

    /**
     * Get common ancestor commit
     *
     * @param string $branchLeft
     * @param string $branchRight
     * @return array
     */
    private function getMergeBaseCommit($branchLeft, $branchRight)
    {
        $result = ProcessHelper::execute(sprintf('git merge-base %s %s', $branchLeft, $branchRight), $this->baseDir);
        print_r($result);
        //TODO

        return $result;
    }

    /**
     * Get first commit of $branch after common ancestor commit of $baseBranch and $branch
     *
     * @param string $baseBranch
     * @param string $branch
     */
    private function getBranchFirstCommit($baseBranch, $branch)
    {
        $result = ProcessHelper::execute(sprintf('git log --format="%s" %s..%s | tail -1', $this->commitFormat, $baseBranch, $branch), $this->baseDir);
        print_r($result);
        //TODO
    }

    /**
     * @return array
     */
    private function getStableBranches()
    {
        $result = ProcessHelper::execute(sprintf('git branch -r | grep -e "%s"', $this->getStableBranchesRegex()), $this->baseDir);
        $result = array_map("trim", $result);

        return $result;
    }

    /**
     * @return string
     */
    private function getStableBranchesRegex()
    {
        $stableBranchesRegex = array_map(function ($element) {
            return '\(^[ ]*'.$element.'$\)';
        }, $this->config['stable-branches-regex']);

        return implode('\|', $stableBranchesRegex);
    }

    /**
     * @param array $commits
     * @return string
     */
    private function stringifyCommitArrays(array $commits)
    {
        $commits = array_map(function($commit) {
            return sprintf('(%s - %s by %s)', $commit[4], $commit[2], $commit[3]);
        }, $commits);
        return implode(', ', $commits);
    }
}