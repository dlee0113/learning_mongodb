<?php
// c-r-u-d-ops-add-info-objects.php

// NOTE: public properties become fields
//       methods are not stored, only properties
class Profile
{
	public $name;
	public $wins;
	public $losses;
	public $type = 'OBJECT';
	public function __construct($name, array $wins, array $losses)
	{
		$this->name   = $name;
		$this->wins   = $wins;
		$this->losses = $losses;
	}
}

// use newDB / test
$client		= new MongoClient(); // connect
$db 		= $client->selectDB('newDB');
$collection = $db->selectCollection('test');

// db.test.insert([{name:'Road Runner',wins:[2001,2002,2003,2004,2005,2006],losses:[],type:'OBJECT'}, 
//                 {name:'Wiley Coyote',wins:[],losses:[2001,2002,2003,2004,2005,2006],type:'OBJECT'}])

$document   = array(new Profile('Road Runner', array(2001,2002,2003,2004,2005,2006),array()),
					new Profile('Wiley Coyote', array(), array(2001,2002,2003,2004,2005,2006)));

// NOTE: different drivers have different methods for batch insert
$collection->batchInsert($document);

// db.test.find({'wins':{$exists:1}})
$query = array('wins' => array('$exists' => 1));
foreach($collection->find($query) as $item) {
	var_dump($item) . PHP_EOL;
}
