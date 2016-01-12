<?php

namespace h4kuna\Fio\DI;

use Nette\DI\CompilerExtension,
	Nette\Utils;

class FioExtension extends CompilerExtension
{

	public $defaults = [
		'account' => NULL,
		'token' => NULL,
		'accounts' => [],
		'temp' => '%tempDir%/fio',
		'transactionClass' => '\h4kuna\Fio\Response\Read\Transaction'
	];

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		if (!$config['accounts']) {
			$config['accounts']['default'] = [
				'account' => $config['account'],
				'token' => $config['token']
			];
		}
		unset($config['account'], $config['token']);

		Utils\FileSystem::createDir($config['temp']);

		// Accounts
		$builder->addDefinition($this->prefix('accounts'))
			->setClass('h4kuna\Fio\Account\Accounts')
			->setFactory('h4kuna\Fio\Account\AccountsFactory::create', [$config['accounts']]);

		// XMLFile
		$builder->addDefinition($this->prefix('xmlFile'))
			->setClass('h4kuna\Fio\Request\Pay\XMLFile')
			->setArguments([$config['temp']]);

		// PaymentFactory
		$builder->addDefinition($this->prefix('paymentFactory'))
			->setClass('h4kuna\Fio\Request\Pay\PaymentFactory');

		// Queue
		$builder->addDefinition($this->prefix('queue'))
			->setClass('h4kuna\Fio\Request\Queue');

		// StatementFactory
		$builder->addDefinition($this->prefix('jsonTransactionFactory'))
			->setClass('h4kuna\Fio\Response\Read\JsonTransactionFactory')
			->setArguments([$config['transactionClass']]);

		// Reader
		$builder->addDefinition($this->prefix('reader'))
			->setClass('h4kuna\Fio\Request\Read\Files\Json')
			->setFactory($this->prefix('@jsonTransactionFactory::createReader'));

		// FioPay
		$builder->addDefinition($this->prefix('fioPay'))
			->setClass('h4kuna\Fio\FioPay');

		// FioRead
		$builder->addDefinition($this->prefix('fioRead'))
			->setClass('h4kuna\Fio\FioRead');
	}

}
