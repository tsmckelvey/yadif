<?php
require_once 'Yadif_Exception.php';

class Yadif_Container
{
	protected $_container = array();

	public function __construct($configuration = null)
	{
		if (!isset($configuration))
			return;

		if (!is_array($configuration)) {
			throw new Yadif_Exception('$configuration is not array');
		}

		foreach ($configuration as $componentClass => $instructions) {
			$this->addComponent( $componentClass );
		}
	}
	
	public function addComponent($componentClass = null)
	{
		if (!isset($componentClass))
			throw new Yadif_Exception('$componentClass not set');

		if (!is_string($componentClass))
			throw new Yadif_Exception('$componentClass is not string, is ' . gettype($componentClass));

		if (!class_exists($componentClass))
			throw new Yadif_Exception('Class' . $componentClass . ' not found');

		$this->_container[ $componentClass ] = new ReflectionClass( $componentClass );
	}

	public function getComponent($componentIndex = null)
	{
		if (!isset($componentIndex))
			throw new Yadif_Exception('$componentIndex not set');

		if (!is_string($componentIndex))
			throw new Yadif_Exception('$componentIndex is not string');

		if (!array_key_exists($componentIndex, $this->_container))
			throw new Yadif_Exception($componentIndex . ' does not exist in $this->_container');

		$componentReflection = $this->_container[ $componentIndex ];

		$componentParams = $componentReflection->getConstructor()->getParameters();

		// the arguments we'll call in the constructor
		$componentMethodArguments = array();

		foreach ($componentParams as $param) {
			$componentMethodArguments[] = $param->getClass()->newInstance();
		}

		if (empty($componentParams)) {
			$component = $componentReflection->newInstance();
		} else {
			$component = $componentReflection->newInstanceArgs( $componentMethodArguments );
		}

		return $component;
	}
}
