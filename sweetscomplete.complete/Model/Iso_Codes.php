<?php
// MongoDB Project
// iso_codes data class
// Header row of file:
// "name","iso2","iso3","iso_numeric","iso_3166"

class Iso_Codes
{

	protected $db         = 'sweetscomplete_iso_codes.csv';
	protected $collection = NULL;

	public function __construct()
	{
		$this->db = __DIR__ . DIRECTORY_SEPARATOR . $this->db;
	}
	
	/*
	 * Returns database documents for all products
	 * @return array $row[iso2] = name
	 */
	public function getIso2AndNames()
	{
		// *** rewrite using find()
		$content = array();
		$collection = $this->getCollection();
		try {
			// sets file pointer to top
			$collection->rewind();
			// get 1st row from CSV file
			$headers = $collection->fgetcsv();
			while (!$collection->eof()) {
				$codes = $collection->fgetcsv();
				if (isset($codes[1])) {
					$content[$codes[1]] = $codes[0];
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
