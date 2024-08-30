#php -d memory_limit=4G ./vendor/bin/phpstan analyse config database app

ls -la

php -d memory_limit=4G ./vendor/bin/rector process --config=./rector/rector.php
