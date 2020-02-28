<?php

final class Database
{
	/**
	 * @var PDO $pdo pdo connection object
	 */
	private PDO $pdo;

	/**
	 * @var string $db_name name of the database to connect to
	 */
	private string $db_name;

	private const SCHEMA_QUERY =
		'SELECT
			*
		FROM
			information_schema.columns
		WHERE
			table_name = :table_name
	';

	private const FK_QUERY =
		'SELECT
			B.*
		FROM
			information_schema.TABLE_CONSTRAINTS AS A
		INNER JOIN
			information_schema.KEY_COLUMN_USAGE AS B
		ON
			A.CONSTRAINT_CATALOG = B.CONSTRAINT_CATALOG AND
			A.CONSTRAINT_SCHEMA = B.CONSTRAINT_SCHEMA AND
			A.CONSTRAINT_NAME = B.CONSTRAINT_NAME AND
			A.TABLE_SCHEMA = B.TABLE_SCHEMA AND
			A.TABLE_NAME = B.TABLE_NAME
		WHERE
			A.CONSTRAINT_TYPE = "FOREIGN KEY" AND
			A.TABLE_NAME = :table_name AND
			A.TABLE_SCHEMA = :table_schema_name
	';

	public function __construct()
	{
		$this->db_name = DB_NAME;

		$this->pdo = Connection::getInstance();
	}

	/**
	 * @return PDO connection to the database
	 */
	public function getConnection(): PDO
	{
		return $this->pdo;
	}

	/**
	 * @param string $table table to retrieve the schema for
	 *
	 * @return Schema object containing the various attributes for the table
	 */
	// public function getTableSchema(string $table): Schema
	public function getTableSchema(string $table): array
	{
		// get metadata for the table's columns
		$query = $this->pdo->prepare(Database::SCHEMA_QUERY);
		$query->execute([':table_name' => $table]);

		// check if schema was found
		if ($query->rowCount() === 0)
		{
			throw new

			InvalidArgumentException("No schema found for table $table.");
		}

		$schema = $query->fetchAll(PDO::FETCH_ASSOC);

		return $schema;
	}

	/**
	 * @param string $table table to retrieve foreign key metatdata for
	 *
	 * @return ForeignKey[] array of foreign keys for the provided table name
	 */
	public function getForeignKeys(string $table): array
	{
		// get foreign key's metadata for the table's columns
		$query = $this->pdo->prepare(Database::FK_QUERY);
		$query->execute([
			':table_name' => $table,
			':table_schema_name' => $this->db_name,
		]);

		$foreign_keys = $query->fetchAll(PDO::FETCH_ASSOC);

		$query = null;

		return $foreign_keys;
	}
}
