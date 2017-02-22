<?php
// data-model-1-to-many-class-defs-dbref.php
class Products
{
	public $_id;
	public $item;
	public $size;
	public $color;
	public $price;
	public function __construct($_id, $item, $size, $color, $price)
	{
		$this->_id   = $_id;
		$this->item	 = $item;
		$this->size  = $size;
		$this->color = $color;
		$this->price = $price;
	}
}
class Customer
{
	public $firstName;
	public $lastName;
	// NOTE: $purchases = array(array('qty','date',DBRef(product)),etc.)
	public $purchases;
	public function __construct($first, $last, array $purchases)
	{
		$this->firstName = $first;
		$this->lastName  = $last;
		$this->purchases = $purchases;
	}
	/**
	 * Displays results of purchase
	 * @param array $custFind = result of db.customers.findOne()
	 * @param MongoCollection $customers = $db->selectCollection('customers');
	 * @return string $output = pre-formatted
	 */
	public static function displayResults($custFind, MongoCollection $customers)
	{
		if (!$custFind) {
			return 'Customer not found!';
		}
		// display "nice" results
		$total   = 0;
		$date    = date('Y-m-d H:i:s');
		$output  = '';
		$output .= 'Customer: ' . $custFind['firstName'] . ' ' . $custFind['lastName'] . PHP_EOL;
		$output .= '-----------------------------------------------------------------------------------------' . PHP_EOL;
		$output .= '                Date |      Item |   Product ID |  Size |  Color |  Price | Qty |   Total' . PHP_EOL;
		$output .= '---------------------|-----------|--------------|-------|--------|--------|-----|--------' . PHP_EOL;
		foreach($custFind['purchases'] as $item) {
			// look up product from DBREf
			$product   = $customers->getDBRef($item['product']);
			$date      = $item['date'];
			$lineTotal = $item['qty'] * $product['price'];
			$total    += $lineTotal;
			$output   .= sprintf("%20s |%10s | %12s | %5s | %6s | %6.2f | %3d | %7s\n",
					$date,
					$product['item'],
					$product['_id'],
					$product['size'],
					$product['color'],
					number_format($product['price'],2),
					$item['qty'],
					$lineTotal);
		}
		$output .= '-----------------------------------------------------------------------------------------' . PHP_EOL;
		$output .= '                                                                            TOTAL: ' . number_format($total,2) . PHP_EOL;
		$output .= '-----------------------------------------------------------------------------------------' . PHP_EOL;
		return $output;
	}
}

date_default_timezone_set('Europe/London');
