<?php
// data-model-tree-approach-parent.php

// include display functions
include 'data-model-tree-approach-include.php';

// use newDB / test
$client		= new MongoClient(); // connect
$db 		= $client->selectDB('newDB');
$movies 	= $db->selectCollection('movies');
$output 	= '';

// clear test data
$movies->drop();

// add test data
/*
db.movies.save({_id:"james_cameron",firstName:"James",lastName:"Cameron"})
db.movies.save({_id:"the_terminator",year:1984,parent:"james_cameron"})
db.movies.save({firstName:"Arnold",lastName:"Schwartzenegger",role:"The Terminator",parent:"the_terminator"})
db.movies.save({firstName:"Linda",lastName:"Hamilton",role:"Sarah Conner",parent:"the_terminator"})
db.movies.save({_id:"true_lies",year:1994,parent:"james_cameron"})
db.movies.save({firstName:"Arnold",lastName:"Schwartzenegger",role:"Harry Tasker",parent:"true_lies"})
db.movies.save({firstName:"Jamie Lee",lastName:"Curtis",role:"Helen Tasker",parent:"true_lies"})
db.movies.save({firstName:"Tom",lastName:"Arnold",role:"Albert Mike Gibson",parent:"true_lies"})
db.movies.save({_id:"titanic",year:1997,parent:"james_cameron"})
db.movies.save({firstName:"Leonardo",lastName:"DiCaprio",role:"Jack Dawson",parent:"titanic"})
db.movies.save({firstName:"Kate",lastName:"Winslet",role:"Rose DeWitt Bukater",parent:"titanic"})
db.movies.save({_id:"avatar",year:2009,parent:"james_cameron"})
db.movies.save({firstName:"Sam",lastName:"Worthington",role:"Jake Sully",parent:"avatar"})
db.movies.save({firstName:"Zoe",lastName:"Saldana",role:"Neytiri",parent:"avatar"})
*/
$data = array(
	array('_id' => 'james_cameron', 'firstName' => 'James', 'lastName' => 'Cameron'),
	array('_id' => 'the_terminator', 'title' => 'The Terminator', 'year' => 1984, 'parent' => 'james_cameron'),
	array('_id' => 'true_lies', 	 'title' => 'True Lies',	  'year' => 1994, 'parent' => 'james_cameron'),
	array('_id' => 'titanic', 		 'title' => 'Titantic',       'year' => 1997, 'parent' => 'james_cameron'),
	array('_id' => 'avatar', 		 'title' => 'Avatar',         'year' => 2009, 'parent' => 'james_cameron'),
	array('firstName' => 'Arnold',    'lastName' => 'Schwartzenegger', 'role' => 'The Terminator',      'parent' => 'the_terminator'),
	array('firstName' => 'Linda',     'lastName' => 'Hamilton',        'role' => 'Sarah Conner',        'parent' => 'the_terminator'),
	array('firstName' => 'Arnold',    'lastName' => 'Schwartzenegger', 'role' => 'Harry Tasker',        'parent' => 'true_lies'),
	array('firstName' => 'Jamie Lee', 'lastName' => 'Curtis',          'role' => 'Helen Tasker',        'parent' => 'true_lies'),
	array('firstName' => 'Tom',       'lastName' => 'Arnold',          'role' => 'Albert Mike Gibson',  'parent' => 'true_lies'),
	array('firstName' => 'Leonardo',  'lastName' => 'DiCaprio',        'role' => 'Jack Dawson',         'parent' => 'titanic'),
	array('firstName' => 'Kate',      'lastName' => 'Winslet',         'role' => 'Rose DeWitt Bukater', 'parent' => 'titanic'),
	array('firstName' => 'Sam',       'lastName' => 'Worthington',     'role' => 'Jake Sully',          'parent' => 'avatar'),
	array('firstName' => 'Zoe', 	  'lastName' => 'Saldana',         'role' => 'Neytiri',             'parent' => 'avatar'),
);
$movies->batchInsert($data);

// db.movies.find({parent:"james_cameron"})
$result = $movies->find(array('parent' => 'james_cameron'));
$output .= TreeApproach::displayResults('db.movies.find({parent:"james_cameron"})', $result);

// db.movies.find({parent:"true_lies"})
$result = $movies->find(array('parent' => 'true_lies'));
$output .= TreeApproach::displayResults('db.movies.find({parent:"true_lies"})', $result);

// db.movies.findOne({_id:"titanic"}).parent
$result = $movies->findOne(array('_id' => 'titanic'));
$output .= TreeApproach::displayShortResult('db.movies.findOne({_id:"titanic"}).parent', $result);

echo $output;
