<?php
// MongoDB Project -- LAB
// members data class
// Header row of file:
// "user_id","name","address","city","state_province","postal_code","country","phone","balance","email","password","photo","dob","status","security_question","confirm_code"

class Members
{
	public $debug = TRUE;
	protected $db_pdo;
	public $membersPerPage = 12;
	public $howManyMembers = 0;
	
	// *** need "client" property
	// *** rewrite to point to MongoDB database
	protected $db         = 'sweetscomplete_members.csv';
	protected $collection = NULL;

	public function __construct()
	{
		$this->db = __DIR__ . DIRECTORY_SEPARATOR . $this->db;
	}
	
	/*
	 * Returns array of arrays where each sub-array = 1 database row of Members
	 * @param int $offset [optional]
	 * @return array $row[] = array('user_id' => id, 'name' => name, etc.)
	 */
	public function getAllMembers($offset = 0)
	{
		// *** rewrite using find({}{user_id:1,photo:1,name:1,city:1,email:1}).sort({name:1})
		$content = array();
		$collection = $this->getCollection();
		try {
			// sets file pointer to top
			$collection->rewind();
			// get 1st row from CSV file
			$headers = $collection->fgetcsv();
			// pagination control
			$count = $this->membersPerPage;
			// loop and get only 5 fields
			while (!$collection->eof()) {
				$document = $collection->fgetcsv();
				if ($offset == 0) {
					if (isset($document[1])) {
						if ($count > 0) {
							// 0 = email, 11 = photo, 1 = name, 3 = city, 9 = email
							$content[$document[0]] = array($document[0], $document[11], $document[1], $document[3], $document[9]);
							$count--;
						}
					}
				} else {
					$offset--;
				}
			}
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $content;
	}
	/*
	 * Returns database row for 1 member
	 * @param int $id = member ID
	 * @return array('user_id' => id, 'name' => name, etc.)
	 */
	public function getDetailsById($id)
	{
		// *** rewrite using findOne({user_id:$id})
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
	/*
	 * Returns database row for 1 member
	 * @param string $email
	 * @return array $row[] = array('user_id' => id, 'name' => name, etc.)
	 */
	public function loginByName($email, $password)
	{
		// First convert password using hash()
		$password = hash('ripemd256', $password);
		// *** rewrite using findOne({$and:[{email:$email},{password:$password}]})
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
				if ($document[9] == $email && $document[10] == $password) {
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
	public function getHowManyMembers()
	{
		// *** rewrite using find() + count()
		if (!$this->howManyMembers) {
			try {
				$this->howManyMembers = 0;
				$collection = $this->getCollection();
				// sets file pointer to top
				$collection->rewind();
				$document = $collection->fgetcsv();
				while (!$collection->eof()) {
					$document = $collection->fgetcsv();
					$this->howManyMembers++;
				}
			} catch (Exception $e) {
				$this->handleError($e);
			}
		}
		return $this->howManyMembers;
	}
	/*
	 * Returns array of arrays where each sub-array = 1 database row of Members
	 * Searches name, address, city, state_province, country, email
	 * @param string $search
	 * @return array $row[id] = array('title' => title, 'description' => description, etc.)
	 */
	public function getMembersByKeyword($search)
	{
		// *** rewrite using:
		//find({$or:[{name:{$regex:/$search/i}},{city:{$regex:/$search/i}},{email:{$regex:/$search/i}}]},
		//     {user_id:1,photo:1,name:1,city:1,email:1,link:1}).sort({name:1});
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
				// 1 = name; 3 = city; 9 = email
				$found = FALSE;
				if (isset($document[1]) && stripos($document[1], $search) !== FALSE) {
					$found = TRUE;
				} elseif (isset($document[3]) && stripos($document[3], $search) !== FALSE) {
					$found = TRUE;
				} elseif (isset($document[9]) && stripos($document[9], $search) !== FALSE) {
					$found = TRUE;
				}
				if ($found) {
						// 0 = email, 11 = photo, 1 = name, 3 = city, 9 = email
						$content[$document[0]] = array($document[0], $document[11], $document[1], $document[3], $document[9]);
				}
			}
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $content;
	}
	/**
	 * Adds member to database
	 * @param array $data
	 * @return array(new member id, confirmation code)
	 */
	public function add($data)
	{
		// *** rewrite using insert()
		// initialize certain fields
		$data['user_id']	  = str_replace(array('-',' ',':'),'',$data['dob']) . date('His') . sprintf('%04d', rand(1,9999));
		$confirmCode 		  = md5(date('YmdHis') . rand(1,9999));
		$data['name'] 		  = $data['firstname'] . ' ' . $data['lastname'];
		$data['password'] 	  = hash('ripemd256', $data['password']);
		$data['balance'] 	  = 0;
		$data['status']		  = 0;
		$data['confirm_code'] = $confirmCode;
		// get rid of form fields no longer needed
		unset($data['firstname'],
			  $data['lastname'],
			  $data['dobyear'],
			  $data['dobmonth'],
			  $data['dobday'],
			  $data['submit']);
		// convert assoc array into numeric
		$newData = array();
		$newData[] = $data['user_id'];
		$newData[] = $data['name'];
		$newData[] = $data['address'];
		$newData[] = $data['city'];
		$newData[] = $data['state_province'];
		$newData[] = $data['postal_code'];
		$newData[] = $data['country'];
		$newData[] = $data['phone'];
		$newData[] = $data['balance'];
		$newData[] = $data['email'];
		$newData[] = $data['password'];
		$newData[] = $data['photo'];
		$newData[] = $data['dob'];
		$newData[] = $data['status'];
		$newData[] = $data['confirm_code'];
		
		// write to file
		try {
			$lineOut = '"' . implode('","', $newData) . '"' . PHP_EOL;
			$this->collection = NULL;
			file_put_contents($this->db, $lineOut, FILE_APPEND);
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return array($data['user_id'], $confirmCode);
	}
	/**
	 * Adds member to database (from admin page)
	 * @param array $data
	 * @return boolean $success
	 */
	public function adminAdd($data)
	{
		$result = 0;
		// manage data
		unset($data['submit'], $data['update']);
		// initialize certain fields
		$data['password'] = hash('ripemd256', $data['password']);
		$data['user_id']  = str_replace(array('-',' ',':'),'',$data['dob']) . date('His') . sprintf('%04d', rand(1,9999));
		// convert assoc array into numeric
		$newData = array();
		$newData[] = $data['user_id'];
		$newData[] = $data['name'];
		$newData[] = $data['address'];
		$newData[] = $data['city'];
		$newData[] = $data['state_province'];
		$newData[] = $data['postal_code'];
		$newData[] = $data['country'];
		$newData[] = $data['phone'];
		$newData[] = $data['balance'];
		$newData[] = $data['email'];
		$newData[] = $data['password'];
		$newData[] = $data['photo'];
		$newData[] = $data['dob'];
		$newData[] = $data['status'];
		$newData[] = $data['confirm_code"'];

		// write to file
		try {
			$lineOut = '"' . implode('","', $newData) . '"' . PHP_EOL;
			$this->collection = NULL;
			file_put_contents($this->db, $lineOut, FILE_APPEND);
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $result;
	}
	/**
	 * Sends out email confirmation
	 * @param int $newId
	 * @param array $data
	 * @param string $confirmCode
	 * @return string $mailStatus
	 */
	public function confirm($newId, $data, $confirmCode)
	{
		// predictable resource: PHPMailer directory renamed to "Mail"
		require_once __DIR__ . '/../Mail/class.phpmailer.php';
		$link 	 = sprintf('<a href="%s?page=confirm&id=%s&code=%s">CLICK HERE</a>', HOME_URL, $newId, $confirmCode);
		$address = "info@sweetscomplete.com";
		$newName = $data['firstname'] . ' ' . $data['lastname'];
		$mail 	 = new PHPMailer(); // defaults to using php "mail()"
		$body 	 = 'Welcome to SweetsComplete ' . $newName . '!'
				 . '<br />To confirm your membership just reply to this email and we\'ll do the rest.'
				 . '<br />'
				 . $link
				 . 'to confirm your new membership account.'
				 . '<br />Here is your confirmation code just in case: ' . $confirmCode
				 . '<br />Happy eating!';
		$mail->AddReplyTo($address,"SweetsComplete");
		$mail->SetFrom($address,"SweetsComplete");
		$mail->AddAddress($data['email'], $newName);
		$mail->AddBCC($address,"SweetsComplete");
		$mail->Subject = 'SweetsComplete Membership Confirmation';
		$mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
		$mail->MsgHTML($body);
		if(!$mail->Send()) {
			$mailStatus = 'Sorry: problem sending the email!';
			error_log($mail->ErrorInfo, 0);
			error_log($link, 0);
		} else {
			$mailStatus = 'Confirmation Email Message sent!';
		}
		return $mailStatus;
	}
	/*
	 * Confirms membership based on ID and confirmation code
	 * @param int $id = member ID
	 * @return boolean
	 */
	public function finishConfirm($id)
	{
		$this->collection = NULL;
		// rewrite using update()
		$origFN = 'sweetscomplete_members.csv';
		$bakFN  = 'sweetscomplete_members.csv.bak';
		// create backup file
		copy($origFN, $bakFN);
		// reverse
		$inFile  = new SplFileObject($bakFN,'r');
		$outFile = new SplFileObject($origFN,'w');
		$result = 0;
		try {
			while (!$inFile->eof()) {
				$members = $inFile->fgetcsv();
				if ($members[0] == $id) {
					$members[15] = 1;
				}
				$lineOut = '"' . implode('","', $members) . '"' . PHP_EOL;
				$outFile->fwrite($lineOut);
			}
			$inFile = NULL;
			$outFile = NULL;
			$result = 1;
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $result;
	}
	/*
	 * Removes member
	 * @param int $id = member ID
	 * @return boolean
	 */
	public function remove($id)
	{
		// *** need to rewrite using remove()
		$this->collection = NULL;
		// rewrite using save()
		$origFN = __DIR__ . '/sweetscomplete_members.csv';
		$bakFN  = __DIR__ . '/sweetscomplete_members.csv.bak';
		// create backup file
		copy($origFN, $bakFN);
		// reverse
		$inFile  = new SplFileObject($bakFN,'r');
		$outFile = new SplFileObject($origFN,'w');
		$result = 0;
		try {
			while (!$inFile->eof()) {
				$members = $inFile->fgetcsv();
				if ($members[0] != $id) {
					$lineOut = '"' . implode('","', $members) . '"' . PHP_EOL;
					$outFile->fwrite($lineOut);
				}
			}
			$inFile = NULL;
			$outFile = NULL;
			$result = 1;
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $result;
	}
	/**
	 * Returns a MongoCollection object
	 * @throws Exception
	 * @return collection $collection
	 */
	public function getCollection()
	{
		if ($this->collection == NULL) {
			// *** rewrite using "members" collection and $this->db
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
		if ($this->client == NULL) {
			try {
				// *** rewrite to return a MongoClient instance
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
