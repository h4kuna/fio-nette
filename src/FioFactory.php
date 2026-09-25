<?php declare(strict_types = 1);

namespace h4kuna\Fio\Nette;

use h4kuna\Fio\Account\AccountCollection;
use h4kuna\Fio\FioPay;
use h4kuna\Fio\FioRead;
use h4kuna\Fio\Pay\XMLFile;
use h4kuna\Fio\Read\Json;
use h4kuna\Fio\Utils\Queue;

class /* readonly */ FioFactory
{

	public function __construct(
		private XMLFile $xmlFile,
		private Json $json,
		private AccountCollection $accountCollection,
		private Queue $queue,
	)
	{
	}

	public function createFioPay(string $name = ''): FioPay
	{
		return new FioPay($this->queue, $this->accountCollection->account($name), $this->xmlFile);
	}

	public function createFioRead(string $name = ''): FioRead
	{
		return new FioRead($this->queue, $this->accountCollection->account($name), $this->json);
	}

}
