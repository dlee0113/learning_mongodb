<?php
class User 
{
	protected $info;
	public function __construct($array)
	{
		$this->info = new ArrayObject($array);
	}
	public function getInfo()
	{
		return $this->info;
	}
}
$user = new User(
	array(	
		'name' 				=> 'Fred Flintstone',
		'address' 			=> '301 Cobblestone Way',
		'city' 				=> 'Bedrock',
		'state_province' 	=> 'Arkanstone',
		'postal_code' 		=> '70777',
		'country' 			=> 'Prehistoric',
		'phone' 			=> '00-00-0200',
		'balance' 			=> '0.01',
		'email' 			=> 'fred@slate.rock_and_gravel.com',
		'password' 			=> 'twinkletoes',
		'photo' 			=> 'http://upload.wikimedia.org/wikipedia/en/thumb/a/ad/Fred_Flintstone.png/165px-Fred_Flintstone.png',
		'dob' 				=> '0000-02-02 00:00:00',
		'status' 			=> 1,
));
