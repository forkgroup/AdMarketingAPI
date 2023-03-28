<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Gdt;

use AdMarketingAPI\Kernel\BaseService;

class Gdt extends BaseService
{
    /**
     * Palyload build.
     */
    protected function build(array $prepends = []): array
    {
        $palyload = array_replace_recursive($this->all(), $prepends);
        if (isset($palyload['account_id'])) {
            $this->app['config']->set('account_id', $palyload['account_id']);
        }

        if (isset($palyload['account_ids'])) {
            $this->app['config']->set('account_id', $palyload['account_ids'][0]);
        }

        foreach ($palyload as $field => $value) {
            if (is_array($value)) {
                $palyload[$field] = json_encode($value);
            }
        }

        return $palyload;
    }
}
