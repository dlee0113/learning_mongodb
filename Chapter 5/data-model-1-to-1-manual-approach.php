<?php
// data-model-1-to-1-manual-approach.php

// load class defs
include 'data-model-1-to-1-class-defs.php';

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
// db.customers.insert({_id:id1, firstName:'Stan',lastName:'Laurel'});
// db.customers.insert({_id:id2, firstName:'Oliver',lastName:'Hardy'});
// db.profile.insert({cust_id:id1.toString(),number:'1234',street:'Main St.',city:'New York',postCode:'10001',country:'US'})
// db.profile.insert({cust_id:id2.toString(),number:'6789',street:'Market St.',city:'Los Angeles',postCode:'90001',country:'US'})
$custData   = array(new Customer(new MongoID(),'Stan','Laurel'),
				    new Customer(new MongoID(),'Oliver','Hardy'));
$customers->batchInsert($custData);
$data = array(new Profile($custData[0]->_id->__toString(),'1234','Main St.','New York','10001','US'),
			  new Profile($custData[1]->_id->__toString(),'6789','Market St.','Los Angeles','90001','US'));
$profile->batchInsert($data);

// find customer
// db.customers.findOne({lastName:'Laurel'})
$customer = $customers->findOne(array('lastName' => 'Laurel'));
$profile  = $profile->findOne(array('cust_id' => $customer['_id']->__toString()));

// display results
echo 'Customer: ' . PHP_EOL;
var_dump($customer);
echo 'Profile: ' . PHP_EOL;
var_dump($profile);
