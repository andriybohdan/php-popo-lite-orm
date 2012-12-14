<?php

class UpdateEvent extends BehaviorEvent { 
	
	public function __construct($orm, $object,$fields) {
		parent::__construct($orm, $object);
		$this->setFields($fields);
	}
		
	private $fields;
	
	public function setFields($fields) { 
		$this->fields=$fields;
	}
	
	public function getFields() { 
		return $this->fields;
	}
	
	public function addField($field) {
		if (!in_array($field,$this->fields))
			$this->fields[]=$field;
	}
	
	public function addFields($fields) {
		foreach ($fields as $field) {  
			if (!in_array($field,$this->fields))
				$this->fields[]=$field;
		}
	}
}
