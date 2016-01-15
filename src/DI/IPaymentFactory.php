<?php

namespace h4kuna\Fio\Nette\DI;

use h4kuna\Fio\Account;

interface IPaymentFactory
{

	/** @return \h4kuna\Fio\Request\Pay\PaymentFactory */
	public function create(Account\FioAccount $account);
}
