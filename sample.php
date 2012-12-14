<?

include(dirname(__FILE__).'/config.php');
include(dirname(__FILE__).'/lib/orm_core.php');

$villageOrm = new VillageORM();

// 1. validation sample
$village = new Village();
$village->setName("");
try {
	$villageOrm->save($village);	
} catch (ValidateException $ex) {
	echo "Validation: ". ($ex->getMessage()). 
		"\n". var_export($ex->getObjectErrors()->getFieldErrors(),true)."\n";
}


// 2. saving new record
$village = new Village();
$village->setName("Kitzbühel");
$villageOrm->save($village);

// get all database fields
print_r($villageOrm->getAllDbFieldValues($village));

// 3. updating record fields
$village->setName("St Anton");
$villageOrm->save($village);

// dump database fields
print_r($villageOrm->getAllDbFieldValues($village));


// 4. updating single field
$village->setName("St Anton");
$villageOrm->update($village,array('name'));


// 5. add another record
$village2 = new Village();
$village2->setName("Saalbach");
$villageOrm->save($village2);

$skiResortOrm = new SkiResortORM();

// 6. save another model instance
$skiResort = new SkiResort();
$skiResort->name = "Ellmau";
$skiResort->villageId = $village2->getId();
$skiResortOrm->save($skiResort);

// dump database fields
echo "Ski resort: ".var_export($skiResortOrm->getAllDbFieldValues($skiResort),true)."\n";
echo "Get village from ski resort: ".var_export($villageOrm->getAllDbFieldValues($skiResort->getVillage()),true)."\n";


// 6. select records
$villages = $villageOrm->findAll(array('name' => "St Anton"),"created_at desc", 1 /*limit*/);


// 7. count records
$count = $villageOrm->countAll(array("created_at>unix_timestamp(now()) - 300")); 
echo "Count: $count\n";

// 8. delete single object
$village2 = new Village();
$villageOrm->delete($village2);

// 9. delete by condition
$villageOrm->deleteAll(array('id' => array($village->getId())));

// 10. delete all
$villageOrm->deleteAll();


