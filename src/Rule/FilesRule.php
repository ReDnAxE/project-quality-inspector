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
 * Class FilesRule
 *
 * @package ProjectQualityInspector\Rule
 */
class FilesRule extends AbstractRule
{
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

        foreach ($this->config as $fileConf) {
            try {
                $files = $this->expectsFilesGlobExists($fileConf, $this->baseDir);
                if (isset($fileConf['grep']) && count($files)) {
                    $this->expectsFilesGrep($files, $fileConf['grep'], $this->getReason($fileConf));
                }
                $this->addAssertion($this->getValue($fileConf));
            } catch (ExpectationFailedException $e) {
                $expectationsFailedExceptions[] = $e;
                $this->addAssertion($this->getValue($fileConf), [['message' => $e->getMessage() . $e->getReason(), 'type' => 'expectsFilesGlobExists|expectsFilesGrep']]);
            }
        }

        if (count($expectationsFailedExceptions)) {
            $this->throwRuleViolationException($expectationsFailedExceptions);
        }
    }

    /**
     * @param $fileConf
     * @param $baseDir
     * @return array
     */
    private function expectsFilesGlobExists($fileConf, $baseDir)
    {
        $files = [];
        $fileName = $this->getValue($fileConf);
        $reason = $this->getReason($fileConf);

        $filePath = $baseDir . DIRECTORY_SEPARATOR . $fileName;

        if ($fileName[0] == '!') {
            $fileName = ltrim($fileName, '!');
            $filePath = $baseDir . DIRECTORY_SEPARATOR . $fileName;
            $this->globShouldNotFind($filePath, $reason);
        } else {
            $files = $this->globShouldFind($filePath, $reason);
        }

        return $files;
    }

    /**
     * @param $filePathGlob
     * @param $reason
     * @return array
     *
     * @throws ExpectationFailedException
     */
    private function globShouldFind($filePathGlob, $reason)
    {
        $message = sprintf('file <fg=green>"%s"</> should exists', $filePathGlob);
        $files = glob($filePathGlob);
        if (!count($files)) {
            throw new ExpectationFailedException($filePathGlob, $message, $reason);
        }

        return $files;
    }

    /**
     * @param string $filePathGlob
     * @param string $reason
     *
     * @throws ExpectationFailedException
     */
    private function globShouldNotFind($filePathGlob, $reason)
    {
        $message = sprintf('file <fg=green>"%s"</> should not exists', $filePathGlob);

        if (count(glob($filePathGlob))) {
            throw new ExpectationFailedException($filePathGlob, $message, $reason);
        }
    }

    /**
     * @param array $files
     * @param array|string $greps
     * @param string $reason
     */
    private function expectsFilesGrep(array $files, $greps, $reason)
    {
        $greps = (!is_array($greps)) ? [$greps] : $greps;

        foreach ($files as $file) {
            foreach ($greps as $grep) {
                $this->fileShouldGrep($file, $grep, $reason);
            }
        }
    }

    /**
     * @param string $filePath
     * @param string $grep
     * @param string $reason
     *
     * @throws ExpectationFailedException
     */
    private function fileShouldGrep($filePath, $grep, $reason)
    {
        $message = sprintf('file <fg=green>"%s"</> should contains <fg=green>"%s"</> string', $filePath, $grep);
        $negation = false;
        $recursiveOption = '';

        if ($grep[0] == '!') {
            $grep = ltrim($grep, '!');
            $negation = true;
            $message = sprintf('file <fg=green>"%s"</> should not contains <fg=green>"%s"</> string', $filePath, $grep);
        }

        if (is_dir($filePath)) {
            $recursiveOption = '-r';
        }

        $result = ProcessHelper::execute(sprintf('grep %s %s %s', $recursiveOption, escapeshellarg($grep), $filePath), $this->baseDir, true);

        if (($negation && count($result)) || (!$negation && !count($result))) {
            throw new ExpectationFailedException($filePath, $message, $reason);
        }
    }
}