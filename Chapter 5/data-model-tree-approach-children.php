<?php
// data-model-tree-approach-children.php

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
db.movies.save({_id:"james_cameron",firstName:"James",lastName:"Cameron",children:["the_terminator","true_lies","titanic","avatar"]})
db.movies.save({_id:"the_terminator",year:1984,parent:"james_cameron",children:["arnie","lhamilton"]})
db.movies.save({_id:"true_lies",year:1994,parent:"james_cameron",children:["arnie","jlcurtis"]})
db.movies.save({_id:"titanic",year:1997,parent:"james_cameron",children:["ldicaprio","kwinslet"]})
db.movies.save({_id:"avatar",year:2009,parent:"james_cameron",children:["sworthington","zsaldana"]})
db.movies.save({_id:"arnie",firstName:"Arnold",lastName:"Schwartzenegger",role:"The Terminator",parent:"the_terminator",children:[]})
db.movies.save({_id:"lhamilton",firstName:"Linda",lastName:"Hamilton",role:"Sarah Conner",parent:"the_terminator",children:[]})
db.movies.save({_id:"jlcurtis",firstName:"Jamie Lee",lastName:"Curtis",role:"Helen Tasker",parent:"true_lies",children:[]})
db.movies.save({_id:"tarnold",firstName:"Tom",lastName:"Arnold",role:"Albert Mike Gibson",parent:"true_lies",children:[]})
db.movies.save({_id:"ldicaprio",firstName:"Leonardo",lastName:"DiCaprio",role:"Jack Dawson",parent:"titanic",children:[]})
db.movies.save({_id:"kwinslet",firstName:"Kate",lastName:"Winslet",role:"Rose DeWitt Bukater",parent:"titanic",children:[]})
db.movies.save({_id:"sworthington",firstName:"Sam",lastName:"Worthington",role:"Jake Sully",parent:"avatar",children:[]})
db.movies.save({_id:"zsaldana",firstName:"Zoe",lastName:"Saldana",role:"Neytiri",parent:"avatar",children:[]})
*/
$data = array(
	array('_id' => 'james_cameron', 'firstName' => 'James', 'lastName' => 'Cameron', 'children' => array("the_terminator","true_lies","titanic","avatar")),
	array('_id' => 'the_terminator', 'title' => 'The Terminator', 'year' => 1984, 'parent' => 'james_cameron', 'children' => array("arnie","lhamilton")),
	array('_id' => 'true_lies', 	 'title' => 'True Lies',	  'year' => 1994, 'parent' => 'james_cameron', 'children' => array("arnie","jlcurtis")),
	array('_id' => 'titanic', 		 'title' => 'Titantic',       'year' => 1997, 'parent' => 'james_cameron', 'children' => array("ldicaprio","kwinslet")),
	array('_id' => 'avatar', 		 'title' => 'Avatar',         'year' => 2009, 'parent' => 'james_cameron', 'children' => array("sworthington","zsaldana")),
	array('firstName' => 'Arnold',    'lastName' => 'Schwartzenegger', 'role' => 'The Terminator',      'parent' => 'the_terminator', 'children' => array()),
	array('firstName' => 'Linda',     'lastName' => 'Hamilton',        'role' => 'Sarah Conner',        'parent' => 'the_terminator', 'children' => array()),
	array('firstName' => 'Arnold',    'lastName' => 'Schwartzenegger', 'role' => 'Harry Tasker',        'parent' => 'true_lies',      'children' => array()),
	array('firstName' => 'Jamie Lee', 'lastName' => 'Curtis',          'role' => 'Helen Tasker',        'parent' => 'true_lies',      'children' => array()),
	array('firstName' => 'Tom',       'lastName' => 'Arnold',          'role' => 'Albert Mike Gibson',  'parent' => 'true_lies',      'children' => array()),
	array('firstName' => 'Leonardo',  'lastName' => 'DiCaprio',        'role' => 'Jack Dawson',         'parent' => 'titanic',        'children' => array()),
	array('firstName' => 'Kate',      'lastName' => 'Winslet',         'role' => 'Rose DeWitt Bukater', 'parent' => 'titanic',        'children' => array()),
	array('firstName' => 'Sam',       'lastName' => 'Worthington',     'role' => 'Jake Sully',          'parent' => 'avatar',         'children' => array()),
	array('firstName' => 'Zoe', 	  'lastName' => 'Saldana',         'role' => 'Neytiri',             'parent' => 'avatar',         'children' => array()),
);
$movies->batchInsert($data);

// db.movies.findOne({_id:"james_cameron"}).children
$result = $movies->findOne(array('_id' => 'james_cameron'));
$output .= TreeApproach::displayShortResult('db.movies.findOne({_id:"james_cameron"}).children', $result);

// gets siblings
// db.movies.find({children:"true_lies"})
$result = $movies->find(array('children' => 'true_lies'));
$output .= TreeApproach::displayChildResult('db.movies.find({children:"true_lies"})', $result);

// db.movies.findOne({_id:"true_lies"}).children
$result = $movies->findOne(array('_id' => 'true_lies'));
$output .= TreeApproach::displayShortResult('db.movies.findOne({_id:"true_lies"}).children', $result);

echo $output;
