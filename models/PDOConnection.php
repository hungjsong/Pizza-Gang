<?php
class PDOConnection{
	private static $dbConnection = null;
	
	private function __construct(){
		
	}
	
	public static function getConnection(){
		
		if(!self::$dbConnection){
			try{
				self::$dbConnection = new PDO('mysql:host=localhost;dbname=pizza_gang_songhj','root','');
			}
			catch(PDOException $e){
				echo $e->getMessage();
			}
		}
		
		return self::$dbConnection;
		
	}
}
?>