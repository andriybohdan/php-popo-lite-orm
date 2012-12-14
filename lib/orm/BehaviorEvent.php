<?php
class BehaviorEvent {

	private $orm;
	
	public function setORM($orm) { 
		$this->orm=$orm;
	}
	
	public function getORM() { 
		return $this->orm;
	}
	
	private $object;
	
	public function setObject($object) { 
		$this->object=$object;
	}
	
	public function getObject() { 
		return $this->object;
	}
	
	public function __construct($orm,$object) { 
		$this->setORM($orm);
		$this->setObject($object);
	}
	
}