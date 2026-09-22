<?php

namespace App\Providers;

use App\Models\PlatformSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || str_contains(request()->header('host', ''), 'pllatinum.me') || request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }

        try {
            if (Schema::hasTable('platform_settings')) {
                $platformName = PlatformSetting::get('platform_name');
                if ($platformName) {
                    config(['app.name' => $platformName, 'legal.app_name' => $platformName]);
                }

                $storageDriver = PlatformSetting::get('storage_driver');
                if ($storageDriver) {
                    config(['filesystems.default' => $storageDriver]);
                }

                $awsKey = PlatformSetting::get('aws_access_key_id');
                if ($awsKey) {
                    config(['filesystems.disks.s3.key' => $awsKey]);
                }

                $awsSecret = PlatformSetting::get('aws_secret_access_key');
                if ($awsSecret) {
                    config(['filesystems.disks.s3.secret' => $awsSecret]);
                }

                $awsRegion = PlatformSetting::get('aws_default_region');
                if ($awsRegion) {
                    config(['filesystems.disks.s3.region' => $awsRegion]);
                }

                $awsBucket = PlatformSetting::get('aws_bucket');
                if ($awsBucket) {
                    config(['filesystems.disks.s3.bucket' => $awsBucket]);
                }

                $awsEndpoint = PlatformSetting::get('aws_endpoint');
                if ($awsEndpoint) {
                    config(['filesystems.disks.s3.endpoint' => $awsEndpoint]);
                }

                $pathStyle = PlatformSetting::get('aws_use_path_style_endpoint');
                if ($pathStyle !== null) {
                    config(['filesystems.disks.s3.use_path_style_endpoint' => (bool) $pathStyle]);
                }

                View::share('globalPlatformLogo', PlatformSetting::logoUrl());
                View::share('globalPlatformName', PlatformSetting::platformName());
            }
        } catch (\Throwable $e) {
            // Gracefully ignore during initial migrations/CLI commands
        }
    }
}
