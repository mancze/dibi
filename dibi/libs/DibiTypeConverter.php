<?php


/**
 * dibi value converter. Converts PHP value to SQL and vice versa.
 *
 * @author     Michal Novák
 * @package    dibi
 */
class DibiTypeConverter extends DibiObject implements IDibiNativeTypeConverter
{
	
	/** @var DibiConnection */
	protected $connection = null;
	
	/** @var array|ArrayAccess cache of to conversion table */
	private $toConversionTable = array();
	
	/** @var array|ArrayAccess cache of from conversion table */
	private $fromConversionTable = array();
	
	
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
		
		// load conversion caches
		$config = $connection->getConfig("typeConverter");
		
		if (isset($config["from"])) {
			$this->fromConversionTable = $config["from"];
		}
		
		if (isset($config["to"])) {
			$this->toConversionTable = $config["to"];
		}
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
		return isset($this->fromConversionTable[$context->type]);
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
		if (is_object($value)) {
			$valueClass = get_class($value);
			
			return isset($this->toConversionTable[$valueClass]);
		}
		else {
			return false;
		}
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
		if (isset($this->fromConversionTable[$context->type])) {
			$callback = $this->fromConversionTable[$context->type];
			
			if (is_string($callback)) {
				if (class_exists($callback)) {
					// ctor
					return new $callback($dbValue);
				}
				else {
					// function
					return $callback($dbValue, $context);
				}
			}
			else {
				// NOTE: must use array_values as converted array is not indexed
				// and nonindex arrays are not valid callbacks
				$callback = array_values((array) $callback);
				return call_user_func($callback, $dbValue, $context);
			}
		}
		
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
		if (is_object($value)) {
			$valueClass = get_class($value);
			
			if (!isset($this->toConversionTable[$valueClass])) {
				throw new DibiNotSupportedException();
			}
			
			$method = $this->toConversionTable[$valueClass];
			
			if (is_string($method)) {
				// invoke method on value
				return $value->$method();
			}
			else {
				// NOTE: must use array_values as converted array is not indexed
				// and nonindex arrays are not valid callbacks
				$method = array_values((array) $method);
				
				if (count($method) == 1) {
					$method = array_pop($method);
				}
				else {
					// ensure autoload
					class_exists($method[0]);
				}
				
				return call_user_func($method, $value, $context);
			}
		}
		
		throw new DibiNotSupportedException();
	}
	
}
