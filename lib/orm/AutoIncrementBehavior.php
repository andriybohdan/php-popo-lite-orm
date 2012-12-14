<?php
class AutoIncrementBehavior extends ObjectBehavior {
	 
	public function afterInsert($event) {
		$orm = $event->getORM();
		$orm->setObjectId($event->getObject(),mysql_insert_id(init_DB_Conn()));
	}
	
}