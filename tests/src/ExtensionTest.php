<?php declare(strict_types = 1);

namespace h4kuna\Fio\Nette\Tests;

use h4kuna\Dir\TempDir;
use h4kuna\Fio\Nette\DI\FioExtension;
use h4kuna\Fio\Nette\FioFactory;
use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Tester\Assert;
use Tester\TestCase;
use function md5;
use function microtime;
use function strval;

require __DIR__ . '/../bootstrap.php';

class ExtensionTest extends TestCase
{

	public function testNoConfig(): void
	{
		Assert::type(Container::class, $this->createContainer());
	}

	public function testOneAccount(): void
	{
		$container = $this->createContainer([
			'account' => '123123/5050',
			'token' => 'token_test',
		]);

		/** @var FioFactory $fioFactory */
		$fioFactory = $container->getService('fio.factory');
		Assert::type(FioFactory::class, $fioFactory);
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
		/** @var FioFactory $fioFactory */
		$fioFactory = $container->getService('fio.factory');
		Assert::type(FioFactory::class, $fioFactory);

		// PAY
		$fioPay = $fioFactory->createFioPay();
		Assert::same($fioPay->getAccount(), $fioFactory->createFioPay('my')->getAccount());

		$fioPay2 = $fioFactory->createFioPay('wife');
		Assert::same('321654', $fioPay2->getAccount()->getAccount());
	}

	/**
	 * @param array<string, mixed> $config
	 */
	private function createContainer(array $config = []): Container
	{
		$tempDir = new TempDir(__DIR__ . '/../temp');
		$temp = $tempDir->getDir();

		$loader = new ContainerLoader($temp, true);
		/** @var class-string<Container> $class */
		$class = $loader->load(static function (Compiler $compiler) use ($config, $tempDir): null {
			$compiler->addExtension('fio', new FioExtension());

			$compiler->addConfig([
				'fio' => $config,
				'parameters' => [
					'tempDir' => $tempDir->getDir(),
				],
			]);

			return null;
		}, md5(strval(microtime(true))));

		return new $class();
	}

}

(new ExtensionTest())->run();
