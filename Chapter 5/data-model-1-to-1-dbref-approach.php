<?php
// data-model-1-to-1-dbref-approach.php

// load class defs
include 'data-model-1-to-1-class-defs-dbref.php';

// use newDB / test
$client		= new MongoClient(); // connect
$db 		= $client->selectDB('newDB');
$customers 	= $db->selectCollection('customers');
$profile    = $db->selectCollection('profile');

// clear test data
$customers->drop();
$profile->drop();

// var id1 = ObjectId();
// var id2 = ObjectId(); 
// db.profile.insert({_id:id1,number:'1234',street:'Main St.',city:'New York',postCode:'10001',country:'US'})
// db.profile.insert({_id:id2,number:'6789',street:'Market St.',city:'Los Angeles',postCode:'90001',country:'US'})
// db.customers.insert({firstName:'Stan',lastName:'Laurel',profile:{'$ref':'customers','$id':id1,'$db':'newDB'});
// db.customers.insert({firstName:'Oliver',lastName:'Hardy',profile:{'$ref':'customers','$id':id2,'$db':'newDB'}});
$profData   = array(new Profile(new MongoID(),'1234','Main St.','New York','10001','US'),
			        new Profile(new MongoID(),'6789','Market St.','Los Angeles','90001','US'));
$profile->batchInsert($profData);
$custData   = array(new Customer('Stan','Laurel',MongoDBRef::create('profile',$profData[0]->_id,'newDB')),
				    new Customer('Oliver','Hardy',MongoDBRef::create('profile',$profData[1]->_id,'newDB')));
$customers->batchInsert($custData);

// find customer
// db.customers.findOne({lastName:'Laurel'})
$customer = $customers->findOne(array('lastName' => 'Laurel'));
// find the customer profile using language driver DBRef method
$profile  = $customers->getDBRef($customer['profile']);

// display results
echo 'Customer: ' . PHP_EOL;
var_dump($customer);
echo 'Profile: ' . PHP_EOL;
var_dump($profile);

