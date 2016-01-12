Fio for Nette
=============
[![Build Status](https://travis-ci.org/h4kuna/fio-nette.svg?branch=master)](https://travis-ci.org/h4kuna/fio-nette)

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
    fioExtension: h4kuna\Fio\DI\FioExtension
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

More accounts
```
fioExtension:
	accounts:
		my-alias: # name for select account
			account: 2600267402/2010
			token: 5asd64as5d46ad5a6
```
