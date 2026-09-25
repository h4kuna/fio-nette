<?php declare(strict_types = 1);

namespace h4kuna\Fio\Nette\DI;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use h4kuna\Dir\TempDir;
use h4kuna\Fio\Account\AccountCollection;
use h4kuna\Fio\Account\AccountCollectionFactory;
use h4kuna\Fio\Contracts\RequestBlockingServiceContract;
use h4kuna\Fio\Exceptions\MissingDependency;
use h4kuna\Fio\Nette\FioFactory;
use h4kuna\Fio\Pay\XMLFile;
use h4kuna\Fio\Read\Json;
use h4kuna\Fio\Read\TransactionFactory;
use h4kuna\Fio\Utils\FileRequestBlockingService;
use h4kuna\Fio\Utils\FioRequestFactory;
use h4kuna\Fio\Utils\Queue;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\Statement;
use Nette\DI\MissingServiceException;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use function assert;
use function is_string;

/**
 * @property-read Config $config
 */
class FioExtension extends CompilerExtension
{

	public function __construct(private ?string $tempDir = null)
	{
	}

	public function getConfigSchema(): Schema
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

	public function loadConfiguration(): void
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
			->setFactory(AccountCollectionFactory::class . '::create', [$this->config->accounts])
			->setType(AccountCollection::class)
			->setAutowired(false);
	}

	private function buildXmlFile(): void
	{
		$this->getContainerBuilder()
			->addDefinition($this->prefix('xml.import'))
			->setFactory(XMLFile::class)
			->setAutowired(false);
	}

	private function buildQueue(): void
	{
		try {
			$tempDir = $this->getContainerBuilder()->getDefinitionByType(TempDir::class);
		} catch (MissingServiceException $e) {
			$tmp = new Statement(TempDir::class, [$this->config->tempDir]);
			$tempDir = $this->getContainerBuilder()->addDefinition($this->prefix('tempDir'))
				->setFactory([$tmp, 'create'])
				->setAutowired(false);
		}

		try {
			$client = $this->getContainerBuilder()->getDefinitionByType(ClientInterface::class);
		} catch (MissingServiceException $e) {
			MissingDependency::checkGuzzlehttp();
			$client = $this->getContainerBuilder()->addDefinition($this->prefix('http.client'))
				->setFactory(Client::class)
				->setAutowired(false);
		}

		$this->getContainerBuilder()
			->addDefinition($this->prefix('request.blocking'))
			->setType(RequestBlockingServiceContract::class)
			->setFactory(FileRequestBlockingService::class, [$tempDir])
			->setAutowired(false);

		$this->getContainerBuilder()
			->addDefinition($this->prefix('queue'))
			->setFactory(Queue::class, [$client, $this->prefix('@request.factory'), $this->prefix('@request.blocking')])
			->setAutowired(false);
	}

	private function buildTransactionFactory(): void
	{
		$this->getContainerBuilder()
			->addDefinition($this->prefix('transaction.factory'))
			->setFactory(TransactionFactory::class)
			->setAutowired(false);
	}

	private function buildJsonReader(): void
	{
		$this->getContainerBuilder()
			->addDefinition($this->prefix('json'))
			->setFactory(Json::class)
			->setAutowired(false);
	}

	private function buildFioFactory(): void
	{
		$this->getContainerBuilder()
			->addDefinition($this->prefix('factory'))
			->setFactory(FioFactory::class)
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
		} catch (MissingServiceException $e) {
			$tryGuzzle = true;
		}

		try {
			$streamFactory = $this->getContainerBuilder()->getDefinitionByType(StreamFactoryInterface::class);
		} catch (MissingServiceException $e) {
			$tryGuzzle = true;
		}

		if ($tryGuzzle) {
			MissingDependency::checkGuzzlehttp();
			$httpFactory = $this->getContainerBuilder()
				->addDefinition($this->prefix('http.factory'))
				->setFactory(HttpFactory::class)
				->setAutowired(false);
			$streamFactory ??= $httpFactory;
			$requestFactory ??= $httpFactory;
		}

		$this->getContainerBuilder()
			->addDefinition($this->prefix('request.factory'))
			->setFactory(FioRequestFactory::class, [$requestFactory, $streamFactory])
			->setAutowired(false);
	}

}
