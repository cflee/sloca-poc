# sloca-poc

Proof of concept code for certain aspects of SLOCA.

## Useful things
`generate.php` allows you to pseudo-randomly generate data based on certain assumptions. You should write your own test cases and test data to reliably test all your edge cases. This tool should only be used to (i) get some data just to make sure your code is running, or (ii) generate lots of data to test how well your code scales.

## Usage
You will need composer somewhere.

```
composer install
```

This will fetch all necessary dependencies, as listed inside `composer.json`. Where necessary, code assumes the PSR-0 autoloader is at `vendor/autoload.php`.
