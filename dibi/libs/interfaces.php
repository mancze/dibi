<?php

/**
 * This file is part of the "dibi" - smart database abstraction layer.
 *
 * Copyright (c) 2005 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */



/**
 * Provides an interface between a dataset and data-aware components.
 * @package    dibi
 */
interface IDataSource extends Countable, IteratorAggregate
{
	//function IteratorAggregate::getIterator();
	//function Countable::count();
}



/**
 * dibi driver interface.
 * @package    dibi
 */
interface IDibiDriver
{

	/**
	 * Connects to a database.
	 * @param  array
	 * @return void
	 * @throws DibiException
	 */
	function connect(array &$config);

	/**
	 * Disconnects from a database.
	 * @return void
	 * @throws DibiException
	 */
	function disconnect();

	/**
	 * Internal: Executes the SQL query.
	 * @param  string      SQL statement.
	 * @return IDibiResultDriver|NULL
	 * @throws DibiDriverException
	 */
	function query($sql);

	/**
	 * Gets the number of affected rows by the last INSERT, UPDATE or DELETE query.
	 * @return int|FALSE  number of rows or FALSE on error
	 */
	function getAffectedRows();

	/**
	 * Retrieves the ID generated for an AUTO_INCREMENT column by the previous INSERT query.
	 * @return int|FALSE  int on success or FALSE on failure
	 */
	function getInsertId($sequence);

	/**
	 * Begins a transaction (if supported).
	 * @param  string  optional savepoint name
	 * @return void
	 * @throws DibiDriverException
	 */
	function begin($savepoint = NULL);

	/**
	 * Commits statements in a transaction.
	 * @param  string  optional savepoint name
	 * @return void
	 * @throws DibiDriverException
	 */
	function commit($savepoint = NULL);

	/**
	 * Rollback changes in a transaction.
	 * @param  string  optional savepoint name
	 * @return void
	 * @throws DibiDriverException
	 */
	function rollback($savepoint = NULL);

	/**
	 * Returns the connection resource.
	 * @return mixed
	 */
	function getResource();

	/**
	 * Returns the connection reflector.
	 * @return IDibiReflector
	 */
	function getReflector();

	/**
	 * Encodes data for use in a SQL statement.
	 * @param  string    value
	 * @param  string    type (dibi::TEXT, dibi::BOOL, ...)
	 * @return string    encoded value
	 * @throws InvalidArgumentException
	 */
	function escape($value, $type);

	/**
	 * Encodes string for use in a LIKE statement.
	 * @param  string
	 * @param  int
	 * @return string
	 */
	function escapeLike($value, $pos);

	/**
	 * Injects LIMIT/OFFSET to the SQL query.
	 * @param  string &$sql  The SQL query that will be modified.
	 * @param  int $limit
	 * @param  int $offset
	 * @return void
	 */
	function applyLimit(&$sql, $limit, $offset);

}





/**
 * dibi result set driver interface.
 * @package    dibi
 */
interface IDibiResultDriver
{

	/**
	 * Returns the number of rows in a result set.
	 * @return int
	 */
	function getRowCount();

	/**
	 * Moves cursor position without fetching row.
	 * @param  int      the 0-based cursor pos to seek to
	 * @return boolean  TRUE on success, FALSE if unable to seek to specified record
	 * @throws DibiException
	 */
	function seek($row);

	/**
	 * Fetches the row at current position and moves the internal cursor to the next position.
	 * @param  bool     TRUE for associative array, FALSE for numeric
	 * @return array    array on success, nonarray if no next record
	 * @internal
	 */
	function fetch($type);

	/**
	 * Frees the resources allocated for this result set.
	 * @param  resource  result set resource
	 * @return void
	 */
	function free();

	/**
	 * Returns metadata for all columns in a result set.
	 * @return array of {name, nativetype [, table, fullname, (int) size, (bool) nullable, (mixed) default, (bool) autoincrement, (array) vendor ]}
	 */
	function getResultColumns();

	/**
	 * Returns the result set resource.
	 * @return mixed
	 */
	function getResultResource();

	/**
	 * Decodes data from result set.
	 * @param  string    value
	 * @param  string    type (dibi::BINARY)
	 * @return string    decoded value
	 * @throws InvalidArgumentException
	 */
	function unescape($value, $type);

}





/**
 * dibi driver reflection.
 *
 * @author     David Grudl
 * @package    dibi
 */
interface IDibiReflector
{

	/**
	 * Returns list of tables.
	 * @return array of {name [, (bool) view ]}
	 */
	function getTables();

	/**
	 * Returns metadata for all columns in a table.
	 * @param  string
	 * @return array of {name, nativetype [, table, fullname, (int) size, (bool) nullable, (mixed) default, (bool) autoincrement, (array) vendor ]}
	 */
	function getColumns($table);

	/**
	 * Returns metadata for all indexes in a table.
	 * @param  string
	 * @return array of {name, (array of names) columns [, (bool) unique, (bool) primary ]}
	 */
	function getIndexes($table);

	/**
	 * Returns metadata for all foreign keys in a table.
	 * @param  string
	 * @return array
	 */
	function getForeignKeys($table);

}



/**
 * dibi type conversion.
 *
 * @author     Michal Novák
 * @package    dibi
 */
interface IDibiTypeConverter
{
	
	
	/**
	 * Tests if converter is capable of conversion of database value to its originated value.
	 * 
	 * @param mixed $dbValue value retrieved from database
	 * @param DibiColumnInfo $context column info of the value
	 * @return bool true if value can be converted, false otherwise
	 */
	public function canConvertFrom($dbValue, DibiColumnInfo $context);
	
	
	/**
	 * Tests if converter is capable of conversion of value to its database value (value for SQL).
	 * 
	 * @param mixed $value value passed to dibi from user's code
	 * @param string $context modifier to which should be value converted
	 * @return bool true if value can be converted, false otherwise
	 */
	public function canConvertTo($value, $context = null);
	
	
	/**
	 * Converts database value to it's true type.
	 * 
	 * @param mixed $dbValue value retrieved from database
	 * @param DibiColumnInfo $context column info of the value
	 * @return mixed converted value
	 * @throws DibiNotSupportedException when conversion is invalid
	 */
	public function convertFrom($dbValue, DibiColumnInfo $context);
	
	
	/**
	 * Converts value from user's code (any object) to SQL value as it will appear in query.
	 * 
	 * In addition method may change context (dibi modifier of the value). This allows the user
	 * to pass custom modifiers to dibi to hint user's type converter.
	 * 
	 * Modifier might be not passed.
	 * 
	 * @param mixed $value value retrieved from database
	 * @param string $context dibi modifier to which value should convert
	 * @return mixed converted value
	 * @throws DibiNotSupportedException when conversion is invalid
	 */
	public function convertTo($value, &$context = null);
	
}



/**
 * dibi type conversion.
 *
 * @author     Michal Novák
 * @package    dibi
 */
interface IDibiNativeTypeConverter extends IDibiTypeConverter
{
	
	/**
	 * Used for DibiConnection injection. This method should be called once and it should ensure
	 * that.
	 * 
	 * @param DibiConnection $connection
	 * @throws InvalidStateException when connection is injected multiple times
	 * @throws InvalidArgumentException when empty argument is passed
	 * @return void
	 */
	public function injectConnection(DibiConnection $connection);
	
}
