<?php

namespace Modules\BillingBase\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Modules\BillingBase\Entities\BillingBase;

class BillingBaseServiceProvider extends ServiceProvider
{
    protected $moduleName = 'BillingBase';
    protected $moduleNameLower = 'billingbase';

    public function boot(): void
    {
        $this->registerViews();
        $this->populateBillingCache();
    }

    protected function populateBillingCache(): void
    {
        if (! Cache::has('billingBase')) {
            Cache::forever('billingBase', BillingBase::first() ?? new BillingBase);
        }
    }

    public function register(): void
    {
        //
    }

    protected function registerViews(): void
    {
        $viewPath = module_path($this->moduleName, 'Resources/views');
        $this->loadViewsFrom($viewPath, $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/lang');
        $this->loadTranslationsFrom($sourcePath, $this->moduleNameLower);
    }
}