<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Tests\Toutiao;

use AdMarketingAPI\Tests\OceanEngineTest;

/**
 * @internal
 * @coversNothing
 */
class OAuthTest extends OceanEngineTest
{
    public function testGetToken()
    {
        $_POST['auth_code'] = '6a274a5bc132d828994f8525e4279e7b97b532b0';
        $app = $this->app();
        $token = $app->oauth->getToken(true);
        dump($token);
        exit;
    }
}
