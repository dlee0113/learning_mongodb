<?php
// MongoDB Project
// purchases data class
// Header row of file:
// "purchase_id","transaction","product_id","user_id","date","quantity","sale_price"

// *** NOTE: this class will no longer be needed
// ***       you will fold this functionality into Members
// ***       by embedding purchases as an array in a Members document

class Purchases
{

	protected $db         = 'sweetscomplete_purchases.csv';
	protected $prodDb     = 'sweetscomplete_products.csv';
	protected $collection = NULL;

	public function __construct()
	{
		$this->db     = __DIR__ . DIRECTORY_SEPARATOR . $this->db;
		$this->prodDb = __DIR__ . DIRECTORY_SEPARATOR . $this->prodDb;
	}

	// *** this method needs to be copied into Members.php
	// *** embed purchases as an array into the "members" collection
	// *** the embedded purchases array will become the new return value
	// *** Purchases.php will then become obsolete
	/*
	 * Returns array of arrays where each sub-array = Purchase info + product info
	 * @param int $id = member ID
	 * @return array $row[] = array('title' => title, 'description' => description, etc.)
	 */
	public function getHistoryById($id)
	{
		// get products
		$products = $this->getAllProducts();
		// process purchases for member ID
		$content = array();
		$collection = $this->getCollection();
		try {
			// sets file pointer to top
			$collection->rewind();
			// get 1st row from CSV file
			$headers = $collection->fgetcsv();
			while (!$collection->eof()) {
				$document = $collection->fgetcsv();
				// 3 = user_id
				if (isset($document[3]) && $document[3] == $id) {
					// need array_combine so that key = header
					// $document[2] = product_id
					$content[] = array_merge(array_combine($headers,$document), $products[$document[2]]);
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
		$collection = new SplFileObject($this->prodDb);
		try {
			// sets file pointer to top
			$collection->rewind();
			// get 1st row from CSV file
			$headers = $collection->fgetcsv();
			while (!$collection->eof()) {
				// need array_combine so that key = header
				$products = $collection->fgetcsv();
				if (isset($products[1])) {
					$content[$products[0]] = array_combine($headers,$products);
					unset($content[$products[0]]['description']);
				}
			}
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $content;
	}
	
	/**
	 * Returns a MongoCollection object
	 * @throws Exception
	 * @return collection $collection
	 */
	public function getCollection()
	{
		// *** rewrite using "products" collection and $this->db
		if (!$this->collection) {
			try {
				// *** 
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
		if (!$this->client) {
			try {
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
