<?php
// MongoDB Project -- COMPLETE
// products data class
// Header row of file:
// "product_id","sku","title","description","price","special","link"

// *** Rewrite using MongoDB
// *** Have a look at the constants in Init.php

class Products
{
	public $companyName 	= 'Sweets Complete';
	public $page 			= 'Home';
	public $debug			= TRUE;
	public $productsPerPage = 9;
	public $howManyProducts = 0;

	// *** need "client" property
	// *** rewrite to point to MongoDB database
	protected $db         = 'sweetscomplete';
	protected $client     = NULL;
	protected $collection = NULL;

	/*
	 * Returns document for $productsPerPage number of products
	 * @param int $offset
	 * @return array(array $row[] = array('title' => title, 'description' => description, etc.))
	 */
	public function getProducts($offset = 0)
	{
		// *** to paginate, avoid using cursor.skip()
		// *** rewrite using find({$and:[{product_id:{$gt:$offset}},
		// ***                           {product_id:{$lt:20}}]},
		// ***                    {product_id:1,title:1,link:1,_id:-1})
		// *** NOTE: for this to work you must establish an index on product_id: db.products.ensureIndex({product_id:1})
		$content = array();
		$collection = $this->getCollection();
		try {
			$max   = $this->getHowManyProducts();
			$start = $offset;
			$end   = $start + $this->productsPerPage;
			$cursor  = $collection->find(array('$and' => array(array('product_id' => array('$gt' => $start)), 
															   array('product_id' => array('$lte' => $end)))),
										 array('product_id' => 1, 'title' => 1, 'link' => 1));
			$cursor->sort(array('title' => 1));
			$content = iterator_to_array($cursor);
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $content;
	}
	/*
	 * Returns an associative array with product_id as key and title as value for all products
	 * @return array['product_id'] = title
	 */
	public function getProductTitles()
	{
		// *** rewrite using find({},{product_id:1,title:1})
		$content = array();
		$collection = $this->getCollection();
		try {
			$cursor  = $collection->find(array(),array('product_id' => 1, 'title' => 1));
			$cursor->sort(array('title' => 1));
			foreach($cursor as $item) {
				$content[$item['product_id']] = $item['title'];
			}			
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $content;
	}
	/*
	 * Returns document for 1 product
	 * @param int $id = product ID
	 * @return array('title' => title, 'description' => description, etc.)
	 */
	public function getDetailsById($id)
	{
		// *** rewrite using findOne({product_id:$id})
		$content = array();
		$collection = $this->getCollection();
		try {
			$content = $collection->findOne(array('product_id' => $id));
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $content;
	}
	/**
	 * Returns a count of how many products are in the products table
	 * @return int $count
	 */
	public function getHowManyProducts()
	{
		// *** rewrite using find() + count()
		$collection = $this->getCollection();
		if (!$this->howManyProducts) {
			try {
				$this->howManyProducts = $collection->find()->count();
			} catch (Exception $e) {
				$this->handleError($e);
			}
		}
		return $this->howManyProducts;
	}
	/*
	 * Returns array of arrays where each sub-array = 1 product document
	 * Returns only those products which are on special
	 * @param int $limit = how many specials to show
	 * @return array $row[] = array('title' => title, 'description' => description, etc.)
	 */
	public function getProductsOnSpecial($limit = 0)
	{
		// *** rewrite using find({special:1},{product_id:1,title:1,link:1}).limit($limit).sort({title:1})
		$content = array();
		$collection = $this->getCollection();
		try {
			$cursor  = $collection->find(array('special' => 1),
								         array('product_id' => 1, 'title' => 1, 'link' => 1));
			if ($limit) {
				$cursor->limit($limit);
			}
			$cursor->sort(array('title' => 1));
			$content = iterator_to_array($cursor);
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $content;
	}
	/*
	 * Returns array of arrays where each sub-array = 1 database row of products
	 * Searches title and description fields
	 * @param string $search
	 * @return array $row[] = array('title' => title, 'description' => description, etc.)
	 */
	public function getProductsByTitleOrDescription($search)
	{
		// *** rewrite using find({$or:[{title:{$regex:'chocolate',$options:'i'}},{description:{$regex:'chocolate',$options:'i'}}]},{title:1})
		$content = array();
		// strip out any unwanted characters
		$search = preg_replace('/[^a-zA-Z0-9 ]/', '', $search);
		$collection = $this->getCollection();
		try {
			$cursor  = $collection->find(array('$or' => array(array('title' => new MongoRegex("/$search/i")),
															  array('description' => new MongoRegex("/$search/i")))),
								         array('product_id' => 1, 'title' => 1, 'link' => 1));
			$cursor->sort(array('title' => 1));
			$content = iterator_to_array($cursor);
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $content;
	}
	/*
	 * Returns all products in shopping cart from $_SESSION
	 * @return array $row[] = array('title' => title, 'description' => description, etc.)
	 */
	public function getShoppingCart()
	{
		$content = (isset($_SESSION['cart'])) ? $_SESSION['cart'] : array();
		return $content;
	}
	/*
	 * Adds purchase to basket
	 * @param int $id = product ID
	 * @param int $quantity
	 * @param float $price (NOTE: sale_price in the `purchases` table = $quantity * $price
	 * @return boolean $success
	 */
	public function addProductToCart($id, $quantity, $price)
	{
		$item = $this->getDetailsById($id);
		$item['qty'] 		= $quantity;
		$item['price']		= $price;
		$item['notes']		= 'Notes';
		$_SESSION['cart'][] = $item;
		return TRUE;
	}
	/*
	 * Removes purchase from basket
	 * @param int $productID
	 * @return boolean $success
	 */
	public function delProductFromCart($productID)
	{
		$removed = FALSE;
		if (isset($_SESSION['cart'])) {
			foreach ($_SESSION['cart'] as $key => $row) {
				if ($row['product_id'] == $productID) {
					unset($_SESSION['cart'][$key]);
					$removed = TRUE;
				}
			}
		}
		return $removed;
	}
	/*
	 * Updates purchase from basket
	 * @param int $productID
	 * @param string $notes
	 * @param int $qty
	 * @return boolean $success
	 */
	public function updateProductInCart($productID, $qty, $notes)
	{
		$updated = FALSE;
		if (isset($_SESSION['cart'])) {
			foreach ($_SESSION['cart'] as $key => $row) {
				if ($row['product_id'] == $productID) {
					$_SESSION['cart'][$key]['qty'] 	 = $qty;
					$_SESSION['cart'][$key]['notes'] = $notes;
					$updated = TRUE;
				}
			}
		}
		return $updated;
	}

	/**
	 * Returns a MongoCollection object
	 * @throws Exception
	 * @return collection $collection
	 */
	public function getCollection()
	{
		// *** rewrite using "products" collection
		if (!$this->collection) {
			try {
				// *** select the "products" collection
				$client = $this->getClient();
				$db = $client->selectDB($this->db);
				$this->collection = $db->selectCollection('products');
			} catch (Exception $e) {
				$this->handleError($e);
			}
		}
		return $this->collection;
	}

	/**
	 * Returns a MongoClient object
	 * @throws Exception
	 * @return collection $collection
	 */
	public function getClient()
	{
		// *** rewrite to return a MongoClient instance
		if (!$this->client) {
			try {
				$this->client = new MongoClient('mongodb://sweet:password@localhost/sweetscomplete');
			} catch (Exception $e) {
				$this->handleError($e);
			}
		}
		return $this->client;
	}
	
	/**
	 * Handles errors
	 * redirects to HOME_URL + ?page=error
	 */
	 public function handleError(Exception $e)
	 {
		error_log($e->getMessage(), 0);
		$_SESSION['error'] = 'Database error';
		header('Location: ' . HOME_URL . '?page=error');
		exit;
	 }
}
