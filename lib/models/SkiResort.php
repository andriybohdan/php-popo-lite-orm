<?

class SkiResort {
	
	/*
		it's optional to define object properties and access methods
		properties can be accessed via $object->propertyName 
	
	*/
		
	protected $village;
	public function getVillage()
	{
		if ($this->village===null && $this->villageId) {
			$villageOrm  = new VillageORM();
			$this->village  = $villageOrm->findById($this->villageId);
		}
		return $this->village;
	}
	
	public function validateName()
	{
		if (mb_strlen($this->name)<=3)
			throw new ValidateException("Must be longer than 3 characters");
	}	

	public function validateVillageId()
	{
		if ($this->getVillage()==null)
			throw new ValidateException("Village not chosen");
	}	
}

?>