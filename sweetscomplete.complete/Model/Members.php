<?php
// MongoDB Project -- COMPLETE
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
	protected $db         = 'sweetscomplete';
	protected $client     = NULL;
	protected $collection = NULL;
	protected $emailList  = array();
	
	/*
	 * Returns array of arrays where each sub-array = 1 database row of Members
	 * @param int $offset [optional]
	 * @return array $row[user_id] = array('user_id' => id, 'name' => name, etc.)
	 */
	public function getAllMembers($offset = 0)
	{
		// *** to paginate, avoid using cursor.skip()
		// *** rewrite using db.members.find({email:{$in:['email@address1','email@address2',etc.]}},
		// ***                               {user_id:1,photo:1,name:1,city:1,email:1}).sort({email:1})
		$content = array();
		$collection = $this->getCollection();
		try {
			// safety check
			$offset = ($offset < 0) ? 0 : $offset;
			$offset = ($offset > $this->getHowManyMembers()) ? $this->getHowManyMembers() - $this->membersPerPage : $offset; 
			
			$find  = array_slice($this->emailList, $offset, $this->membersPerPage);
			$cursor  = $collection->find(array('email' => array('$in' => $find)),
										 array('user_id' => 1, 'photo' => 1, 'city' => 1, 'email' => 1, 'name' => 1));
			$cursor->sort(array('email' => 1));
			foreach($cursor as $member) {
				$content[$member['user_id']] = $member;
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
			$content = $collection->findOne(array('user_id' => $id));
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $content;
	}
	/*
	 * Returns database row for 1 member
	 * @param string $email
	 * @return array('user_id' => id, 'name' => name, etc.)
	 */
	public function loginByName($email, $password)
	{
		// First convert password using hash()
		$password = hash('ripemd256', $password);
		// *** rewrite using findOne({$and:[{email:$email},{password:$password}]})
		$content = array();
		$collection = $this->getCollection();
		try {
			$content = $collection->findOne(array('$and' => array(array('email' => $email),array('password' => $password))));
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $content;
	}
	/*
	 * Returns array of arrays where each sub-array = 1 database row of Members
	 * Searches name, address, city, state_province, country, email
	 * @param string $search
	 * @return array $row[id] = array('user_id' => id, 'name' => name, etc.)
	 */
	public function getMembersByKeyword($search)
	{
		// *** rewrite using:
		//find({$or:[{name:{$regex:/$search/i}},{city:{$regex:/$search/i}},{email:{$regex:/$search/i}}]},
		//     {user_id:1,photo:1,name:1,city:1,email:1}).sort({email:1});
		$content = array();
		// strip out any unwanted characters
		$search = preg_replace('/[^a-zA-Z0-9 ]/', '', $search);
		$collection = $this->getCollection();
		try {
			$cursor  = $collection->find(array('$or' => array(array('name'  => new MongoRegex("/$search/i")),
															  array('city'  => new MongoRegex("/$search/i")),
															  array('email' => new MongoRegex("/$search/i")))),
										 array('user_id' => 1, 'photo' => 1, 'city' => 1, 'email' => 1, 'name' => 1));
			$cursor->sort(array('email' => 1));
			foreach($cursor as $member) {
				$content[$member['user_id']] = $member;
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
		
		// write document to collection
		try {
			$collection = $this->getCollection();
			$collection->insert($data);
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

		// write to file
		try {
			$collection = $this->getCollection();
			$collection->insert($data);
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
		// rewrite using findOne() and update()
		$result = TRUE;
		$collection = $this->getCollection();
		try {
			$collection = $this->getCollection();
			$member = $collection->findOne(array('user_id' => $id));
			if ($member) {
				$member['status'] = 1;
				$collection->update(array('user_id' => $id), $data);
			} else {
				throw new Exception();
			}
		} catch (Exception $e) {
			$result = FALSE;
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
		$result = TRUE;
		$collection = $this->getCollection();
		try {
			$collection = $this->getCollection();
			$member = $collection->remove(array('user_id' => $id));
		} catch (Exception $e) {
			$result = FALSE;
			$this->handleError($e);
		}
		return $result;
	}

	public function getHowManyMembers()
	{
		// *** rewrite using find() + count()
		if (!$this->howManyMembers) {
			try {
				$this->howManyMembers  = count($this->getEmailList());
			} catch (Exception $e) {
				$this->handleError($e);
			}
		}
		return $this->howManyMembers;
	}
	
	/**
	 * Produces an array of member email
	 * @return array $emailList
	 */
	public function getEmailList()
	{
		if (!$this->emailList) {
			$collection = $this->getCollection();
			$cursor  = $collection->find(array(),array('email' => 1))->sort(array('email' => 1));
			foreach($cursor as $item) {
				$this->emailList[] = $item['email'];
			}		
		}
		return $this->emailList;
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
				$this->collection = $db->selectCollection('members');
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

	// Copied from Purchases.php
	/*
	 * Returns array of arrays where each sub-array = Purchase info + product info
	 * @param int $id = member ID
	 * @return array $row[] = array('title' => title, 'description' => description, etc.)
	 */
	public function getHistoryById($id)
	{
		$content = array();
		$collection = $this->getCollection();
		try {
			$member = $collection->findOne(array('user_id' => (float) $id));
		} catch (Exception $e) {
			$this->handleError($e);
		}
		return $member['purchases'];
	}


}
