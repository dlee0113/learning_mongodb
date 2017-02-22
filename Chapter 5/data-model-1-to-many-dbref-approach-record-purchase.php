<?php
// data-model-1-to-many-dbref-approach-record-purchase.php

// load class defs
include 'data-model-1-to-many-class-defs-dbref.php';

// use newDB / test
$client		= new MongoClient(); // connect
$db 		= $client->selectDB('newDB');
$customers 	= $db->selectCollection('customers');
$products   = $db->selectCollection('products');

// get $_POST data
$custName   = (isset($_POST['lastName'])) ? strip_tags($_POST['lastName']) : '';
$purchItems = (isset($_POST['qty'])) ? $_POST['qty'] : array();

// get list of products
$prodFind  = $products->find();
$prodList  = iterator_to_array($prodFind);

// find customer (NOTE: assumes last name is unique;  otherwise create a unique key)
$custQuery = array('lastName' => $custName);
$custFind  = $customers->findOne($custQuery);
if (!($custFind && count($purchItems))) {
	$output = 'Customer Not Found or No Purchases Made';
} else {
	$custId = $custFind['_id']->__toString();
	$purchases = array();
	foreach ($purchItems as $key => $value) {
		if ($value) {
			$purchases[] = array('qty' 	=> (int) $value, 
								 'date' => date('Y-m-d H:i:s'),
								 'product' => MongoDBRef::create('products',$prodList[$key]['_id'],'newDB'));
		}
	}
	// update customer
	$update = array('$push' => array('purchases' => array('$each' => $purchases)));
	$customers->update($custQuery, $update);
	$custFind  = $customers->findOne($custQuery);
	// display "nice" results
	$output = Customer::displayResults($custFind, $customers);
}

// display output
echo '<h1>data-model-1-to-many-dbref-approach-record-purchase.php</h1>' . PHP_EOL;
echo '<pre>' . $output . '</pre>' . PHP_EOL;
