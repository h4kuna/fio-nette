<?php

namespace h4kuna\Fio\Nette\DI;

use Nette\DI\CompilerExtension,
	Nette\Utils;

class FioExtension extends CompilerExtension
{

	public $defaults = [
		'account' => NULL,
		'token' => NULL,
		'accounts' => [],
		'temp' => '%tempDir%/fio',
		'transactionClass' => '\h4kuna\Fio\Response\Read\Transaction',
		'downloadOptions' => []
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

		// AccountCollection
		$builder->addDefinition($this->prefix('accounts'))
			->setClass('h4kuna\Fio\Account\AccountCollection')
			->setFactory('h4kuna\Fio\Account\AccountCollectionFactory::create', [$config['accounts']]);

		// XMLFile - lazy
		$builder->addDefinition($this->prefix('xmlFile'))
			->setClass('h4kuna\Fio\Request\Pay\XMLFile')
			->setArguments([$config['temp']]);

		// Queue
		$queue = $builder->addDefinition($this->prefix('queue'))
			->setClass('h4kuna\Fio\Request\Queue', [$config['temp']]);
		if ($config['downloadOptions']) {
			$setup = new \Nette\DI\Statement('?->setDownloadOptions(?)', [$queue, $config['downloadOptions']]);
			$queue->setSetup([$setup]);
		}

		// JsonTransactionFactory - lazy
		$builder->addDefinition($this->prefix('jsonTransactionFactory'))
			->setClass('h4kuna\Fio\Response\Read\JsonTransactionFactory')
			->setArguments([$config['transactionClass']]);

		// Reader - lazy
		$builder->addDefinition($this->prefix('reader'))
			->setClass('h4kuna\Fio\Request\Read\Files\Json');

		// FioFactory
		$builder->addDefinition($this->prefix('fioFactory'))
			->setClass('h4kuna\Fio\Nette\FioFactory');
	}

}
