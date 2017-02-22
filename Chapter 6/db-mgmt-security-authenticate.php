<?php
// db-mgmt-security-authenticate.php

// load class defs
include 'db-mgmt-security-authenticate-class-defs.php';

// non-authenticated connection
$client		= new MongoClient();

// authenticated connection -- uncomment and try
//$client		= new MongoClient('mongodb://test:password@localhost/newDB');

// use newDB / test
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

// find customers
print_r(iterator_to_array($customers->find()));
