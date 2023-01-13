<?php
session_start();
require_once("PDOConnection.php");

class User{
	function login($username, $password){
		
		$username = strtolower($username);
		//Retrieves user's account details.
		$stmt = PDOConnection::getConnection()->prepare('SELECT id, username, firstName, lastName, memberType 
							  FROM `user`
							  WHERE username = :username AND password = :password');
		$stmt->execute([':username'=>$username, ':password'=>self::hashPassword($password)]);
		
		//If account was found, set $_SESSION key-value pairs with retrieve row column values.
		if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$_SESSION['username'] = $row['username'];
			$_SESSION['memberID'] = $row['id'];
			$_SESSION['firstName'] = $row['firstName'];
			$_SESSION['lastName'] = $row['lastName'];
			$_SESSION['memberType'] = $row['memberType'];
			return $row;
		}
		//If no account was found, return empty string/null.
		else{
			return "";
		}
	}
	
	function hashPassword($password){
		return hash("SHA512","48vued10z".$password."fjhw729do1",false);
	}

	function signUp($username, $password, $firstName, $lastName){
		
		$username = strtolower($username);
		//Checks if the provided username is currently in use.
		$stmt = PDOConnection::getConnection()->prepare('SELECT * 
							  FROM `user` 
							  WHERE username = :username');
		$stmt->execute([':username' => $username]);
		
		//If username is already in use, the if block executes.
		if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			return "";
		}
		else{
			$stmt = PDOConnection::getConnection()->prepare('INSERT INTO `user`(username, password, firstName, lastName) 
								  VALUES (:username, :password, :firstName, :lastName)');
			$stmt->execute([':username' => $username, ':password' =>self::hashPassword($password), ':firstName' => $firstName, ':lastName' => $lastName]);
			
			return 1;
		}
	}
	
	function logout(){
		session_unset;
		return 1;
	}
	
	function getPizzaPoints(){
		
		
		if($_SESSION['memberID'] != null){
			//Retrieves user's account details.
			$stmt = PDOConnection::getConnection()->prepare('SELECT pizzaPoints 
								  FROM `user`
								  WHERE id = :id');
			$stmt->execute([':id'=>$_SESSION['memberID']]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			return $row['pizzaPoints'];
		}
		else{
			return "";
		}
	}
	
	function refundPizzaPoints($quantity){
		
		if($_SESSION['memberID'] != null){
			//Retrieves user's account details.
			$stmt = PDOConnection::getConnection()->prepare('SELECT pizzaPoints 
								  FROM `user`
								  WHERE id = :id');
			$stmt->execute([':id'=>$_SESSION['memberID']]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$stmt2 = PDOConnection::getConnection()->prepare('UPDATE `user` 
								  SET pizzaPoints = :pizzaPoints
								  WHERE id = :id');
			$stmt2->execute([':id'=>$_SESSION['memberID'], ':pizzaPoints'=>($row['pizzaPoints']+(6*$quantity))]);
			$row2 = $stmt2->fetch(PDO::FETCH_ASSOC);

			return 1;
		}
		else{
			return "";
		}
	}
	
	function subtractPizzaPoints($pizzaPoints){
		
		if($_SESSION['memberID'] != null){
			$stmt = PDOConnection::getConnection()->prepare('UPDATE `user` 
								  SET pizzaPoints = :pizzaPoints
								  WHERE id = :id');
			$stmt->execute([':id'=>$_SESSION['memberID'], ':pizzaPoints'=>$pizzaPoints-6]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			return 1;
		}
		else{
			return "";
		}
	}
}
?>