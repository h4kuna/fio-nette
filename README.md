# Fio for Nette

[![Downloads this Month](https://img.shields.io/packagist/dm/h4kuna/fio-nette.svg)](https://packagist.org/packages/h4kuna/fio-nette)

Homepage for **Fio** and [documentation](//github.com/h4kuna/fio).

## Install by composer

```sh
$ composer require h4kuna/fio-nette
```

Example NEON config
-------------------
Define extension
```neon
extensions:
	fio: h4kuna\Fio\Nette\DI\FioExtension
```

Configure extension
```neon
fio:
	# mandatory
	account: 2600267402/2010
	token: 5asd64as5d46ad5a6
```

More accounts and first is default.
```neon
fio:
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
use h4kuna\Fio\Nette;

$fioFactory = $container->getService('fio.factory');
$fioPay = $fioFactory->createFioPay('next-alias');

// both are same, because first is default
$fioRead = $fioFactory->createFioRead();
$fioRead = $fioFactory->createFioRead('my-alias');
```
