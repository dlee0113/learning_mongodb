<?php
// data-model-1-to-1-class-defs.php

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

class Profile
{
	public $cust_id;
	public $number;
	public $street;
	public $city;
	public $postCode;
	public $country;
	public function __construct($cust_id, $number, $street, $city, $postCode, $country)
	{
		$this->cust_id 	= $cust_id;
		$this->number 	= $number;
		$this->street 	= $street;
		$this->city 	= $city;
		$this->postCode = $postCode;
		$this->country 	= $country;
	}
}
