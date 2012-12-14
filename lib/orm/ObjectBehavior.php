<?php
class ObjectBehavior {

	public function createORM($orm) {}
	public function beforeSave($event) {}
	public function beforeInsert($event) {}
	public function beforeUpdate($updateEvent) {}
	public function afterSave($event) {}
	public function afterInsert($event) {}
	public function afterUpdate($updateEvent) {}
	public function afterFailedSave($event) {}
	public function beforeDelete($event) {}
	public function afterDelete($event) {}
	public function afterFailedDelete($event) {}
	
	
}