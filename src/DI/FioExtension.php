<?php declare(strict_types=1);

namespace h4kuna\Fio\Nette\DI;

use h4kuna\Fio;
use Nette;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\Schema\Expect;
use Nette\Utils;

class FioExtension extends CompilerExtension
{

	public function getConfigSchema(): Nette\Schema\Schema
	{
		$tempDir = $this->getContainerBuilder()->parameters['tempDir'] ?? '/tmp';
		return Expect::structure([
			'account' => Expect::string(),
			'token' => Expect::string(),
			'accounts' => Expect::array([]),
			'tempDir' => Expect::string()
				->default($tempDir . DIRECTORY_SEPARATOR . 'fio'),
			'session' => Expect::bool(false),
			'transactionClass' => Expect::string(Fio\Response\Read\Transaction::class),
			'downloadOptions' => Expect::array([]),
		]);
	}


	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;

		if ($config->accounts === []) {
			$config->accounts['default'] = [
				'account' => $config->account,
				'token' => $config->token,
			];
		}

		Utils\FileSystem::createDir($config->tempDir);

		$this->buildAccountCollection($builder, $config->accounts);

		$this->buildXmlFile($builder, $config->tempDir);

		$this->buildQueue($builder, $config->tempDir, $config->downloadOptions);

		$this->buildTransactionFactory($builder, $config->transactionClass);

		$this->buildJsonReader($builder);

		$this->buildFioFactory($builder);
	}


	private function buildAccountCollection(ContainerBuilder $builder, array $accounts): void
	{
		$builder->addDefinition($this->prefix('accounts'))
			->setFactory(Fio\Account\AccountCollectionFactory::class . '::create', [$accounts]);
	}


	private function buildXmlFile(ContainerBuilder $builder, string $tempDir): void
	{
		$builder->addDefinition($this->prefix('xmlFile'))
			->setFactory(Fio\Request\Pay\XMLFile::class, [$tempDir]);
	}


	private function buildQueue(ContainerBuilder $builder, string $tempDir, array $downloadOptions): void
	{
		$queue = $builder->addDefinition($this->prefix('queue'))
			->setFactory(Fio\Request\Queue::class, [$tempDir]);
		if ($downloadOptions !== []) {
			$queue->addSetup('setDownloadOptions', [$downloadOptions]);
		}
	}


	private function buildTransactionFactory(ContainerBuilder $builder, string $transactionClass): void
	{
		$builder->addDefinition($this->prefix('jsonTransactionFactory'))
			->setFactory(Fio\Response\Read\JsonTransactionFactory::class, [$transactionClass]);
	}


	private function buildJsonReader(ContainerBuilder $builder): void
	{
		$builder->addDefinition($this->prefix('reader'))
			->setFactory(Fio\Request\Read\Files\Json::class);
	}


	private function buildFioFactory(ContainerBuilder $builder): void
	{
		$builder->addDefinition($this->prefix('fioFactory'))
			->setFactory(Fio\Nette\FioFactory::class);
	}

}
