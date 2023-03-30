<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\OceanEngine\Tools;

use AdMarketingAPI\OceanEngine\OceanEngine;

class Tools extends OceanEngine
{
    /**
     * 获取计划诊断详情.
     *
     * @see https://ad.oceanengine.com/openapi/doc/index.html?id=326
     *
     * @return array
     */
    public function diagnosis(int $advertiser_id, array $ad_ids)
    {
        $payload = [
            'advertiser_id' => $advertiser_id,
            'ad_ids' => $ad_ids,
        ];

        return $this->request('open_api/2/tools/diagnosis/ad/get/', $payload);
    }

    /**
     * 获取计划诊断预估变化趋势.
     *
     * @see https://ad.oceanengine.com/openapi/doc/index.html?id=327
     *
     * @return array
     */
    public function diagnosis_curve(int $advertiser_id, int $ad_id)
    {
        $payload = [
            'advertiser_id' => $advertiser_id,
            'ad_id' => $ad_id,
        ];

        return $this->request('open_api/2/tools/diagnosis/ad/curve/', $payload);
    }

    /**
     * 获取建站列表.
     *
     * @see https://ad.oceanengine.com/openapi/doc/index.html?id=387
     *
     * @return array
     */
    public function sites(int $advertiser_id, int $page = 1, int $pageSize = 20)
    {
        $payload = [
            'advertiser_id' => $advertiser_id,
            'page' => $page,
            'page_size' => $pageSize,
        ];

        return $this->request('open_api/2/tools/site/get/', $payload);
    }
}
