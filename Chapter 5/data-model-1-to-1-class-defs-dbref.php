<?php
// data-model-1-to-1-class-defs-dbref.php

class Customer
{
	public $firstName;
	public $lastName;
	public $profile;
	public function __construct($first, $last, array $profile)
	{
		$this->firstName = $first;
		$this->lastName  = $last;
		$this->profile   = $profile;
	}
}

class Profile
{
	public $_id;
	public $number;
	public $street;
	public $city;
	public $postCode;
	public $country;
	public function __construct(MongoId $id, $number, $street, $city, $postCode, $country)
	{
		$this->_id 		= $id;
		$this->number 	= $number;
		$this->street 	= $street;
		$this->city 	= $city;
		$this->postCode = $postCode;
		$this->country 	= $country;
	}
}
