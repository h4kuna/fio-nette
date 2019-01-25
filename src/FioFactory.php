<?php declare(strict_types=1);

namespace h4kuna\Fio\Nette;

use h4kuna\Fio;
use Nette\DI;

class FioFactory
{

	/** @var Fio\Account\AccountCollection */
	private $accountCollecion;

	/** @var Fio\Request\IQueue */
	private $queue;

	/** @var DI\Container */
	private $container;

	public function __construct(Fio\Account\AccountCollection $accountCollecion, Fio\Request\IQueue $queue, DI\Container $container)
	{
		$this->accountCollecion = $accountCollecion;
		$this->queue = $queue;
		$this->container = $container;
	}


	public function createFioPay(string $name = ''): Fio\FioPay
	{
		return new Fio\FioPay($this->queue, $this->accountCollecion->account($name), $this->container->getByType(Fio\Request\Pay\XMLFile::class));
	}


	public function createFioRead(string $name = ''): Fio\FioRead
	{
		return new Fio\FioRead($this->queue, $this->accountCollecion->account($name), $this->container->getByType(Fio\Request\Read\Files\Json::class));
	}

}
