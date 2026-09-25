# Fio for Nette

[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/fio-nette.svg)](https://packagist.org/packages/h4kuna/fio-nette)
[![Latest Stable Version](https://poser.pugx.org/h4kuna/fio-nette/v/stable?format=flat)](https://packagist.org/packages/h4kuna/fio-nette)
[![Coverage Status](https://coveralls.io/repos/github/h4kuna/fio-nette/badge.svg?branch=main)](https://coveralls.io/github/h4kuna/fio-nette?branch=main)
[![Total Downloads](https://poser.pugx.org/h4kuna/fio-nette/downloads?format=flat)](https://packagist.org/packages/h4kuna/fio-nette)
[![License](https://poser.pugx.org/h4kuna/fio-nette/license?format=flat)](https://packagist.org/packages/h4kuna/fio-nette)

Part of the [h4kuna PHP libraries](https://github.com/h4kuna/library), see the overview of all packages.

Nette DI extension for [h4kuna/fio](https://github.com/h4kuna/fio), where you find the documentation of the library.

## Install by composer

Requires PHP 8.2 or newer.

```sh
composer require h4kuna/fio-nette

# optional, default HTTP client and factories
composer require guzzlehttp/guzzle
```

If your container already has services implementing `Psr\Http\Client\ClientInterface`, `Psr\Http\Message\RequestFactoryInterface` and `Psr\Http\Message\StreamFactoryInterface`, the extension uses them, otherwise Guzzle is required.

## Example NEON config

Define the extension:
```neon
extensions:
	fio: h4kuna\Fio\Nette\DI\FioExtension
```

Configure the extension:
```neon
fio:
	# mandatory
	account: 2600267402/2010
	token: 5asd64as5d46ad5a6

	# optional, the default is %tempDir%/h4kuna/fio
	tempDir: %tempDir%/fio
```

More accounts, the first one is the default:
```neon
fio:
	accounts:
		my-alias: # name to select the account
			account: 2600267402/2010
			token: 5asd64as5d46ad5a6
		next-alias:
			account: 123456789/3216
			token: 6a4sd54asadsasde564
```

The service `fio.factory` is autowired as `h4kuna\Fio\Nette\FioFactory`. Choose the account like this:
```php
/** @var h4kuna\Fio\Nette\FioFactory $fioFactory */
$fioFactory = $container->getService('fio.factory');
$fioPay = $fioFactory->createFioPay('next-alias');

// both are the same, because the first one is the default
$fioRead = $fioFactory->createFioRead();
$fioRead = $fioFactory->createFioRead('my-alias');
```
