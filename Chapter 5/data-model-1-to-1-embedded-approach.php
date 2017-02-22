<?php
// data-model-1-to-1-embedded-approach.php

// load class defs
include 'data-model-1-to-1-class-defs-embedded.php';

// use newDB / test
$client		= new MongoClient(); // connect
$db 		= $client->selectDB('newDB');
$customers 	= $db->selectCollection('customers');

// clear test data
$customers->drop();

// db.customers.insert({firstName:'Stan',lastName:'Laurel',
//						profile:{number:'1234',street:'Main St.',city:'New York',postCode:'10001',country:'US'}});
// db.customers.insert({firstName:'Oliver',lastName:'Hardy',
//					    profile:{number:'6789',street:'Market St.',city:'Los Angeles',postCode:'90001',country:'US'}});

$custData   = array(new Customer('Stan','Laurel',  new Profile('1234','Main St.','New York','10001','US')),
				    new Customer('Oliver','Hardy', new Profile('6789','Market St.','Los Angeles','90001','US')));
$customers->batchInsert($custData);

// find customer
// db.customers.findOne({'profile.postCode':'10001'})
$customer = $customers->findOne(array('profile.postCode' => '10001'));

echo 'Customer: ' . PHP_EOL;
var_dump($customer);
