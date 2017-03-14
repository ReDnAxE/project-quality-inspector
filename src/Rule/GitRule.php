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

        $notMergedBranchesInfo = $this->listMergedOrNotMergedBranches($stableBranches, false);

        try {
            foreach ($notMergedBranchesInfo as $notMergedBranchInfo) {
                $this->expectsBranchNotTooBehind($notMergedBranchInfo, $stableBranches);
                $this->expectsBranchNotTooOld($notMergedBranchInfo, $stableBranches); //TODO
            }
        } catch (ExpectationFailedException $e) {
            $expectationsFailedExceptions[] = $e;
        }

        if (count($expectationsFailedExceptions)) {
            $this->throwRuleViolationException($expectationsFailedExceptions);
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
     * @param array $stableBranches
     * @param int $threshold
     *
     * @throws ExpectationFailedException
     */
    private function expectsNoMergedBranches(array $stableBranches, $threshold)
    {
        if ($mergedBranches = $this->listMergedOrNotMergedBranches($stableBranches, true)) {
            if (count($mergedBranches) >= $threshold) {
                $message = sprintf('there is too much remaining merged branches (%s) : %s', count($mergedBranches), $this->stringifyCommitArrays($mergedBranches));
                throw new ExpectationFailedException($mergedBranches, $message);
            }
        }
    }

    /**
     * Retrieve two first commits dates after common ancestor of the two branches, and expects there
     *
     * @param array $notMergedBranchInfo
     * @param array $stableBranches
     *
     * @throws ExpectationFailedException
     */
    private function expectsBranchNotTooBehind(array $notMergedBranchInfo, array $stableBranches)
    {
        foreach ($stableBranches as $stableBranch) {
            $lrAheadCommitsCount = $this->getLeftRightAheadCommitsCountAfterMergeBase($stableBranch, $notMergedBranchInfo[4]);

            if ($lrAheadCommitsCount[$stableBranch] > 0) {
                $stableBranchFirstCommitInfo = $this->getBranchFirstCommitInfo($notMergedBranchInfo[4], $stableBranch);
                $interval = $this->getBranchesInfosDatesDiff($notMergedBranchInfo, $stableBranchFirstCommitInfo);

                if ((int)$interval->format('%r%a') >= (int)$this->config['too-old-stable-work-not-in-branch-threshold']) {
                    $message = sprintf('The branch %s is behind %s by %s commit(s), that contain more than %s days old work. %s should update the branch %s', $notMergedBranchInfo[4], $stableBranch, $lrAheadCommitsCount[$stableBranch], $this->config['too-old-stable-work-not-in-branch-threshold'], $notMergedBranchInfo[3], $notMergedBranchInfo[4]);
                    throw new ExpectationFailedException($notMergedBranchInfo, $message);
                }
            }
        }
    }

    /**
     * @param array $notMergedBranchInfo
     * @param array $stableBranches
     *
     * @throws ExpectationFailedException
     */
    private function expectsBranchNotTooOld(array $notMergedBranchInfo, array $stableBranches)
    {
        foreach ($stableBranches as $stableBranch) {
            //TODO check if branch is too old (stableBranch last commit - notMergedBranch last commit)
        }
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
        $result = explode("\t", $result[0]);

        $result = [
            $branchLeft => $result[0],
            $branchRight => $result[1]
        ];

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
     * @return array
     */
    private function getBranchFirstCommitInfo($baseBranch, $branch)
    {
        $branchInfo = null;
        $result = ProcessHelper::execute(sprintf('git log --format="%s" %s..%s | tail -1', $this->commitFormat, $baseBranch, $branch), $this->baseDir);
        if (count($result)) {
            $branchInfo = explode('|', $result[0]);
            $branchInfo[] = $branch;
        }

        return $branchInfo;
    }

    /**
     * @param $branchInfoLeft
     * @param $branchInfoRight
     * @return bool|\DateInterval
     */
    private function getBranchesInfosDatesDiff($branchInfoLeft, $branchInfoRight)
    {
        $format = 'Y-m-!d H:i:s O';
        $dateLeft = \DateTime::createFromFormat($format, $branchInfoLeft[1]);
        $dateRight = \DateTime::createFromFormat($format, $branchInfoRight[1]);

        return $dateLeft->diff($dateRight);
    }

    /**
     * @param $branch
     * @return string
     */
    private function getBranchLastCommitInfo($branch)
    {
        $result = ProcessHelper::execute(sprintf('git show --format="%s" %s | head -n 1', $this->commitFormat, $branch), $this->baseDir);
        $branchInfo = explode('|', $result[0]);
        $branchInfo[] = $branch;

        return $branchInfo;
    }

    /**
     * @return array
     */
    private function getStableBranches()
    {
        $result = ProcessHelper::execute(sprintf('git branch -r | grep -e "%s"', $this->getStableBranchesRegex()), $this->baseDir);

        return array_map("trim", $result);
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