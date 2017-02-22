<?php
// data-model-1-to-many-dbref-approach-make-purchase.php

// load class defs
include 'data-model-1-to-many-class-defs-dbref.php';

// use newDB / test
$client		= new MongoClient(); // connect
$db 		= $client->selectDB('newDB');
$customers 	= $db->selectCollection('customers');
$products   = $db->selectCollection('products');

// get list of customers and products
$custList  = iterator_to_array($customers->find());
$prodList  = iterator_to_array($products->find());

// display form
include 'data-model-1-to-many-form-dbref.phtml';
