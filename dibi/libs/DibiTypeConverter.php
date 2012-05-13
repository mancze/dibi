<?php


/**
 * dibi value converter. Converts PHP value to SQL and vice versa.
 *
 * @author     Michal Novák
 * @package    dibi
 */
class DibiTypeConverter extends DibiObject implements IDibiTypeConverter
{
	
	/** @var DibiConnection */
	protected $connection = null;
	
	
	public function __construct() { }
	

	/**
	 * Used for DibiConnection injection. This method should be called once and it should ensure
	 * that.
	 * 
	 * @param DibiConnection $connection
	 * @throws InvalidStateException when connection is injected multiple times
	 * @throws InvalidArgumentException when empty argument is passed
	 * @return void
	 */
	public function injectConnection(DibiConnection $connection) {
		if (!empty($this->connection)) {
			throw new InvalidStateException("DibiConnection already injected.");
		}
		
		if (empty($connection)) {
			throw new InvalidArgumentException("Connection cannot be null.");
		}
		
		$this->connection = $connection;
	}
	
	
	/**
	 * Tests if converter is capable of conversion of database value to its originated value.
	 * 
	 * @param mixed $dbValue value retrieved from database
	 * @param DibiColumnInfo $context column info of the value
	 * @return bool true if value can be converted, false otherwise
	 */
	public function canConvertFrom($dbValue, DibiColumnInfo $context)
	{
		return false;
	}
	
	
	/**
	 * Tests if converter is capable of conversion of value to its database value (value for SQL).
	 * 
	 * @param mixed $value value passed to dibi from user's code
	 * @param string $context modifier to which should be value converted
	 * @return mixed converted value
	 */
	public function canConvertTo($value, $context = null)
	{
		return false;
	}
	
	
	/**
	 * Converts database value to it's true type.
	 * 
	 * @param mixed $dbValue value retrieved from database
	 * @param DibiColumnInfo $context column info of the value
	 * @return bool true if value can be converted, false otherwise
	 */
	public function convertFrom($dbValue, DibiColumnInfo $context)
	{
		throw new DibiNotSupportedException();
	}
	
	
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
	 */
	public function convertTo($value, &$context = null)
	{
		throw new DibiNotSupportedException();
	}
	
}
