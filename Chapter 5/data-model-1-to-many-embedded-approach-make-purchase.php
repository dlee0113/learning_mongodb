<?php
// data-model-1-to-many-embedded-approach-make-purchase.php

ini_set('display_errors', 1);

// load class defs
include 'data-model-1-to-many-class-defs-embedded.php';

// use newDB / test
$client		= new MongoClient(); // connect
$db 		= $client->selectDB('newDB');
$customers 	= $db->selectCollection('customers');
$products   = $db->selectCollection('products');

// get list of customers and products
$custList  = iterator_to_array($customers->find());
$prodList  = iterator_to_array($products->find());

// display form
include 'data-model-1-to-many-form-embedded.phtml';
