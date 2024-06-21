<?php

namespace h4kuna\Fio\Nette\Tests;

use h4kuna;
use h4kuna\Fio;
use Nette\DI;
use Tester\Assert;
use Tester\TestCase;

require __DIR__ . '/../bootsrap.php';

class ExtensionTest extends TestCase
{

	public function testNoConfig(): void
	{
		Assert::type(DI\Container::class, $this->createContainer());
	}


	public function testOneAccount(): void
	{
		$container = $this->createContainer([
			'account' => '123123/5050',
			'token' => 'token_test',
		]);
		$fioFactory = $container->getService('fio.factory');
		Assert::true($fioFactory->createFioRead() instanceof Fio\FioRead);

		$fioPay = $fioFactory->createFioPay();
		Assert::true($fioPay instanceof Fio\FioPay);
	}


	public function testMoreAccounts(): void
	{
		$container = $this->createContainer([
			'accounts' => [
				'my' => [
					'account' => '123123/5050',
					'token' => 'token_test',
				],
				'wife' => [
					'account' => '321654/0300',
					'token' => 'wife_token',
				],
			],
		]);
		/* @var $fioFactory Fio\Nette\FioFactory */
		$fioFactory = $container->getService('fio.factory');
		Assert::true($fioFactory->createFioRead() instanceof Fio\FioRead);

		// PAY
		$fioPay = $fioFactory->createFioPay();
		Assert::true($fioPay instanceof Fio\FioPay);
		Assert::same($fioPay->getAccount(), $fioFactory->createFioPay('my')->getAccount());

		$fioPay2 = $fioFactory->createFioPay('wife');
		Assert::same('321654', $fioPay2->getAccount()->getAccount());

		// READ
		Assert::true($fioFactory->createFioRead() instanceof Fio\FioRead);
	}


	/**
	 * @param array<string, mixed> $config
	 */
	private function createContainer(array $config = []): DI\Container
	{
		$tempDir = new h4kuna\Dir\TempDir(__DIR__ . '/../temp');
		$temp = $tempDir->getDir();

		$loader = new DI\ContainerLoader($temp, true);
		$class = $loader->load(function (DI\Compiler $compiler) use ($config, $tempDir): void {
			$compiler->addExtension('fio', new Fio\Nette\DI\FioExtension());

			$compiler->addConfig([
				'fio' => $config,
				'parameters' => [
					'tempDir' => $tempDir->getDir(),
				],
			],
			);
		}, md5(strval(microtime(true))));

		$container = new $class();
		assert($container instanceof DI\Container);

		return $container;
	}

}

(new ExtensionTest())->run();


