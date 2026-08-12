<?php

	define('host','localhost');
	define('db_name','dipla');
	define('user','romain.lobello');
	define('pass','vw5vZGJ6n3UiyoVq');

	try{
		$db = new PDO("mysql:host=" . host . ";port=3306;dbname=" . db_name, user, pass);
		$db->exec("SET CHARACTER SET utf8");
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	catch(PDOException $e){
		echo $e;
	}
?>