<?php
use App\OmegaConfig;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue supports a variety of backends via a single, unified
    | API, giving you convenient access to each backend using identical
    | syntax for each. The default queue connection is defined below.
    |
    */

    'default' => OmegaConfig::get('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection options for every queue backend
    | used by your application. An example configuration is provided for
    | each backend supported by Laravel. You're also free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'connection' => OmegaConfig::get('DB_QUEUE_CONNECTION'),
            'table' => OmegaConfig::get('DB_QUEUE_TABLE', 'jobs'),
            'queue' => OmegaConfig::get('DB_QUEUE', 'default'),
            'retry_after' => (int) OmegaConfig::get('DB_QUEUE_RETRY_AFTER', 90),
            'after_commit' => false,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => OmegaConfig::get('BEANSTALKD_QUEUE_HOST', 'localhost'),
            'queue' => OmegaConfig::get('BEANSTALKD_QUEUE', 'default'),
            'retry_after' => (int) OmegaConfig::get('BEANSTALKD_QUEUE_RETRY_AFTER', 90),
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => OmegaConfig::get('AWS_ACCESS_KEY_ID'),
            'secret' => OmegaConfig::get('AWS_SECRET_ACCESS_KEY'),
            'prefix' => OmegaConfig::get('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => OmegaConfig::get('SQS_QUEUE', 'default'),
            'suffix' => OmegaConfig::get('SQS_SUFFIX'),
            'region' => OmegaConfig::get('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => OmegaConfig::get('REDIS_QUEUE_CONNECTION', 'default'),
            'queue' => OmegaConfig::get('REDIS_QUEUE', 'default'),
            'retry_after' => (int) OmegaConfig::get('REDIS_QUEUE_RETRY_AFTER', 90),
            'block_for' => null,
            'after_commit' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database and table that store job
    | batching information. These options can be updated to any database
    | connection and table which has been defined by your application.
    |
    */

    'batching' => [
        'database' => OmegaConfig::get('DB_CONNECTION', 'sqlite'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control how and where failed jobs are stored. Laravel ships with
    | support for storing failed jobs in a simple file or in a database.
    |
    | Supported drivers: "database-uuids", "dynamodb", "file", "null"
    |
    */

    'failed' => [
        'driver' => OmegaConfig::get('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => OmegaConfig::get('DB_CONNECTION', 'sqlite'),
        'table' => 'failed_jobs',
    ],

];
