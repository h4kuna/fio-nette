<?php

namespace h4kuna\Fio\Nette;

use h4kuna\Fio,
	Nette\DI;

/**
 * @author Milan Matějček
 */
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

	public function createFioPay($name = NULL)
	{
		return new Fio\FioPay($this->queue, $this->getAccount($name), $this->container->getByType('h4kuna\Fio\Request\Pay\XMLFile'));
	}

	public function createFioRead($name = NULL)
	{
		return new Fio\FioRead($this->queue, $this->getAccount($name), $this->container->getByType('h4kuna\Fio\Request\Read\Files\Json'));
	}

	private function getAccount($name)
	{
		if ($name) {
			return $this->accountCollecion->get($name);
		}
		return $this->accountCollecion->getDefault();
	}

}
