<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Tests;

use AdMarketingAPI\Kernel\AccessToken;
use AdMarketingAPI\Kernel\ServiceContainer;
use Mockery;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * class TestCase.
 *
 * @internal
 * @coversNothing
 */
class TestCase extends BaseTestCase
{
    /**
     * Tear down the test case.
     */
    public function tearDown()
    {
        $this->finish();
        parent::tearDown();
        if ($container = Mockery::getContainer()) {
            $this->addToAssertionCount($container->Mockery_getExpectationCount());
        }
        Mockery::close();
    }

    /**
     * Create API Client mock object.
     *
     * @param string $name
     * @param array|string $methods
     * @param null|\EasyAdm\Kernel\ServiceContainer $app
     *
     * @return \Mockery\Mock
     */
    public function mockApiClient($name, $methods = [], ServiceContainer $app = null)
    {
        $methods = implode(',', array_merge([
            'httpGet', 'httpPost', 'httpPostJson', 'httpUpload',
            'request', 'requestRaw', 'requestArray', 'registerMiddlewares',
        ], (array) $methods));

        $client = Mockery::mock(
            $name . "[{$methods}]",
            [
                $app ?? Mockery::mock(ServiceContainer::class),
                Mockery::mock(AccessToken::class), ]
        )->shouldAllowMockingProtectedMethods();
        $client->allows()->registerHttpMiddlewares()->andReturnNull();

        return $client;
    }

    /**
     * Run extra tear down code.
     */
    protected function finish()
    {
        // call more tear down methods
    }
}
