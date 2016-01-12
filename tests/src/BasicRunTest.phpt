<?php

use h4kuna\Fio,
	Tester\Assert;

$container = require __DIR__ . '/../bootsrap.php';


$fioPay = $container->getByType('h4kuna\Fio\FioPay');
Assert::true($fioPay instanceof Fio\FioPay);

$fioRead = $container->getByType('h4kuna\Fio\FioRead');
Assert::true($fioRead instanceof Fio\FioRead);