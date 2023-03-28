<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Gdt\Dmp;

use AdMarketingAPI\Gdt\Gdt;
use CURLFile;

class Audience extends Gdt
{
    public const AUDIENCE_TYPE_CUSTOMER_FILE = 'CUSTOMER_FILE';

    public const AUDIENCE_TYPE_LOOKALIKE = 'LOOKALIKE';

    public const AUDIENCE_TYPE_USER_ACTION = 'USER_ACTION';

    public const AUDIENCE_TYPE_LBS = 'LBS';

    public const AUDIENCE_TYPE_KEYWORD = 'KEYWORD';

    public const AUDIENCE_TYPE_AD = 'AD';

    public const AUDIENCE_TYPE_COMBINE = 'COMBINE';

    public const USER_ID_TYPE_HASH_IDFA = ' HASH_IDFA';

    public const USER_ID_TYPE_HASH_IMEI = ' HASH_IMEI';

    public const USER_ID_TYPE_GDT_OPENID = ' GDT_OPENID';

    public const USER_ID_TYPE_HASH_MAC = ' HASH_MAC';

    public const USER_ID_TYPE_HASH_MOBILE_PHONE = ' HASH_MOBILE_PHONE';

    public const USER_ID_TYPE_HASH_QQ = ' HASH_QQ';

    public const USER_ID_TYPE_IDFA = ' IDFA';

    public const USER_ID_TYPE_IMEI = ' IMEI';

    public const USER_ID_TYPE_MAC = ' MAC';

    public const USER_ID_TYPE_MOBILE_QQ_OPENID = ' MOBILE_QQ_OPENID';

    public const USER_ID_TYPE_QQ = ' QQ';

    public const USER_ID_TYPE_WX_OPENID = ' WX_OPENID';

    public const USER_ID_TYPE_WECHAT_OPENID = ' WECHAT_OPENID';

    public const USER_ID_TYPE_SALTED_HASH_IMEI = ' SALTED_HASH_IMEI';

    public const USER_ID_TYPE_SALTED_HASH_IDFA = ' SALTED_HASH_IDFA';

    /**
     * 创建客户人群.
     * @see https://developers.e.qq.com/docs/audience_management/file?version=1.1
     *
     * @param string $account_id
     * @param string $name
     * @param string $type
     * @param array $attrs
     *
     * @return array
     */
    public function add($account_id, $name, $type = self::AUDIENCE_TYPE_CUSTOMER_FILE, $attrs = [])
    {
        $paiload = array_merge([
            'account_id' => $account_id,
            'name' => $name,
            'type' => $type,
        ], $attrs);

        return $this->required(['account_id', 'name', 'type'])
            ->request('custom_audiences/add', $paiload, 'POST');
    }

    /**
     * 上传客户人群数据文件.
     * @see https://developers.e.qq.com/docs/apilist/audiences/custom_audience_file?version=1.1#a1
     *
     * @param string $account_id
     * @param string $name
     * @param string $file
     * @param string $user_id_type
     * @param array $attrs
     * @param mixed $audience_id
     *
     * @return array
     */
    public function addFile($account_id, $audience_id, $file, $user_id_type = self::USER_ID_TYPE_HASH_IMEI, $attrs = [])
    {
        $payload = array_merge([
            'account_id' => $account_id,
            'audience_id' => $audience_id,
            'user_id_type' => $user_id_type,
            'file' => new CURLFile($file, null, null),
        ], $attrs);

        $this->app['config']->set('account_id', $account_id);
        $token = $this->app['oauth']->getToken()['access_token'];

        return $this->curlFile('https://api.e.qq.com/v1.1/custom_audience_files/add', $token, $payload);
    }

    /**
     * 创建定向.
     * @see https://developers.e.qq.com/docs/apilist/ads/targeting?version=1.1#a1
     *
     * @param string $account_id
     * @param string $name
     * @param array $attrs
     *
     * @return array
     */
    public function targeting($account_id, $name, $attrs = [])
    {
        $payload = array_merge([
            'account_id' => $account_id,
            'targeting_name' => $name,
        ], $attrs);

        return $this->required(['account_id', 'targeting_name'])
            ->request('targetings/add', $payload, 'POST');
    }
}
