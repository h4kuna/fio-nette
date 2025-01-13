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
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * @property-read Config $config
 */
class FioExtension extends CompilerExtension
{
	public function __construct(private ?string $tempDir = null) {

	}

	public function getConfigSchema(): Nette\Schema\Schema
	{
		$tempDir = $this->tempDir ?? $this->getContainerBuilder()->parameters['tempDir'] ?? '';
		assert(is_string($tempDir));

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
		if ($this->config->accounts === [] && $this->config->account !== '' && $this->config->token !== '') {
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
		$this->getContainerBuilder()
			->addDefinition($this->prefix('accounts'))
			->setFactory(Fio\Account\AccountCollectionFactory::class . '::create', [$this->config->accounts])
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
			$tmp = new Nette\DI\Definitions\Statement(TempDir::class, [$this->config->tempDir]);
			$tempDir = $this->getContainerBuilder()->addDefinition($this->prefix('tempDir'))
				->setFactory([$tmp, 'create'])
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
			->addDefinition($this->prefix('request.blocking'))
			->setType(Fio\Contracts\RequestBlockingServiceContract::class)
			->setFactory(Fio\Utils\FileRequestBlockingService::class, [$tempDir])
			->setAutowired(false);

		$this->getContainerBuilder()
			->addDefinition($this->prefix('queue'))
			->setFactory(Fio\Utils\Queue::class, [$client, $this->prefix('@request.factory'), $this->prefix('@request.blocking')])
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
		$streamFactory = $requestFactory = null;
		$tryGuzzle = false;
		try {
			$requestFactory = $this->getContainerBuilder()->getDefinitionByType(RequestFactoryInterface::class);
		} catch (Nette\DI\MissingServiceException) {
			$tryGuzzle = true;
		}

		try {
			$streamFactory = $this->getContainerBuilder()->getDefinitionByType(StreamFactoryInterface::class);
		} catch (Nette\DI\MissingServiceException) {
			$tryGuzzle = true;
		}

		if ($tryGuzzle) {
			Fio\Exceptions\MissingDependency::checkGuzzlehttp();
			$httpFactory = $this->getContainerBuilder()
				->addDefinition($this->prefix('http.factory'))
				->setFactory(HttpFactory::class)
				->setAutowired(false);
			$streamFactory ??= $httpFactory;
			$requestFactory ??= $httpFactory;
		}

		$this->getContainerBuilder()
			->addDefinition($this->prefix('request.factory'))
			->setFactory(Fio\Utils\FioRequestFactory::class, [$requestFactory, $streamFactory])
			->setAutowired(false);
	}

}
