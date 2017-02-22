<?php
// data-model-1-to-many-embedded-approach-record-purchase.php
date_default_timezone_set('Europe/London');

// load class defs
include 'data-model-1-to-many-class-defs-embedded.php';

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
	$purchases = array();
	foreach ($purchItems as $key => $value) {
		if ($value) {
			$purchases[] = new Purchases($prodList[$key],(int) $value,date('Y-m-d H:i:s'));
		}
	}
	// update customer
	$update = array('$push' => array('purchases' => array('$each' => $purchases)));
	$customers->update($custQuery, $update);
	$custFind  = $customers->findOne($custQuery);
	// display "nice" results
	$output = Purchases::displayResults($custFind);
}

// display output
echo '<h1>data-model-1-to-many-embedded-approach-record-purchase.php</h1>' . PHP_EOL;
echo '<pre>' . $output . '</pre>' . PHP_EOL;
