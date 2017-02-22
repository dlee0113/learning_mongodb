<?php
// sharded_database_test.php
// populates a collection with 500,000 documents

/*
 *  How to use this program:
 *  1. Enable sharding on a database + collection
 * 	2. Add at least 2 shards to the collection
 *  3. VERY IMPORTANT: make sure the shard key is indexed!!!
 *  4. Run this program
 */
 
date_default_timezone_set('Europe/London');
$maxDocs	= 500000;
$alpha      = array_merge(range('A','Z'),range('a','z'));

// set up database connection
$client		= new MongoClient(); // connect
$dbTest		= $client->selectDB('mydb');
$collection = $dbTest->selectCollection('test');

// clear out pre-existing data
$collection->remove();

// random place to put search element
$srchNum = rand(1000, $maxDocs);
$srchKey = '19700101000000';
$srchTxt = 'AAAAAA';

// populate database with pseudo-random elements
for ($x = 0; $x < $maxDocs; $x++) {
	$text		= $alpha[rand(0,51)] 
				. $alpha[rand(0,51)] 
				. $alpha[rand(0,51)] 
				. $alpha[rand(0,51)]
				. $alpha[rand(0,51)]
				. $alpha[rand(0,51)];
	$key		= date('YmdHis') . rand(1,9999);
	$element    = array('key' => $key, 'text' => $text);
	$collection->insert($element);
	$srchNum--;
	// insert search element
	if ($srchNum === 0) {
		$collection->insert(array('key' => $srchKey, 'text' => $srchTxt));
	}
	echo $key . ':' . $text . ' | ';
}

// record time to find search element
echo 'db.test.findOne({text:"' . $srchKey . '"})' . PHP_EOL;
echo 'Start: ' . microtime() . PHP_EOL;
echo $collection->find(array('key' => $srchKey)) . PHP_EOL;
echo 'Stop:  ' . microtime() . PHP_EOL;
echo PHP_EOL;

/*
// List databases with sharding enabled:
// use config
echo 'db.databases.find( { "partitioned": true } )' . PHP_EOL;
$dbConfig	= $client->selectDB('config');
$collConfig = $db->selectCollection('databases');
var_dump(iterator_to_array($collConfig->find(array('partitioned' => 1));
echo PHP_EOL;

// List shards:
// use admin
echo 'db.runCommand( { listShards : 1 } )' . PHP_EOL;
$dbAdmin	= $client->selectDB('admin');
var_dump($dbAdmin->command(array('listShards' => 1)));
echo PHP_EOL;

// View cluster details:
echo 'db.printShardingStatus()' . PHP_EOL;
var_dump($dbAdmin->command(array('printShardingStatus' => TRUE)));
*/
