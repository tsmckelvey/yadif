<?php
require_once 'Yadif_Exception.php';

class Yadif_Container
{
	const CONFIG_CLASS = 'class';

	const CONFIG_ARGUMENTS = 'arguments';

	protected $_container = array();

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

	public function getComponent($name = null)
	{
		$name = $this->_validateArg($name, '$name', 'string');

		if (!array_key_exists($name, $this->_container))
			throw new Yadif_Exception($name . ' not index in $this->_container');

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
