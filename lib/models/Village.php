<?

class Village {
	
	protected $id;

	public function setId($id) {
		$this->id = $id;
	}

	public function getId() {
		return $this->id;
	}
	
	protected $name;

	public function setName($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}
	
	public function validateName()
	{
		if (mb_strlen($this->getName())<=3)
			throw new ValidateException("Must be longer than 3 characters");
	}
	
}

?>