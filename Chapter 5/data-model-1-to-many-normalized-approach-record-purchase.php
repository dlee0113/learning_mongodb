<?php
// data-model-1-to-many-normalized-approach-record-purchase.php
date_default_timezone_set('Europe/London');

// load class defs
include 'data-model-1-to-many-class-defs.php';

// use newDB / test
$client		= new MongoClient(); // connect
$db 		= $client->selectDB('newDB');
$customers 	= $db->selectCollection('customers');
$products   = $db->selectCollection('products');
$purchases  = $db->selectCollection('purchases');

// get $_POST data
$custName   = (isset($_POST['lastName'])) ? strip_tags($_POST['lastName']) : '';
$purchItems = (isset($_POST['qty'])) ? $_POST['qty'] : array();

// get list of products
$prodFind  = $products->find();
$prodList  = iterator_to_array($prodFind);

// find customer (NOTE: assumes last name is unique;  otherwise create a unique key)
$custId   = '';
$custFind = $customers->findOne(array('lastName' => $custName));
if (!($custFind && count($purchItems))) {
	$output = 'Customer Not Found or No Purchases';
} else {
	$custId = $custFind['_id']->__toString();
	foreach ($purchItems as $key => $value) {
		if ($value) {
			//public function __construct($cust_id, $prod_id, $qty, $date)
			$purchases->insert(new Purchases($custId,$key,(int) $value,date('Y-m-d H:i:s')));
		}
	}
	// lookup purchases
	$purchFind = $purchases->find(array('cust_id' => $custId))->sort(array('date' => 1));	
	// display "nice" results
	$output = Purchases::displayResults($custFind, $prodList, $purchFind);
}

// display results
echo '<h1>data-model-1-to-many-normalized-approach-record-purchase.php</h1>' . PHP_EOL;
echo '<pre>' . $output . '</pre>' . PHP_EOL;
