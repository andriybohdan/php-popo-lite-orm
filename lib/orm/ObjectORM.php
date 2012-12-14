<?php

class ObjectORM {
	
	// methods which can be overriden
	
	protected function getFields() { 
		return array();
	}
	
	protected function getBehaviors() { 
		return false;
	}
	
	// public methods 
	
	public function __construct($tableName = false) {
		if ($tableName!=false) { 
			$this->tableName = $tableName;
		} 
		$dbFields = $this->getFields();
		$this->addFields($dbFields);
		$this->callEvent('createORM',$this);
	}
	
	public function getTableName() {
		if ($this->tableName===null) { 
			$this->tableName = $this->getTableNameFromSaverClass();
		} 
		return $this->tableName;
	}
	
	public function addFields($fields) { 
		$this->dbFields = array_merge($this->dbFields,$fields);
	}
	
	public function setTableName($tableName) { 
		$this->tableName = $tableName;
	}
	
	public function save($object,$fields = false){
		if(!is_object($object)){
			return false;
		}

		$event = new BehaviorEvent($this, $object);
		$this->callEvent('beforeSave',$event);
		if($this->isNewObject($object)){
			$done = $this->insert($object);
		}else{
			$done = $this->update($object,$fields);
		}

		if($done){
			$this->callEvent('afterSave',$event);
		}else{
			$this->callEvent('afterFailedSave',$event);
			return false;
		}

		return true;

	}
	
	public function isNewObject($object) { 
		if (method_exists($object,'getIsNewObject')) { 
			return $object->getIsNewObject();
		} if (property_exists($object,'isNewObject')) { 
			return $object->isNewObject;
		} else {
			// by default object is considered as new
			$this->setNewObject($object,true);
			return true;
		}
	}
	
	public function setNewObject($object,$isNew) { 
		if (method_exists($object,'setIsNewObject')) { 
			$object->setIsNewObject($isNew);
		} else {
			$object->isNewObject = $isNew; 
		}
	}

	public function insert($object){
		if(!is_object($object)){
			return false;
		}

		$event = new BehaviorEvent($this, $object);
		$this->callEvent('beforeInsert',$event);
		
		$this->validate($object);
		
		$dbCon	= init_DB_Conn();
		$fieldValues = $this->getAllDbFieldValues($object);
	
		$quotedValues = $this->quoteValues($fieldValues,$dbCon);
		$query = 'INSERT INTO `' . $this->getTableName() .'` '
				 	.'('.implode(',',array_keys($fieldValues)).') ' .
				 ' VALUES (' .implode(',',$quotedValues). ')';

		ErrorHandler::logDebug(__METHOD__." exec query: ".$query);
		if (mysql_query($query,$dbCon)) { 
			$this->setNewObject($object,false);
			$this->callEvent('afterInsert',$event);
			return true;
		} else {
			throw new DBException(mysql_error($dbCon));
		}
	}
	
	public function update($object,$fields = false,$validate = true){
		if(!is_object($object)){
			return false;
		}

		if ($fields === false) { 
			$fields = $this->getDbFields();
		}
		
		$updateEvent = new UpdateEvent($this, $object, $fields);
		$this->callEvent('beforeUpdate',$updateEvent);
		$fields = $updateEvent->getFields();
		
		if ($validate) 
			$this->validate($object);
		
		$dbCon	= init_DB_Conn();

		$fieldValues = $this->getDbFieldValues($object,$fields);
		$quotedValues = $this->quoteValues($fieldValues,$dbCon);
		$sets = array();
		foreach ($quotedValues as $field => $quotedVal ) { 
			$sets[]="`$field`=".$quotedVal;
		}
		$query = 'UPDATE `' . $this->getTableName() .'`'
				 	.' SET '.implode(',',$sets).
					' WHERE `'.$this->getIdField().'`='.$this->quoteValue($this->getObjectId($object));

		ErrorHandler::logDebug(__METHOD__." exec query: ".$query);
		if (mysql_query($query,$dbCon)) { 
			$this->callEvent('afterUpdate',$updateEvent);
			return true;
		} else {
			throw new DBException(mysql_error($dbCon));
		}
	}
	
	public function findById($id) { 
		return $this->findOne(array($this->getIdField()=>$id));
	}
	
	public function findOne($conditions = false,$order = false,$limit = false, $offset = false) {
		$dbCon	= init_DB_Conn();
		$whereStr = $this->getWhereFromConditions($conditions,$dbCon);
		$fields = $this->getAllDbFields();
		$fieldsWithAlias = array_map(create_function('$f','return "`'.$this->getTableName().'`.`$f`";'),$fields);
		$query = "SELECT ".implode(',',$fieldsWithAlias)." FROM `".$this->getTableName()."`"
			.($whereStr!=false?" WHERE ".$whereStr:"")
			.($order!=false?" ORDER BY $order":"")
			.($limit!=false?" LIMIT $limit":" LIMIT 1")
			.($offset!=false?" OFFSET $offset":"");
		ErrorHandler::logDebug(__METHOD__." queryRow: ".$query);
		$res = mysql_query($query,$dbCon);
		if ($res) { 
			$row = mysql_fetch_assoc($res);
			mysql_free_result($res);
		} else { 
			throw new DBException(mysql_error($dbCon));
		}
		if ($row==false) 
			return null;
		$object = $this->newObject();
		$this->setNewObject($object,false);
		$this->setObjectId($object,$row[$this->getIdField()]);
		$this->setDbFieldValues($object,$row);
		return $object;
	}
	
	public function findAll($conditions = false,$order = false,$limit = false, $offset = false, $group = false,$having = false) {
		$dbCon	= init_DB_Conn();
		$whereStr = $this->getWhereFromConditions($conditions,$dbCon);
		$fields = $this->getAllDbFields();
		$fieldsWithAlias = array_map(create_function('$f','return "`'.$this->getTableName().'`.`$f`";'),$fields);
		$query = "SELECT ".implode(',',$fieldsWithAlias)." FROM `".$this->getTableName()."`"
			.($whereStr!=false?" WHERE ".$whereStr:"")
			.($group!=false?" GROUP BY $group":"")
			.($having!=false?" HAVING $having":"")
			.($order!=false?" ORDER BY $order":"")
			.($limit!=false?" LIMIT $limit":"")
			.($offset!=false?" OFFSET $offset":"");
		ErrorHandler::logDebug(__METHOD__." queryAll: ".$query);
		$res = mysql_query($query,$dbCon);
		if ($res) { 
			$allObjects = array();
			while ($row = mysql_fetch_assoc($res)) {
				$object = $this->newObject();
				$this->setNewObject($object,false);
				$this->setObjectId($object,$row[$this->getIdField()]);
				$this->setDbFieldValues($object,$row);
				$allObjects[] = $object; 
			}
			mysql_free_result($res);
			return $allObjects;
		} else { 
			throw new DBException(mysql_error($dbCon));
		}
	}
	
	public function findScalar($select, $from = false, $conditions = false,$order = false,$offset = false, $group = false,$having = false)
	{
		$row=$this->findRows($select,$from,$conditions,$order,1,$offset,$group,$having);
		return array_shift($row[0]);
	}
	
	public function findRow($select, $from = false, $conditions = false,$order = false,$offset = false, $group = false,$having = false) {
		return $this->findRows($select,$from,$conditions,$order,1,$offset,$group,$having);
	}
	
	public function findRows($select, $from = false, $conditions = false,$order = false,$limit = false, $offset = false, $group = false,$having = false) {
		$dbCon	= init_DB_Conn();
		$whereStr = $this->getWhereFromConditions($conditions,$dbCon);
		$query = "SELECT ".$select." FROM ".($from==false?"`".$this->getTableName()."`":$from)
			.($whereStr!=false?" WHERE ".$whereStr:"")
			.($group!=false?" GROUP BY $group":"")
			.($having!=false?" HAVING $having":"")
			.($order!=false?" ORDER BY $order":"")
			.($limit!=false?" LIMIT $limit":"")
			.($offset!=false?" OFFSET $offset":"");
		ErrorHandler::logDebug(__METHOD__." queryAll: ".$query);
		$res = mysql_query($query,$dbCon);
		if ($res) { 
			$allRows = array();
			while ($row = mysql_fetch_assoc($res)) {
				$allRows[] = $row; 
			}
			mysql_free_result($res);
			return $allRows;
		} else { 
			throw new DBException(mysql_error($dbCon));
		}
	}
	
	public function updateAll($fieldValues, $conditions = false) {
		$dbCon	= init_DB_Conn();
		$whereStr = $this->getWhereFromConditions($conditions,$dbCon);
		$fields = $this->getDbFields();
		$updatedFields =  array_intersect_key($fieldValues,array_combine($fields,array_fill(0,count($fields),1)));
		$sets = array();
		foreach ($updatedFields as $field => $val) { 
			$sets[] = "`$field`=".$this->quoteValue($val,$dbCon);
		}
		$query = "UPDATE `".$this->getTableName()."`"
			." SET ".implode(',',$sets)
			.($whereStr!=false?" WHERE ".$whereStr:"");
		ErrorHandler::logDebug(__METHOD__." exec: ".$query);
		if (mysql_query($query,$dbCon)) { 
			return mysql_affected_rows($dbCon);
		} else {
			throw new DBException(mysql_error($dbCon));
		}
	}	
	
	public function countAll($conditions = false) {
		$dbCon	= init_DB_Conn();
		$whereStr = $this->getWhereFromConditions($conditions,$dbCon);
		$query = "SELECT COUNT(*) as c FROM `".$this->getTableName()."`"
			.($whereStr!=false?" WHERE ".$whereStr:"");
		ErrorHandler::logDebug(__METHOD__." queryCount: ".$query);
		$res = mysql_query($query,$dbCon);
		if ($res) { 
			$row = mysql_fetch_assoc($res);
			mysql_free_result($res);
		} else { 
			throw new DBException(mysql_error($dbCon));
		}
		if (empty($row))
			throw new DBException("Couldn't get count - empty result in method ".__METHOD__);
		return $row['c'];
	}
	
	public function delete($object) { 
		$event = new BehaviorEvent($this, $object);
		$this->callEvent('beforeDelete',$event);
		$res = $this->deleteById($this->getObjectId($object));
		if ($res===1) { 
			$this->callEvent('afterDelete',$event);
			$this->setNewObject($object,true);
		} else { 
			$this->callEvent('afterFailedDelete',$event);
		}
	}
	
	public function deleteById($id) { 
		return $this->deleteOne(array($this->getIdField()=>$id));
	}
	
	public function deleteOne($conditions = false,$order = false,$limit = false, $offset = false) {
		return $this->deleteAll($conditions,$order,($limit==false?1:$limit),$offset);
	}
	
	public function deleteAll($conditions = false,$order = false,$limit = false, $offset = false) {
		$dbCon	= init_DB_Conn();
		$whereStr = $this->getWhereFromConditions($conditions,$dbCon);
		$query = "DELETE FROM `".$this->getTableName()."`"
			.($whereStr!=false?" WHERE ".$whereStr:"")
			.($order!=false?" ORDER BY $order":"")
			.($limit!=false?" LIMIT $limit":"")
			.($offset!=false?" OFFSET $offset":"");
		ErrorHandler::logDebug(__METHOD__." exec delete: ".$query);
		if (mysql_query($query,$dbCon)) { 
			return mysql_affected_rows($dbCon);
		} else {
			throw new DBException(mysql_error($dbCon));
		}
	}
	
	public function intersectFieldValues($values, $fields = null) { 
		if ($fields===null) { 
			$fields = $this->getAllDbFields();
		}
		return array_intersect_key($values,array_combine($fields,array_fill(0,count($fields),1)));
	}
	
	public function getObjectErrors($object) { 
		if (property_exists($object,'objectErrors')) { 
			return $object->objectErrors;
		} else { 
			return new ObjectErrors($object);
		}
	} 
	
	public function validate($object) {
		$objectErrors = $this->getObjectErrors($object);
		if (method_exists($object,'preFieldsValidate')) {
			try {  
				$object->preFieldsValidate($objectErrors);
			} catch (ValidateException $ex) {
				$objectErrors->add($ex->getMessage(),$ex->getField());
			}
		}
		$fields = $this->getDbFields();
		if (!empty($fields)) { 
			foreach ($fields as $field) { 
				$validatorName = $this->getValidatorNameByField($field);
				if (method_exists($object,$validatorName)) { 
					try {  
						$object->$validatorName();
					} catch (ValidateException $ex) { 
						$objectErrors->add($ex->getMessage(),$field);
					}
				}
			}
		}
		if (method_exists($object,'postFieldsValidate')) {
			try {  
				$object->postFieldsValidate($objectErrors,$this);
			} catch (ValidateException $ex) { 
				$objectErrors->add($ex->getMessage(),$ex->getField());
			}
		}
		if ($objectErrors->isError()) { 
			throw new ValidateException("Please check validation errors below and correct",$object);
		}
	}	
	
	// events to override in descendants
	
	protected function createORM($orm) {}
	protected function beforeSave($event) {}
	protected function beforeInsert($event) {}
	protected function beforeUpdate($updateEvent) {}
	protected function afterSave($event) {}
	protected function afterInsert($event) {}
	protected function afterUpdate($updateEvent) {}
	protected function afterFailedSave($event) {}
	protected function beforeDelete($event) {}
	protected function afterDelete($event) {}
	protected function afterFailedDelete($event) {}
	
	// private stuff and methods less interesting for descendants
	
	private $dbFields = array();
	private $tableName;
	
	public function getObjectId($object) {
		if (method_exists($object,'getId')) { 
			return $object->getId();
		} else if (property_exists($object,'id')) { 
			return $object->id;
		} else {
			return null; 
		}
	}
	
	public function setObjectId($object,$id) {
		if (method_exists($object,'setId')) { 
			$object->setId($id);
		} else if (property_exists($object,'id')) { 
			$object->id = $id;
		} else {
			$object->id = $id; 
		}
	}
	
	public function getIdField() {
		return 'id';
	}
		
	public function newObject() {
		$objectClass = $this->getObjectClass();
		return new $objectClass;
	}
	
	public function getObjectClass() { 
		$className = get_class($this);
		if (preg_match('/^([a-zA-Z0-9]+)ORM$/',$className,$m)) {
			return $m[1];
		} else 
			throw new Exception("Couldn't detect object class from ORM name");
	}
	
	public function getDbFields() {
		return $this->dbFields;
	}	
	
	public function getAllDbFields() { 
		return array_merge(array($this->getIdField()),$this->getDbFields());
	}

	protected function callBehaviorsEvent($eventName) {
		$args = func_get_args();
		$behaviors = $this->getBehaviors();
		if (!empty($behaviors)) 
			foreach ($behaviors as $behavior) {
				call_user_func_array(array($this, 'callBehaviorEvent'), array_merge(array($behavior),$args));
			}
	}
	
	protected function callBehaviorEvent($behavior,$eventName) {
		$args = func_get_args();
		array_shift($args);
		array_shift($args);
		try {  
			call_user_func_array(array($behavior, $eventName), $args);
		} catch (Exception $ex) { 
			ErrorHandler::logWarn($ex->getMessage());
		}
	}
	
		
	protected function getTableNameFromSaverClass() { 
		$className = get_class($this);
		if (preg_match('/^([a-zA-Z0-9]+)ORM$/',$className,$m)) {
			$str = $m[1]; 
			$str = strtolower(trim(preg_replace('/([A-Z]+[a-z0-9]*)/','$1_',$str),'_'));
			if ($str[strlen($str)-1]=='y') { 
				return substr($str,0,strlen($str)-1).'ies';
			} else 
				return $str.'s';
		} else 
			return false;
	}
		
	protected function getGetterNameByField($field) { 
		$parts = explode('_',$field);
		return 'get'.implode('',array_map(create_function('$p','return (strlen($p)>0?ucfirst($p):$p);'),$parts));
	}
	
	protected function getSetterNameByField($field) { 
		$parts = explode('_',$field);
		return 'set'.implode('',array_map(create_function('$p','return (strlen($p)>0?ucfirst($p):$p);'),$parts));
	}
	
	protected function getValidatorNameByField($field) { 
		$parts = explode('_',$field);
		return 'validate'.implode('',array_map(create_function('$p','return (strlen($p)>0?ucfirst($p):$p);'),$parts));
	}
	
	
	protected function getPropertyNameByField($field) { 
		$parts = explode('_',$field);
		$first = array_shift($parts);
		$name = $first;
		if (!empty($parts)) 
			$name .= implode('',array_map(create_function('$p','return (strlen($p)>0?ucfirst($p):$p);'),$parts));
		return $name;
	}
	
	public function getDbFieldValues($object,$fields = false) { 
		if ($fields === false) { 
			$fields = $this->getDbFields();
		}
		$values = array();
		foreach ($fields as $key => $val) {
			if (is_numeric($key)) { 
				$field = $val;
				$values[$field] = $this->getDbFieldValue($object,$field);
			} else { 
				$field = $key;
				$getterName = $val;
				if (method_exists($object,$getterName))
					$values[$field] = $object->$getterName();
				else if (property_exists($object,$getterName))
					$values[$field] = $object->$getterName;
				else 
					$values[$field] = null;
			}			
		}
		return $values;
	}
	
	public function getAllDbFieldValues($object) {
		$fields = $this->getDbFieldValues($object);
		$fields[$this->getIdField()] = $this->getObjectId($object);
		return $fields;
	}	
	
	public function getDbFieldValue($object,$field) { 
		$getterName = $this->getGetterNameByField($field);
		if (method_exists($object,$getterName)) 
			return $object->$getterName();
		else { 
			$propertyName = $this->getPropertyNameByField($field);
			if (property_exists($object,$propertyName))
				return $object->$propertyName;
			else  {
				return null;
			}
		}
	}
	
	public function setDbFieldValues($object,$values) { 
		foreach ($values as $field => $val) {
			$this->setDbFieldValue($object,$field,$val);
		}
	}
	
	public function setDbFieldValue($object,$field,$val) {
		$setterName = $this->getSetterNameByField($field);
		if (method_exists($object,$setterName)) 
			$object->$setterName($val);
		else { 
			$propertyName = $this->getPropertyNameByField($field);
			if (property_exists($object,$propertyName))
				$object->$propertyName = $val;
			else  {
				// create property anyway
				$object->$propertyName = $val;
			}
		}
	}
	
	protected function quoteValue($val, $dbCon = null) { 
		if ($val === null)	
			return 'NULL';
		else
			return "'".mysql_real_escape_string($val,init_DB_Conn())."'";
	}
	
	protected function quoteValues($values, $dbCon = null) {
		if (!$dbCon)
			$dbCon = init_DB_Conn();
		$quotedValues = array();
		if (!empty($values)) 
			foreach ($values as $key => $val) {	
				$quotedValues[$key] = $this->quoteValue($val,$dbCon);
			}
		return $quotedValues;
	}
	
	
	protected function callEvent($eventName) {
		$args = func_get_args(); 
		call_user_func_array(array($this, 'callBehaviorsEvent'), $args);
		array_shift($args);
		try {  
			call_user_func_array(array($this, $eventName), $args);
		} catch (Exception $ex) { 
			ErrorHandler::logWarn($ex->getMessage());
		}
	}

	protected function getWhereFromConditions($conditions,$dbCon = false) { 
		if (!$dbCon)
			$dbCon = init_DB_Conn();
		$whereStr = false;
		if ($conditions!=false) { 
			if (is_array($conditions)) { 
				foreach ($conditions as $key => $cond) {
					$whereStr = ($whereStr === false ? "" : "$whereStr AND ")
						. $this->parseCondition($key, $cond, $dbCon);
				}
			} else { 
				$whereStr = $conditions;
			}			
		} 
		return $whereStr;
	}
	
	protected function parseCondition($key, $condition, $dbCon = false) {
		if (!$dbCon) $dbCon = init_DB_Conn();

		$resCond = "";
		if (is_array($condition)) {
			foreach ($condition as $subKey => $subCond) {
				if ($subKey==false) {
					$subKey = $key;
				}
				$resSingleCond = $this->parseCondition($key, $subCond, $dbCon);
				if ($resSingleCond!=false) {
					if ($resCond!=false) {
						 $resCond .= " OR ";
					}
					$resCond .= $resSingleCond;
				}
			}
		} else if (is_numeric($key)) {
			$resCond = $condition;
		} else {
			$valuePart = "";
			if ($condition === null) {
				$valuePart = " IS NULL";
			} else {
				$valuePart = "=" . $this->quoteValue($condition, $dbCon);
			}
			$resCond = "`$key`$valuePart";
		}

		return "($resCond)";
	}
}