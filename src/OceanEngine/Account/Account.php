<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\OceanEngine\Account;

use AdMarketingAPI\OceanEngine\OceanEngine;

class Account extends OceanEngine
{
    public const BUDGET_MODE_INFINITE = 'BUDGET_MODE_INFINITE';

    public const BUDGET_MODE_DAY = 'BUDGET_MODE_DAY';

    /**
     * 查询账号余额.
     *
     * @see https://ad.oceanengine.com/openapi/doc/index.html?id=151
     *
     * @return array
     */
    public function fund(int $advertiser_id = 0)
    {
        $payload = [
            'advertiser_id' => $advertiser_id,
        ];
        return $this->request('open_api/2/advertiser/fund/get/', $payload);
    }

    /**
     * 获取账户日预算.
     *
     * @see https://ad.oceanengine.com/openapi/doc/index.html?id=329
     *
     * @return array
     */
    public function budget(array $advertiser_ids = [])
    {
        $payload = [
            'advertiser_ids' => $advertiser_ids,
        ];

        return $this->request('open_api/2/advertiser/budget/get/', $payload);
    }

    /**
     * 更新账户日预算.
     *
     * @see https://ad.oceanengine.com/openapi/doc/index.html?id=316
     *
     * @param float $budget_mode
     * @param int $budget
     * @return array
     */
    public function updateBudget(int $advertiser_id, string $budget_mode = self::BUDGET_MODE_DAY, float $budget = 0)
    {
        $payload = [
            'advertiser_id' => $advertiser_id,
            'budget_mode' => $budget_mode,
            'budget' => $budget,
        ];

        return $this->request('open_api/2/advertiser/update/budget/', $payload, 'POST');
    }
}
