Fio for Nette
=============
[![Build Status](https://travis-ci.org/h4kuna/fio-nette.svg?branch=master)](https://travis-ci.org/h4kuna/fio-nette)
[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/fio-nette.svg)](https://packagist.org/packages/h4kuna/fio-nette)

Homepage for **Fio** and [documentation](//github.com/h4kuna/fio).

Installation to project
-----------------------
The best way to install h4kuna/fio-nette is using Composer:
```sh
$ composer require h4kuna/fio-nette
```

Example NEON config
-------------------
Define extension
```
extensions:
    fioExtension: h4kuna\Fio\Nette\DI\FioExtension
```

Configure extension
```
fioExtension:
    # mandatory
	account: 2600267402/2010
	token: 5asd64as5d46ad5a6

    # optional
    transactionClass: \h4kuna\Fio\Response\Read\Transaction # if you need change name of property
```

More accounts and first is default.
```
fioExtension:
	accounts:
		my-alias: # name for select account
			account: 2600267402/2010
			token: 5asd64as5d46ad5a6
		next-alias:
			account: 123456789/3216
			token: 6a4sd54asadsasde564
```

And choose account like this.
```php
// nette 2.3
$fioFactory = $container->getByType('h4kuna\Fio\Nette\FioFactory');
// nette 2.4 & 2.3
$fioFactory = $container->getService('fioExtension.fioFactory');
$fioPay = $fioFactory->createFioPay('next-alias');

// both are same, because first is default
$fioRead = $fioFactory->createFioRead();
$fioRead = $fioFactory->createFioRead('my-alias');
```
