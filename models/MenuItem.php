<?php
require_once("PDOConnection.php");


class MenuItem{
	
	private $id, $itemName, $category, $price, $tax, $isListed;
	static private $items = [];
	
	function __construct($id, $itemName, $category, $price, $isListed) {
		$this->id = $id;
		$this->itemName = $itemName;
		$this->category = $category;
		$this->price = $price;
		$this->isListed = $isListed;
		MenuItem::$items[] = $this;
	}

	static function loadAllItems(): void{
		try {
			$sql = "SELECT *
					FROM menuitem
					ORDER BY category";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$isListed = ($row['isListed'] == 1) ? "Yes" : "No";
				$item = new MenuItem($row['id'], $row['itemName'], $row['category'], $row['price'], $isListed);
			}
		}
		catch(Exception $e) {
			echo "EXCEPTION: ".$e->getMessage();
		}
	}
	
	//Can be displayed at top right to denote how many items are in the cart.
	function calculateTotalQuantity(): int{
		$quantity = 0;
		foreach($_SESSION['cart'] as $item){
			$quantity += $item['quantity'];	
		}
		return $quantity;
	}
	
	static function getAll(): array {
		return MenuItem::$items;
	}

	function getAssociativeArray() {
		$sql = "SELECT *
				FROM `order_menuitem`
				WHERE menuItemID = :menuItemID";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':menuItemID'=>$this->id]);
			
			//Checks to see if item has been purchased before (i.e. is used a foreign key in `order_menuitem`).
			//If 1, will be used to prevent deletion of item.
			$previouslyPurchased = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$previouslyPurchased = $previouslyPurchased == true ? 1 : 0;
		
		return ['id'=>$this->id, 'itemName'=>$this->itemName, 'category'=>$this->category, 'price'=>$this->price,
				'isListed'=>$this->isListed,'previouslyPurchased'=>$previouslyPurchased];
	}

	
	function addMenuItem($name, $category, $price){
		try {
			//Checks to see if any menu items are currently using the name provided.
			$sql = "SELECT *
					FROM `menuitem`
					WHERE itemName = :itemName";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':itemName'=>$name]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
			//If menu item with the same name exists, prevents adding the item.
			if($row){
				return "duplicate";
			}
			else{
			$sql = "INSERT INTO `menuitem`(itemName, category, price)
					VALUES(:itemName, :category, :price)";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':itemName'=>$name, ':category'=>$category, ':price'=>$price]);
			
			$itemID = PDOConnection::getConnection()->lastInsertId();
			
			$sql = "SELECT *
					FROM `menuitem`
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$itemID]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
			return $row;
			}
		}
		catch(Exception $e) {
			echo "EXCEPTION: ".$e->getMessage();
		}
	}
	
	//Only works if nobody has ever ordered the item due to foreign key constraints.
	function deleteMenuItem($itemID){
		try {
			$sql = "SELECT *
					FROM `menuitem`
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$itemID]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
			$itemToBeRemoved = $row;
			
			$sql = "DELETE FROM `menuitem` 
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$itemID]);
			
			//Checks if row was successfully removed
			$sql = "SELECT *
					FROM `menuitem`
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$itemID]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if($row){
				return "";
			}
			else{
				return $itemToBeRemoved;
			}
		}
		catch(Exception $e) {
			echo "EXCEPTION: ".$e->getMessage();
		}
	}
	
	function updateMenuItem($itemID, $name, $category, $price, $isListed){
		try {
			//Checks to see if the name is currently in use by another menu item.
			$sql = "SELECT *
					FROM `menuitem`
					WHERE itemName = :itemName";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':itemName'=>$name]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
			//If menu item with the same name exists and is not referring to itself, prevents adding the item.
			if($row && $row['id'] != $itemID){
				return "duplicate";
			}
			//If the name is not shared with other menu items or is in use by itself, execute else block.
			else{
			$sql = "UPDATE `menuitem`
					SET itemName = :itemName, category = :category, price = :price, isListed = :isListed
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$itemID, ':itemName'=>$name, ':category'=>$category, ':price'=>$price, ':isListed'=>$isListed]);
			
			$sql = "SELECT *
					FROM `menuitem`
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$itemID]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
			return $row;
			}
		}
		catch(Exception $e) {
			echo "EXCEPTION: ".$e->getMessage();
		}
	}
}
?>