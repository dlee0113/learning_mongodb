<?php
// example.php
date_default_timezone_set('Europe/London');
$client		= new MongoClient(); // connect
$db 		= $client->selectDB('mydb');
$collection = $db->selectCollection('test');
$element    = array('a' => date('Y-m-d H:i:s'));
$collection->insert($element);
$cursor     = $collection->find();
var_dump(iterator_to_array($cursor));
