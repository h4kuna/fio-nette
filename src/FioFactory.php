<?php declare(strict_types=1);

namespace h4kuna\Fio\Nette;

use h4kuna\Fio;

class /* readonly */ FioFactory
{

	public function __construct(
		private Fio\Pay\XMLFile $xmlFile,
		private Fio\Read\Json $json,
		private Fio\Account\AccountCollection $accountCollection,
		private Fio\Utils\Queue $queue,
	)
	{
	}


	public function createFioPay(string $name = ''): Fio\FioPay
	{
		return new Fio\FioPay($this->queue, $this->accountCollection->account($name), $this->xmlFile);
	}


	public function createFioRead(string $name = ''): Fio\FioRead
	{
		return new Fio\FioRead($this->queue, $this->accountCollection->account($name), $this->json);
	}

}
