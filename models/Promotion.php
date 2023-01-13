<?php
require_once("PDOConnection.php");

class Promotion{
	private $id, $discountRate, $isValid, $code;
	static private $promotions = [];
	
	function __construct($id, $isValid, $discountRate, $code) {
		$this->id = $id;
		$this->isValid = $isValid;
		$this->discountRate = $discountRate;
		$this->code = $code;
		Promotion::$promotions[] = $this;
	}

	static function loadAllPromotions(): void{
		
		try {
			//Retrieves all rows from table promotion
			$sql = "SELECT *
					FROM `promotion`";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				//If the column isValid is true, then "yes" the promotion is valid.
				$isValid = ($row['isValid'] == 1) ? "Yes" : "No";
				$item = new Promotion($row['id'], $isValid, $row['discountRate'], $row['code']);
			}
		}
		catch(Exception $e) {
			echo "EXCEPTION: ".$e->getMessage();
		}
	}
	
	function applyPromoCode($code){
			
		//Select promotion codes with the provided code that are still valid.
		$sql = "SELECT *
				FROM promotion
				WHERE code = :code
				AND isValid = 1";
		$stmt = PDOConnection::getConnection()->prepare($sql);
		$executeSQL = $stmt->execute([':code'=>$code]);
		
		//If a promotion is found, save the promotion data within $_SESSION and return row to be displayed.
		if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			//Checks if user has previously used this voucher.
			$sql2 = "SELECT *
				FROM `order`
				WHERE promotionID = :promotionID
				AND customerID = :customerID";
			$stmt2 = PDOConnection::getConnection()->prepare($sql2);
			$executeSQL2 = $stmt2->execute([':promotionID'=>$row['id'], ':customerID'=>$_SESSION['memberID']]);
			
			if($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
				return "used";
			}
			else{
				$_SESSION['promotion'] = $row['id'];
				$_SESSION['promoCode'] = $row['code'];
				$_SESSION['discountRate'] = $row['discountRate'];
				return $row;
			}
		}
	}

	static function loadValidPromotions(): void{
		
		try {
			//Retrieves all valid (i.e. isiValid = 1) promotions.
			$sql = "SELECT *
					FROM `promotion`
					WHERE isValid = 1";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$item = new Promotion($row['id'], $row['isValid'], $row['discountRate'], $row['code']);
			}
		}
		catch(Exception $e) {
			echo "EXCEPTION: ".$e->getMessage();
		}
	}
	
	static function getAll(): array {
		return Promotion::$promotions;
	}
	
	function getAssociativeArray() {
		
		$sql = "SELECT *
				FROM `order`
				WHERE promotionID = :promotionID";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':promotionID'=>$this->id]);
			
			//Checks to see if item has been purchased before (i.e. is used a foreign key in `order_menuitem`).
			//If 1, will be used to prevent deletion of item.
			$previouslyUsed = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$previouslyUsed = $previouslyUsed == true ? 1 : 0;
		
		
		return ['id'=>$this->id, 'isValid'=>$this->isValid, 'discountRate'=>$this->discountRate, 'code'=>$this->code, 'previouslyUsed'=>$previouslyUsed];
	}
	
	function addPromotion($discountRate, $code){
		
		//Checks if any promotions are currently valid and share the same code.
		$sql = "SELECT *
				FROM `promotion`
				WHERE code = :code
				AND isValid = 1";
		$stmt = PDOConnection::getConnection()->prepare($sql);
		$executeSQL = $stmt->execute([':code'=>$code]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		//If promotion using the same code name is currently valid, prevents adding new promotion.
		if($row){
			return "";
		}
		else{
			$sql = "INSERT INTO `promotion`(discountRate, code)
					VALUES(:discountRate, :code)";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':discountRate'=>$discountRate, ':code'=>$code]);
			
			$promoID = PDOConnection::getConnection()->lastInsertId();
			
			$sql = "SELECT *
					FROM `promotion`
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$promoID]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
			return $row;
		}
	}
	
	function updatePromotion($id, $isValid, $discountRate, $code){
		
		//Checks to see if any promotions are currently using the provided promo code.
		$sql = "SELECT *
				FROM `promotion`
				WHERE code = :code";
		$stmt = PDOConnection::getConnection()->prepare($sql);
		$executeSQL = $stmt->execute([':code'=>$code]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		
		//If promotion using the same code name is currently valid and it is referring to a promotion other than the one being updated, prevents update.
		if($row['id'] != $id && $row['id'] != null){
			return "";
		}
		else{
			$sql = "UPDATE `promotion`
					SET discountRate = :discountRate, code = :code, isValid = :isValid
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$id, ':discountRate'=>$discountRate, ':code'=>$code, 'isValid'=>$isValid]);
			
			$sql = "SELECT *
					FROM `promotion`
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$id]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
			return $row;
		}
	}
	
	//Promotion can only be deleted if it is currently not being referred to by a row in the table `order`. If it is, foreign key constraint will fail and prevent deletion.
	function deletePromotion($id){
		
		try {
			$sql = "SELECT *
					FROM `promotion`
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$id]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$promotionToBeRemoved = $row;
			
			$sql = "DELETE FROM `promotion` 
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$id]);
			
			//Checks if row was successfully removed
			$sql = "SELECT *
					FROM `promotion`
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$id]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if($row){
				return "";
			}
			else{
				return $promotionToBeRemoved;
			}
		}
		catch(Exception $e) {
			echo "EXCEPTION: ".$e->getMessage();
		}
	}
}
?>