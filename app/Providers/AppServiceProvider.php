<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\GeneralSetting;
use Prophecy\Doubler\Generator\Node\ArgumentNode;
use View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $generalSetting = GeneralSetting::first() ?? new GeneralSetting();
        if ($generalSetting->site_title === null) {
            $generalSetting->site_title = 'KS Lab';
        }

        View::share([
            'generalSetting' => $generalSetting,
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
