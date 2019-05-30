<?php

namespace RoiupAgency\LumenCassandra;

use Illuminate\Support\ServiceProvider;

class CassandraServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(){}

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // Add database driver.
	    $this->app->singleton('db', function ($app) {
            $config = [
            	'host' => env('DB_HOST', 'localhost'),
            	'port' => intval(env('DB_PORT', 9042)),
            	'keyspace' => env('DB_KEYSPACE', 'mykeyspace'),
            	'username' => env('DB_USERNAME', ''),
            	'password' => env('DB_PASSWORD', ''),
            ];

            if (env('DB_CLI_HOST',false) && $this->app->runningInConsole()) {
                $config['host'] = env('DB_CLI_HOST', 'localhost');
            }

            return new Connection($config);
        });

    }
}
