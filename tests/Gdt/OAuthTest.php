<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Tests\Gdt;

use AdMarketingAPI\Tests\GdtTest;

/**
 * @internal
 * @coversNothing
 */
class OAuthTest extends GdtTest
{
    public function testGetAuthUrl()
    {
        $app = $this->app()['oauth'];
        $this->assert(true);
    }

    public function testGetToken()
    {
        $_POST['authorization_code'] = '6a274a5bc132d828994f8525e4279e7b97b532b0';
        $app = $this->app();
        $token = $app->oauth->getToken(true);
        dump($token);
        exit;
    }
}
