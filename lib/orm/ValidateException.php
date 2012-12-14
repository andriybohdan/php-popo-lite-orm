<?php

class ValidateException extends Exception {
	
	private $field = false;
	private $object;

	public function __construct($message, $object  = false, $field = false) {
		$this->field = $field;
		$this->object = $object;
		parent::__construct($message);
	}
	
	public function getField() { 
		return $this->field;
	}
	
	
	public function getObject() { 
		return $this->object;
	}
	
	public function getObjectErrors() { 
		return $this->getObject()->objectErrors;
	}
	
}
