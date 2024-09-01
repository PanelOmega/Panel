#php -d memory_limit=4G ./vendor/bin/phpstan analyse config database app

php -d memory_limit=4G ./vendor/bin/rector process --dry-run --config=./rector/rector.php
