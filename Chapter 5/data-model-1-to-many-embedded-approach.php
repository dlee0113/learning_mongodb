<?php
// data-model-1-to-many-embedded-approach.php

// load class defs
include 'data-model-1-to-many-class-defs-embedded.php';

// use newDB / test
$client		= new MongoClient(); // connect
$db 		= $client->selectDB('newDB');
$customers 	= $db->selectCollection('customers');
$products   = $db->selectCollection('products');

// clear test data
$customers->drop();
$products->drop();

// add products test data
$prodData = array(new Products('T-S-ORA',	  'T Shirt', 'S',	  'Orange',  3.99),
			      new Products('T-XXL-GRE',	  'T Shirt', 'XXL',	  'Green',   6.99),
				  new Products('J-26-32-BLU', 'Jeans',   '26/32', 'Blue',	49.99),
				  new Products('J-46-32-BLU', 'Jeans',   '46/28', 'Blue',	59.99),
                  new Products('TH-R-BLA',	  'Top Hat', 'R',	  'Black',  29.99));
$products->batchInsert($prodData);

// add customer test data
// NOTE: purchases are embedded in customer document, products are embedded in purchases
$custData = array(new Customer('Stan','Laurel',array( new Purchases($prodData[0],2,'2014-01-01 11:01:01'),
													  new Purchases($prodData[2],1,'2014-02-02 12:02:02'),)),
			      new Customer('Oliver','Hardy',array(new Purchases($prodData[1],3,'2014-03-03 13:03:03'),
											      	  new Purchases($prodData[3],2,'2014-04-04 14:04:04'),
											      	  new Purchases($prodData[4],1,'2014-05-05 15:05:05')))); 
$customers->batchInsert($custData);

// find purchases for customer 'Hardy'
// var cust = db.customers.findOne({lastName:'Hardy'})
$custFind  = $customers->findOne(array('lastName' => 'Hardy'));
 
// display results
echo Purchases::displayResults($custFind);
