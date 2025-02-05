<?php

namespace Modules\WaipuTV\Observers;

use Modules\BillingBase\Observers\ItemObserver;

/**
 * Observer Class
 *
 * can handle   'creating', 'created', 'updating', 'updated',
 *              'deleting', 'deleted', 'saving', 'saved',
 *              'restoring', 'restored',
 */
class WaipuTVItemObserver extends ItemObserver
{
    protected $productTypesForDailyConversion = ['TV'];
}
