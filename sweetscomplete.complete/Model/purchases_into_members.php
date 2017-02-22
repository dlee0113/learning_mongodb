<?php
/*
 * purchases_into_members.php
 * 
 * Fields in sweetscomplete_purchases.csv:
 * 0 = "purchase_id", 1 = "transaction", 2 = "product_id", 3 = "user_id", 4 = "date", 5 = "quantity", 6 ="sale_price"
 *
 * If you run into problems, restore "members" from the CSV file:
 *    -- Login to the MongoDB shell
 *    -- use sweetscomplete
 *    -- db.members.drop()
 *    -- exit the shell
 *    -- run this command:
 *       mongoimport -d sweetscomplete -c members --file sweetscomplete_members.csv --type csv --headerline
 */

$client		= new MongoClient();
$db 		= $client->selectDB('sweetscomplete');
$customers 	= $db->selectCollection('members');
$products	= $db->selectCollection('products');

// open connection to purchases file
$purchases  = new SplFileObject('sweetscomplete_purchases.csv', 'r');
$headerrs   = $purchases->fgetcsv();

// loop through purchases
$purchNum  = 0;
$userId    = '';
$purchData = array();
while(!$purchases->eof()) {
	// get next row of purchases
	$nextPurch = $purchases->fgetcsv();	
	// check to see if customer number has changed
	if (isset($nextPurch[3]) && $nextPurch[3] != $userId) {
		if ($userId) {
			// insert into member document
			$custData = $customers->findOne(array('user_id' => $userId));
			$customers->update(array('user_id' => $userId),
							   array('$set' => array('purchases' => $purchData)));
			echo $userId . ':' . $custData['name'] . ':' . $purchNum . PHP_EOL;
		}
		$userId = (float) $nextPurch[3];
		$purchData = array();
		$purchNum  = 0;
	}			
	// lookup product info
	if (isset($nextPurch[2])) {
		$prodData  = $products->findOne(array('product_id' => $nextPurch[2]));
		// *** these are the fields needed for each purchase:
		// *** purchase_id, transaction, product_id, date, quantity, sale_price, sku, title, special
		$purchData[$purchNum] = $nextPurch;
		$purchData[$purchNum]['sku'] 	 = (isset($prodData['sku']))     ? $prodData['sku']     : 'N/A';
		$purchData[$purchNum]['title']   = (isset($prodData['title']))   ? $prodData['title']   : 'N/A';
		$purchData[$purchNum]['special'] = (isset($prodData['special'])) ? $prodData['special'] : 'N/A';
		$purchNum++;
	}
}
