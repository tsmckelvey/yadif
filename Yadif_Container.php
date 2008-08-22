<?php
require_once 'Yadif_Exception.php';

class Yadif_Container
{
	const CONFIG_CLASS = 'class';

	const CONFIG_ARGUMENTS = 'arguments';

	// container of component configurations
	protected $_container = array();

	// parameters which have been set, expected to be bound
	protected $_parameters = array();

	protected function _validateArg($arg, $argVar, $type)
	{
		$isTypes = array('array', 'bool', 'float', 'int', 'null', 'numeric', 'object',
						 'resource', 'scalar', 'string');

		if (in_array($type, $isTypes)) $isType = "is_$type";

		if (!$isType($arg))
			throw new Yadif_Exception($argVar . ' must be ' . $type . ', is ' . gettype($arg));

		return $arg;
	}

	public function __construct(array $config = null)
	{
		if (isset($array)) {
			$config = $this->_validateArg($config, '$config', 'array');

			foreach ($config as $componentName => $componentConfig) {
				$this->addComponent( $componentName, $componentConfig );
			}
		}
	}
	
	public function addComponent($name = null, array $config = null)
	{
		$name = $this->_validateArg($name, '$name', 'string');

		$config = $this->_validateArg($config, '$config', 'array');

		if (!class_exists( $config[self::CONFIG_CLASS] ))
			throw new Yadif_Exception( 'Class ' . $config[self::CONFIG_CLASS] . ' not found' );

		if (!is_array($config[ self::CONFIG_ARGUMENTS ])) $config[ self::CONFIG_ARGUMENTS ] = array();

		$this->_container[ $name ] = $config;

		return $this;
	}

	public function bindParam($param, $value)
	{
		$param = $this->_validateArg($param, '$param', 'string');

		$value = $this->_validateArg($value, '$value', 'string');

		if ($param{0} != ':')
			throw new Yadif_Exception($param . ' must start with a colon (:)');

		$this->_parameters[$param] = $value;
	}

	public function getComponent($name = null)
	{
		$name = $this->_validateArg($name, '$name', 'string');

		// if we're trying to "getParameter" (see the loop below)
		if (array_key_exists($name, $this->_parameters)) {
			return $this->_parameters[ $name ];
		}

		if (!array_key_exists($name, $this->_container))
			throw new Yadif_Exception($name . ' not index in $this->_container or $this->_parameters');

		$component = $this->_container[ $name ];

		$injection = array();

		// array of methods to call arguments on
		foreach ($component[ self::CONFIG_ARGUMENTS ] as $injectionMethod => $injectionArgs) {
			// actual arguments to call on the methods
			foreach ($injectionArgs as $arg) {
				$injection[] = $this->getComponent($arg);
			}
		}

		$componentReflection = new ReflectionClass($component[ self::CONFIG_CLASS ]);

		if (empty($injection)) {
			$component = $componentReflection->newInstance();
		} else {
			$component = $componentReflection->newInstanceArgs( $injection );
		}

		return $component;
	}
}
