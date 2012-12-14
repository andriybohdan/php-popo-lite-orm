<?

class SkiResortORM extends ObjectORM { 
	
	public function getFields() { 
		return array('name', 'village_id');
	}

	public function getBehaviors()
	{
		return array(new AutoIncrementBehavior(), new TimestampBehavior());
	}

}

?>