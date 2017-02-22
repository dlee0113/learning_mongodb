<?php
// c-r-u-d-ops-update-array-push-each-sort-slice.php

function findAll($collection)
{
	// db.test.find()
	$output = '';
	foreach($collection->find() as $item) {
		$output .= sprintf('Name: %10s | Amount |  Date', $item['name']) . PHP_EOL;
		foreach($item['purchases'] as $purchase) {
			$output .= vsprintf('                 | %6.2f | %10s', $purchase) . PHP_EOL;
		}
	}
	return $output;
}

// use mydb / customer
$client		= new MongoClient(); // connect
$db 		= $client->selectDB('newDB');
$collection = $db->selectCollection('test');

// db.test.remove()
$collection->remove(); // removes all documents

// db.test.insert([{name:'Mutt',purchases:[{amount:111.11,date:'2014-01-01'},{etc.}]},
//                 {name:'Jeff',purchases:[{amount:444.44,date:'2014-04-04'},{etc.}]},
//                 {name:'MLove',purchases:[{amount:777.77,date:'2014-07-07'},{etc.}]})
$document   = array(array('name' => 'Mutt', 'purchases' => array(
												array('amount' => 111.11, 'date' => '2014-01-01'),
												array('amount' => 222.22, 'date' => '2014-02-02'),
												array('amount' => 333.33, 'date' => '2014-03-03'))),
					array('name' => 'Jeff', 'purchases' => array(
												array('amount' => 444.44, 'date' => '2014-04-04'),
												array('amount' => 555.55, 'date' => '2014-05-05'),
												array('amount' => 666.66, 'date' => '2014-06-06'))),
					array('name' => 'MLove', 'purchases' => array(
												array('amount' => 777.77, 'date' => '2014-07-07'),
												array('amount' => 888.88, 'date' => '2014-08-08'),
												array('amount' => 999.99, 'date' => '2014-09-09'))),
					);
$collection->batchInsert($document);

// db.test.find()
echo 'BEFORE ------------------------------------' . PHP_EOL;
echo findAll($collection);

// db.test.update( { name: "Jeff" },
//                 { $push: { purchases: { $each: [ { amount: 1111.11, date: '2014-11-11' },
//                                                  { amount: 1212.12, date: '2014-12-12' } ],
//                                         $sort: { amount: -1 },
//                                         $slice: -4
//                                       }
//                           }
//                  }
//               )

$query  = array('name' => 'Jeff');
$update = 
	array('$push' => 
		array('purchases' => 
			array(
				'$each' => array(
					array('amount' => 1111.11, 'date' => '2014-11-11'),
					array('amount' => 1212.12, 'date' => '2014-12-12')),
				// takes only the 4 largest amounts
				'$sort' => array('amount' => -1),
				'$slice' => -4
				)
			)
);
$collection->update($query, $update);

// db.test.find()
echo 'AFTER -------------------------------------' . PHP_EOL;
echo findAll($collection);
