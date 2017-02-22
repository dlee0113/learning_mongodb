<?php
// data-model-1-to-many-class-defs.php

class Customer
{
	public $_id;
	public $firstName;
	public $lastName;
	public function __construct($id, $first, $last)
	{
		$this->_id       = $id;
		$this->firstName = $first;
		$this->lastName  = $last;
	}
}
// NOTE: we override _id using a more easily remembered unique key
class Products
{
	public $_id;
	public $item;
	public $size;
	public $color;
	public $price;
	public function __construct($prod_id, $item, $size, $color, $price)
	{
		$this->_id   = $prod_id;
		$this->item	 = $item;
		$this->size  = $size;
		$this->color = $color;
		$this->price = $price;
	}
}
class Purchases
{
	public $cust_id;
	public $prod_id;
	public $qty;
	public $date;
	public function __construct($cust_id, $prod_id, $qty, $date)
	{
		$this->cust_id 	= $cust_id;
		$this->prod_id  = $prod_id;
		$this->qty		= $qty;
		$this->date		= $date;
	}
	/**
	 * Displays results of purchase
	 * @param array $custFind = result of db.customers.findOne()
	 * @param array $prodList = array_to_iterator(db.products.find())
	 * @param array $purchFind = db.purchases.find({cust_id:cust._id.toString().substring(10,34)})
	 * @return string $output = pre-formatted
	 */
	public static function displayResults($custFind, $prodList, $purchFind)
	{
		// display "nice" results
		$total   = 0;
		$output  = '';
		$output .= 'Customer: ' . $custFind['firstName'] . ' ' . $custFind['lastName'] . PHP_EOL;
		$output .= '------------------------------------------------------------------' . PHP_EOL;
		$output .= '      Item |   Product ID |  Size | Color |  Price | Qty |   Total' . PHP_EOL;
		$output .= '-----------|--------------|-------|-------|--------|-----|--------' . PHP_EOL;
		foreach($purchFind as $item) {
			$date      = $item['date'];
			$lineTotal = $item['qty'] * $prodList[$item['prod_id']]['price'];
			$total    += $lineTotal;
			$output   .= sprintf("%10s | %12s | %5s | %5s | %6.2f | %3d | %7s\n",
					$prodList[$item['prod_id']]['item'],
					$item['prod_id'],
					$prodList[$item['prod_id']]['size'],
					$prodList[$item['prod_id']]['color'],
					number_format($prodList[$item['prod_id']]['price'],2),
					$item['qty'],
					$lineTotal);
		}
		$output .= '------------------------------------------------------------------' . PHP_EOL;
		$output .= 'Purchase Date: ' . $date . '                   TOTAL: ' . number_format($total,2) . PHP_EOL;
		$output .= '------------------------------------------------------------------' . PHP_EOL;
		return $output;
	}
}
