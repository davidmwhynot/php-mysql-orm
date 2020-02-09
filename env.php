<?php
// SETUP ENVIRONMENT

if (file_exists(dirname(__DIR__) . '/.env.php')) {
	include ".env.php";

	if (!defined('DB_USER')) {
		define('DB_USER', $DB_USER);
	}

	if (!defined('DB_PASSWORD')) {
		define('DB_PASSWORD', $DB_PASSWORD);
	}

	if (!defined('DB_HOST')) {
		define('DB_HOST', $DB_HOST);
	}

	if (!defined('DB_NAME')) {
		define('DB_NAME', $DB_NAME);
	}

} else {
	throw new Error("Missing .env.php configuration file.");
}
