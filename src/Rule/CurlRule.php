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
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * Class CurlRule
 *
 * @package ProjectQualityInspector\Rule
 */
class CurlRule extends AbstractRule
{
    private $client;

    public function __construct(array $config, $baseDir)
    {
        parent::__construct($config, $baseDir);

        $this->client = new \GuzzleHttp\Client();
    }

    /**
     * @inheritdoc
     */
    public function evaluate()
    {
        $expectationsFailedExceptions = [];

        foreach ($this->config['queries'] as $query) {
            try {
                $this->expectsResponseMatches($query);
            } catch (ExpectationFailedException $e) {
                
            }
        }
    }

    /**
     * @param  array $query
     */
    public function expectsResponseMatches($query)
    {
        $request = $query['request'];
        $expectedResponse = $query['expectedResponse'];
        $request['url'] = ($request['url']) ? $request['url'] : $this->config['base-url'];
        $request['method'] = ($request['method']) ? $request['method'] : 'GET';

        $response = $this->client->send(new Request($request['method'], $request['url']));

        try {
            $this->expectsHeadersMatches($expectedResponse, $response);
            $this->addAssertion($request['url']);
        } catch (ExpectationFailedException $e) {
            $expectationsFailedExceptions[] = $e;
            $this->addAssertion($request['url'], [['message' => $e->getMessage() . $e->getReason(), 'type' => 'expectsHeadersMatches']]);
        }

        if (count($expectationsFailedExceptions)) {
            $this->throwRuleViolationException($expectationsFailedExceptions);
        }
    }

    /**
     * @param  array $expectedResponse
     * @param  array $response
     *
     * @throws ExpectationFailedException
     */
    private function expectsHeadersMatches($expectedResponse, Response $response)
    {
        $this->expectsStatusCodeMatches($expectedResponse, $response);

        foreach ($expectedResponse['headers'] as $expectedHeader => $expectedHeaderValue) {
            if (!isset($response->getheaders()[$expectedHeader])) {
                $message = sprintf('there is no expected header "%s" in response of url', $expectedHeader);
                throw new ExpectationFailedException($mergedBranches, $message);
            }

            if ($response->getheaders()[$expectedHeader][0] != $expectedHeaderValue) {
                $message = sprintf('the expected "%s" header\'s value should be "%s". Current value is %s', $expectedHeader, $expectedHeaderValue, $response->getheaders()[$expectedHeader][0]);
                throw new ExpectationFailedException($mergedBranches, $message);
            }
        }
    }

    /**
     * @param  array $expectedResponse
     * @param  array $response
     *
     * @throws ExpectationFailedException
     */
    private function expectsStatusCodeMatches($expectedResponse, Response $response)
    {
        if (isset($expectedResponse['statusCode']) && $expectedResponse['statusCode'] != $response->getStatusCode()) {
            $message = sprintf('the expected status code should be "%s". Current value is "%s"', $expectedResponse['statusCode'], $response->getStatusCode());
            throw new ExpectationFailedException($mergedBranches, $message);
        }
    }
}