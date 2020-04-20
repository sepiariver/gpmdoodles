# Testing GPMDoodles

1. From this directory `composer install`  
2. Ensure that a valid, working MODX `config.core.php` file exists in the project root.
3. GPMDoodles must be installed, or symlinked from the components folders. `ln -s /path/to/repo/core/components/gpmdoodles /path/to/modx/core/components/gpmdoodles`
3. Run `vendor/bin/phpunit`