<?php

use h4kuna\Fio,
	Tester\Assert;

$container = require __DIR__ . '/../bootsrap.php';

/* @var $fioFactory Fio\Nette\FioFactory */
$fioFactory = $container->getByType('h4kuna\Fio\Nette\FioFactory');

// PAY
$fioPay = $fioFactory->createFioPay();
Assert::true($fioPay instanceof Fio\FioPay);
Assert::same($fioPay->getAccount(), $fioFactory->createFioPay('fio1')->getAccount());

$fioPay2 = $fioFactory->createFioPay('fio2');
Assert::same('22222222', $fioPay2->getAccount()->getAccount());


// READ
Assert::true($fioFactory->createFioRead() instanceof Fio\FioRead);
