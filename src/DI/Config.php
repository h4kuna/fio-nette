<?php declare(strict_types=1);

namespace h4kuna\Fio\Nette\DI;

final class Config
{

	public string $account = '';

	public string $token = '';

	/**
	 * @var array<array{account: string, token: string}>
	 */
	public array $accounts = [];

	public string $tempDir;

}


