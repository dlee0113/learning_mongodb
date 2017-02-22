<?php
/*
 * db-mgmt-performance-index-test.php
 */

$client			= new MongoClient();
$db 			= $client->selectDB('sweetscomplete');
$customers 		= $db->selectCollection('members');
$purchNoIndex	= $db->selectCollection('purchases_no_index');
$purchWithIndex	= $db->selectCollection('purchases');

// loop through customers
// sum up purchases
// use projections to limit the amount of data sent
// db.members.find({},{user_id:1,name:1}).sort({name:1})

// no index
$timer = explode(' ', microtime());
$time[] = 'Start -Index: ';
$time[] = $timer[1] + $timer[0];
$custCursor = $customers->find(array(),array('user_id' => 1, 'name' => 1))->sort(array('name' => 1));
foreach($custCursor as $person) {
	// lookup purchases for this customer
	$purchCursor = $purchNoIndex->find(array('user_id' => $person['user_id']));
	$count = 0;
	$total = 0;
	foreach($purchCursor as $purchase) {
		$count++;
		$total += $purchase['quantity'] * $purchase['sale_price'];
	}
	printf("%20s | %8d | %12.2f\n", $person['name'], $count, $total);
}
$timer = explode(' ', microtime());
$time[] = 'Stop -Index: ';
$time[] = $timer[1] + $timer[0];

// with index
$timer = explode(' ', microtime());
$time[] = 'Start +Index: ';
$time[] = $timer[1] + $timer[0];
$custCursor = $customers->find(array(),array('user_id' => 1, 'name' => 1))->sort(array('name' => 1));
foreach($custCursor as $person) {
	// lookup purchases for this customer
	$purchCursor = $purchWithIndex->find(array('user_id' => $person['user_id']));
	$count = 0;
	$total = 0;
	foreach($purchCursor as $purchase) {
		$count++;
		$total += $purchase['quantity'] * $purchase['sale_price'];
	}
	printf("%20s | %8d | %12.2f\n", $person['name'], $count, $total);
}
$timer = explode(' ', microtime());
$time[] = 'Stop -Index: ';
$time[] = $timer[1] + $timer[0];

echo '-------------------------------------------------' . PHP_EOL;
echo 'NO INDEX' . PHP_EOL;
echo '-------------------------------------------------' . PHP_EOL;
echo $time[0] . $time[1] . PHP_EOL;
echo $time[2] . $time[3] . PHP_EOL;
echo 'DIFFERENCE: ' . (float) ($time[3] - $time[1]) . PHP_EOL;
echo '-------------------------------------------------' . PHP_EOL;
echo PHP_EOL;
echo '-------------------------------------------------' . PHP_EOL;
echo 'WITH INDEX' . PHP_EOL;
echo '-------------------------------------------------' . PHP_EOL;
echo $time[4] . $time[5] . PHP_EOL;
echo $time[6] . $time[7] . PHP_EOL;
echo 'DIFFERENCE: ' . (float) ($time[7] - $time[5]) . PHP_EOL;
echo '-------------------------------------------------' . PHP_EOL;
echo PHP_EOL;
