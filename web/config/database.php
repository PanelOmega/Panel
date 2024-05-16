<?php

use App\OmegaConfig;
use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => OmegaConfig::get('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => OmegaConfig::get('DB_URL'),
            'database' => OmegaConfig::get('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => OmegaConfig::get('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => OmegaConfig::get('DB_URL'),
            'host' => OmegaConfig::get('DB_HOST', '127.0.0.1'),
            'port' => OmegaConfig::get('DB_PORT', '3306'),
            'database' => OmegaConfig::get('DB_DATABASE', 'laravel'),
            'username' => OmegaConfig::get('DB_USERNAME', 'root'),
            'password' => OmegaConfig::get('DB_PASSWORD', ''),
            'unix_socket' => OmegaConfig::get('DB_SOCKET', ''),
            'charset' => OmegaConfig::get('DB_CHARSET', 'utf8mb4'),
            'collation' => OmegaConfig::get('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => OmegaConfig::get('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => OmegaConfig::get('DB_URL'),
            'host' => OmegaConfig::get('DB_HOST', '127.0.0.1'),
            'port' => OmegaConfig::get('DB_PORT', '3306'),
            'database' => OmegaConfig::get('DB_DATABASE', 'laravel'),
            'username' => OmegaConfig::get('DB_USERNAME', 'root'),
            'password' => OmegaConfig::get('DB_PASSWORD', ''),
            'unix_socket' => OmegaConfig::get('DB_SOCKET', ''),
            'charset' => OmegaConfig::get('DB_CHARSET', 'utf8mb4'),
            'collation' => OmegaConfig::get('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => OmegaConfig::get('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => OmegaConfig::get('DB_URL'),
            'host' => OmegaConfig::get('DB_HOST', '127.0.0.1'),
            'port' => OmegaConfig::get('DB_PORT', '5432'),
            'database' => OmegaConfig::get('DB_DATABASE', 'laravel'),
            'username' => OmegaConfig::get('DB_USERNAME', 'root'),
            'password' => OmegaConfig::get('DB_PASSWORD', ''),
            'charset' => OmegaConfig::get('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => OmegaConfig::get('DB_URL'),
            'host' => OmegaConfig::get('DB_HOST', 'localhost'),
            'port' => OmegaConfig::get('DB_PORT', '1433'),
            'database' => OmegaConfig::get('DB_DATABASE', 'laravel'),
            'username' => OmegaConfig::get('DB_USERNAME', 'root'),
            'password' => OmegaConfig::get('DB_PASSWORD', ''),
            'charset' => OmegaConfig::get('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => OmegaConfig::get('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => OmegaConfig::get('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => OmegaConfig::get('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => OmegaConfig::get('REDIS_CLUSTER', 'redis'),
            'prefix' => OmegaConfig::get('REDIS_PREFIX', Str::slug(OmegaConfig::get('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => OmegaConfig::get('REDIS_URL'),
            'host' => OmegaConfig::get('REDIS_HOST', '127.0.0.1'),
            'username' => OmegaConfig::get('REDIS_USERNAME'),
            'password' => OmegaConfig::get('REDIS_PASSWORD'),
            'port' => OmegaConfig::get('REDIS_PORT', '6379'),
            'database' => OmegaConfig::get('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => OmegaConfig::get('REDIS_URL'),
            'host' => OmegaConfig::get('REDIS_HOST', '127.0.0.1'),
            'username' => OmegaConfig::get('REDIS_USERNAME'),
            'password' => OmegaConfig::get('REDIS_PASSWORD'),
            'port' => OmegaConfig::get('REDIS_PORT', '6379'),
            'database' => OmegaConfig::get('REDIS_CACHE_DB', '1'),
        ],

    ],

];
