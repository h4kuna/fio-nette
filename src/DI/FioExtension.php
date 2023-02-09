<?php declare(strict_types=1);

namespace h4kuna\Fio\Nette\DI;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use h4kuna\Dir\TempDir;
use h4kuna\Fio;
use Nette;
use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Psr\Http\Client\ClientInterface;

/**
 * @property-read Config $config
 */
class FioExtension extends CompilerExtension
{

	public function getConfigSchema(): Nette\Schema\Schema
	{
		$tempDir = $this->getContainerBuilder()->parameters['tempDir'] ?? '';
		if ($tempDir !== '') {
			$tempDir .= '/';
		}
		$tempDir .= 'h4kuna/fio';

		$config = new Config();
		$config->tempDir = $tempDir;

		return Expect::from($config);
	}


	public function loadConfiguration()
	{
		if ($this->config->accounts === []) {
			$this->config->accounts = [
				'default' => [
					'account' => $this->config->account,
					'token' => $this->config->token,
				],
			];
		}

		$this->buildAccountCollection();

		$this->buildXmlFile();

		$this->buildTransactionFactory();

		$this->buildJsonReader();

		$this->buildFioFactory();
	}


	public function beforeCompile(): void
	{
		$this->buildRequestFactory();
		$this->buildQueue();
	}


	private function buildAccountCollection(): void
	{
		$x = Fio\Account\AccountCollectionFactory::create($this->config->accounts);

		$this->getContainerBuilder()
			->addDefinition($this->prefix('accounts'))
			->setFactory('unserialize(?)', [serialize($x)])
			->setType(Fio\Account\AccountCollection::class)
			->setAutowired(false);
	}


	private function buildXmlFile(): void
	{
		$this->getContainerBuilder()
			->addDefinition($this->prefix('xml.import'))
			->setFactory(Fio\Pay\XMLFile::class)
			->setAutowired(false);
	}


	private function buildQueue(): void
	{
		try {
			$tempDir = $this->getContainerBuilder()->getDefinitionByType(TempDir::class);
		} catch (Nette\DI\MissingServiceException) {
			$tempDir = $this->getContainerBuilder()->addDefinition($this->prefix('tempDir'))
				->setFactory(TempDir::class, [$this->config->tempDir])
				->setAutowired(false);
		}

		try {
			$client = $this->getContainerBuilder()->getDefinitionByType(ClientInterface::class);
		} catch (Nette\DI\MissingServiceException) {
			Fio\Exceptions\MissingDependency::checkGuzzlehttp();
			$client = $this->getContainerBuilder()->addDefinition($this->prefix('http.client'))
				->setFactory(Client::class)
				->setAutowired(false);
		}

		$this->getContainerBuilder()
			->addDefinition($this->prefix('queue'))
			->setFactory(Fio\Utils\Queue::class, [$tempDir, $client, $this->prefix('@request.factory')])
			->setAutowired(false);
	}


	private function buildTransactionFactory(): void
	{
		$this->getContainerBuilder()
			->addDefinition($this->prefix('transaction.factory'))
			->setFactory(Fio\Read\TransactionFactory::class)
			->setAutowired(false);
	}


	private function buildJsonReader(): void
	{
		$this->getContainerBuilder()
			->addDefinition($this->prefix('json'))
			->setFactory(Fio\Read\Json::class)
			->setAutowired(false);
	}


	private function buildFioFactory(): void
	{
		$this->getContainerBuilder()
			->addDefinition($this->prefix('factory'))
			->setFactory(Fio\Nette\FioFactory::class)
			->setArguments([
				$this->prefix('@xml.import'),
				$this->prefix('@json'),
				$this->prefix('@accounts'),
				$this->prefix('@queue'),
			]);
	}


	private function buildRequestFactory(): void
	{
		Fio\Exceptions\MissingDependency::checkGuzzlehttp();

		try {
			$httpFactory = $this->getContainerBuilder()->getDefinitionByType(HttpFactory::class);
		} catch (Nette\DI\MissingServiceException) {
			$httpFactory = $this->getContainerBuilder()->addDefinition($this->prefix('http.factory'))
				->setFactory(HttpFactory::class)
				->setAutowired(false);
		}

		$this->getContainerBuilder()
			->addDefinition($this->prefix('request.factory'))
			->setFactory(Fio\Utils\GuzzleRequestFactory::class, [$httpFactory])
			->setAutowired(false);
	}

}
