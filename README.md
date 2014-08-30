# sloca-poc

Proof of concept code for certain aspects of SLOCA.

## Important things
`generate.php` allows you to pseudo-randomly generate data based on certain assumptions. It should only be used for getting some data to briefly exercise your code. You should be writing your own test cases and test data in order to reliably test all your edge cases.

## Usage
You will need composer somewhere.

```
composer install
```

This will fetch all necessary dependencies, as listed inside `composer.json`. Where necessary, code assumes the PSR-0 autoloader is at `vendor/autoload.php`.
