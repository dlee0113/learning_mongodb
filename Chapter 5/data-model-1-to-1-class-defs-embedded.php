<?php
// data-model-1-to-1-class-defs-embedded.php

class Customer
{
	public $firstName;
	public $lastName;
	public $profile;
	public function __construct($first, $last, Profile $profile)
	{
		$this->firstName = $first;
		$this->lastName = $last;
		$this->profile = $profile;
	}
}

class Profile
{
	public $number;
	public $street;
	public $city;
	public $postCode;
	public $country;
	public function __construct($number, $street, $city, $postCode, $country)
	{
		$this->number = $number;
		$this->street = $street;
		$this->city = $city;
		$this->postCode = $postCode;
		$this->country = $country;
	}
}
