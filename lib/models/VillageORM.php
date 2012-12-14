<?

class VillageORM extends ObjectORM { 
	
	public function getFields() { 
		return array('name');
	}

	public function getBehaviors()
	{
		return array(new AutoIncrementBehavior(), new TimestampBehavior());
	}

}

?>