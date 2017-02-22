<?php
// MongoDB Project -- LAB
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
	protected $db         = 'sweetscomplete_products.csv';
	protected $collection = NULL;

	public function __construct()
	{
		$this->db = __DIR__ . DIRECTORY_SEPARATOR . $this->db;
	}
	
	public function __destruct()
	{
		if ($this->collection) {
			$this->collection = NULL;
		}
	}

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
		$limit = $this->productsPerPage;
		try {
			// sets file pointer to top
			$collection->rewind();
			// get 1st row from CSV file
			$headers = $collection->fgetcsv();
			$count = 0;
			while (!$collection->eof()) {
				$document = $collection->fgetcsv();
				// loop until $offset is reached
				if ($count++ >= $offset) {
					// only add to $limit
					if ($limit--) {
						// need array_combine so that key = header
						// 2 = title
						if (isset($document[2])) {
							$content[] = array_combine($headers, $document);
						}
					} else {
						break;
					}
				}
			}
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $content;
	}
	/*
	 * Returns database documents for all products
	 * @return array(array $row[product_id] = array('title' => title, 'description' => description, etc.))
	 */
	public function getAllProducts()
	{
		// *** rewrite using find() + projection
		$content = array();
		$collection = $this->getCollection();
		try {
			// sets file pointer to top
			$collection->rewind();
			// get 1st row from CSV file
			$headers = $collection->fgetcsv();
			while (!$collection->eof()) {
				// need array_combine so that key = header
				$products = $collection->fgetcsv();
				$content[$products[0]] = array_combine($headers,$products);
			}
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
			// sets file pointer to top
			$collection->rewind();
			// get 1st row from CSV file
			$headers = $collection->fgetcsv();
			while (!$collection->eof()) {
				$document = $collection->fgetcsv();
				// 0 = product_id; 2 = title
				if (isset($document[2])) {
					$content[$document[0]] = $document[2];
				}
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
			// sets file pointer to top
			$collection->rewind();
			// get 1st row from CSV file
			$headers = $collection->fgetcsv();
			while (!$collection->eof()) {
				$document = $collection->fgetcsv();
				// 0 = product_id
				if ($document[0] == $id) {
					// need array_combine so that key = header
					$content = array_combine($headers,$document);
					break;
				}
			}
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
		if (!$this->howManyProducts) {
			try {
				$this->howManyProducts = 0;
				$collection = $this->getCollection();
				// sets file pointer to top
				$collection->rewind();
				$document = $collection->fgetcsv();
				while (!$collection->eof()) {
					$document = $collection->fgetcsv();
					$this->howManyProducts++;
				}
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
			// sets file pointer to top
			$collection->rewind();
			// get 1st row from CSV file
			$headers = $collection->fgetcsv();
			while (!$collection->eof()) {
				$document = $collection->fgetcsv();
				// checks to see if field 5 (special) is 1
				if (isset($document[5]) && $document[5] == 1) {
					// need array_combine so that key = header
					$content[] = array_combine($headers,$document);
				}
			}
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
		// *** rewrite using find({$or:[{title:{$regex:'chocolate',$options:'i'}},
		// ***                    {description:{$regex:'chocolate',$options:'i'}}]},{title:1})
		$content = array();
		// strip out any unwanted characters
		$search = preg_replace('/[^a-zA-Z0-9 ]/', '', $search);
		$collection = $this->getCollection();
		try {
			// sets file pointer to top
			$this->collection->rewind();
			// get 1st row from CSV file
			$headers = $collection->fgetcsv();
			while (!$collection->eof()) {
				$document = $collection->fgetcsv();
				// 2 = title; 3 = description
				if (isset($document[2]) && isset($document[3])) {
					if (stripos($document[2], $search) !== FALSE
						|| stripos($document[3], $search) !== FALSE) {
						// need array_combine so that key = header
						$content[] = array_combine($headers,$document);
					}
				}
			}
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
		// *** use client to select a database first
		if ($this->collection == NULL) {
			try {
				// *** select the "products" collection
				$this->collection = new SplFileObject($this->db);
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
		if ($this->client == NULL) {
			try {
				// create an instance of MongoClient
				$this->client = NULL;
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
