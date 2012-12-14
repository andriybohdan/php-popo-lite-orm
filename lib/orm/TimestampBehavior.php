<?php

class TimestampBehavior extends ObjectBehavior {
	 
	public function createORM($orm) {
		$orm->addFields(array('created_at','updated_at'));
	}
	
	public function beforeInsert($event) {
		$now = time();
		$event->getORM()->setDbFieldValue($event->getObject(),'created_at',$now);
		$event->getORM()->setDbFieldValue($event->getObject(),'updated_at',$now);
	}
	
	public function beforeUpdate($updateEvent) {
		$updateEvent->addField('updated_at');
		$updateEvent->getORM()->setDbFieldValue($updateEvent->getObject(),'updated_at',time());
	}
	
}