<?php

include dirname(__DIR__) . '/env.php';

abstract class Connection
{
	/**
	 * @var PDO $instance PDO Database connection object
	 */
	private static $instance;

	/**
	 * Retrieve a connection to the database.
	 *
	 * @return PDO PDO Database connection object
	 */
	public static function getInstance(): PDO
	{
		if (!isset(self::$instance))
		{
			self::$instance = new PDO(
				'mysql:host=' . DB_HOST .
				';dbname=' . DB_NAME,
				DB_USER,
				DB_PASSWORD
			);
		}

		return self::$instance;
	}
}
