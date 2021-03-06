<?php

include __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();

// 2# Create Nette Configurator
$configurator = new Nette\Configurator;

$tmp = __DIR__ . '/temp';
$configurator->enableDebugger($tmp);
$configurator->setTempDirectory($tmp);
$configurator->setDebugMode(FALSE);
$configurator->addConfig(__DIR__ . '/config/test.neon');
$local = __DIR__ . '/config/test.local.neon';
if (is_file($local)) {
	$configurator->addConfig($local);
}
$container = $configurator->createContainer();

return $container;



