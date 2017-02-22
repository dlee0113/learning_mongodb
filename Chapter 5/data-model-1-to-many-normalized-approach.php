<?php
// data-model-1-to-many-normalized-approach.php

// load class defs
include 'data-model-1-to-many-class-defs.php';

// use newDB / test
$client		= new MongoClient(); // connect
$db 		= $client->selectDB('newDB');
$customers 	= $db->selectCollection('customers');
$products   = $db->selectCollection('products');
$purchases  = $db->selectCollection('purchases');

// clear test data
$customers->drop();
$products->drop();
$purchases->drop();

// add test data
$prodData = array(new Products('T-S-ORA',	  'T Shirt', 'S',	  'Orange',  3.99),
			      new Products('T-XXL-GRE',	  'T Shirt', 'XXL',	  'Green',   6.99),
				  new Products('J-26-32-BLU', 'Jeans',   '26/32', 'Blue',	49.99),
				  new Products('J-46-32-BLU', 'Jeans',   '46/28', 'Blue',	59.99),
                  new Products('TH-R-BLA',	  'Top Hat', 'R',	  'Black',  29.99));
$products->batchInsert($prodData);
$custData = array(new Customer(new MongoID(),'Stan','Laurel'),
			      new Customer(new MongoID(),'Oliver','Hardy'));
$customers->batchInsert($custData);
$purchData = array(new Purchases($custData[0]->_id->__toString(),$prodData[0]->_id,2,'2014-01-01 11:01:01'),
				   new Purchases($custData[0]->_id->__toString(),$prodData[2]->_id,1,'2014-02-02 12:02:02'),
				   new Purchases($custData[1]->_id->__toString(),$prodData[1]->_id,3,'2014-03-03 13:03:03'),
				   new Purchases($custData[1]->_id->__toString(),$prodData[3]->_id,2,'2014-04-04 14:04:04'),
				   new Purchases($custData[1]->_id->__toString(),$prodData[4]->_id,1,'2014-05-05 15:05:05'));
$purchases->batchInsert($purchData);

// find purchases for customer 'Hardy'
// var cust = db.customers.findOne({lastName:'Hardy'})
$custFind  = $customers->findOne(array('lastName' => 'Hardy'));
// db.purchases.find({cust_id:cust._id.toString().substring(10,34)})
$purchFind = $purchases->find(array('cust_id' => $custFind['_id']->__toString()));
// db.products.find()
$prodFind  = $products->find();
$prodList  = iterator_to_array($prodFind);
 
// dump product list
var_dump($prodList);
echo PHP_EOL;

// display results
echo Purchases::displayResults($custFind, $prodList, $purchFind);
