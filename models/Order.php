<?php
require_once("PDOConnection.php");
require_once("MenuItem.php");

class Order{
	function createOrder($orderedItems, $grandTotal, $taxedAmount){
		
		$trackingID = self::generateTrackingID();
		//Create an order.
		$sql = "INSERT INTO `order`(customerID, promotionID, grandTotal, taxPaid, trackingID, datePlaced, timePlaced)
				VALUES(:customerID, :promotionID, :grandTotal, :taxPaid, :trackingID, :datePlaced, :timePlaced)";
		$stmt = PDOConnection::getConnection()->prepare($sql);
		$executeSQL = $stmt->execute([':customerID'=>$_SESSION['memberID'], ':promotionID'=>$_SESSION['promotion'], 
									  ':grandTotal'=>$grandTotal, ':taxPaid'=>$taxedAmount, ':trackingID'=>$trackingID,
									  ':datePlaced'=>date("y-m-d"), ':timePlaced'=>date("H:i:s")]);
		$orderID = PDOConnection::getConnection()->lastInsertId();	
			
		//Insert the items ordered into the order_menuitem table and set the foreign key orderID value to the previously created order (i.e. $orderID);
		foreach($orderedItems as $item){	
			$sql = "INSERT INTO `order_menuitem`(orderID, menuitemID, price, quantity, total)
					VALUES(:orderID, :menuitemID, :price, :quantity, :total)";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':orderID'=>$orderID, ':menuitemID'=>$item['id'], ':price'=>$item['price'], ':quantity'=>$item['quantity'], ':total'=>$item['total']]);
		}
		
		//Empty cart, remove promotion, and return tracking ID.
		unset($_SESSION['cart']);
		unset($_SESSION['promotion']);
		unset($_SESSION['promoCode']);
		unset($_SESSION['discountRate']);
		return $trackingID;
	}	

	//Calculate total of all items in the cart without tax or discounts.
	function calculateTotal(): float{
		$total = 0.00;
		foreach($_SESSION['cart'] as $item){
			$total += ($item['price']*$item['quantity']);	
		}
		return round($total, 2);
	}
	
	function generateTrackingID(){
		try{
			//Each character from the tracking ID will be randomly selected from the characters/numbers in $pattern. Tracking IDs will be case-sensitive.
			$pattern = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRXTUVWXYZ";
			$trackingID = "";
			for($i = 0; $i < 7; $i++){
				//Without -1, accessing an index out of bounds is possible and will throw an exception.
				$index = rand(0, strlen($pattern) - 1);
				$trackingID .= $pattern[$index];
			}
			//Ends the loop is checkTrackingID() returns 1/true;
			if(self::checkTrackingID($trackingID)){
				return $trackingID;
			}
		}
		catch(Exception $e) {
			echo "EXCEPTION: ".$e->getMessage();
		}
	}
	
	//Checks if tracking ID is in use by another order.
	function checkTrackingID($trackingID){
		
		//Retrieve all rows with generated tracking ID from generateTrackingID().
		$sql = "SELECT *
				FROM order
				WHERE trackingID = :trackingID";
		$stmt = PDOConnection::getConnection()->prepare($sql);
		$executeSQL = $stmt->execute([':trackingID'=>$trackingID]);			
		
		//If trackingID is in use, generate another tracking ID and check again.
		if($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			self::generateTrackingID();
		}
		//If trackingID does not exist/is not in use, return 1.
		else{
			return "1";
		}
	}
	
	function viewPreviousOrder(){
		
		$orders = [];
		$orderItems = [];
		//Select all orders made by a customer based on their user id. Left outer join used to include rows in which their promotionID column is null.
		$sql = "SELECT order.id, order.grandTotal, order.taxPaid, order.trackingID, order.promotionID, promotion.discountRate, 
					promotion.code, order.status, DATE_FORMAT(`order`.datePlaced, '%M %D, %Y') AS datePlaced, 
					TIME_FORMAT(`order`.timePlaced, '%h:%i:%s %p') AS timePlaced, DATE_FORMAT(`order`.dateDelivered, '%M %D, %Y') AS dateDelivered,
					TIME_FORMAT(`order`.timeDelivered, '%h:%i:%s %p') AS timeDelivered
				FROM `order` LEFT OUTER JOIN `promotion`
				ON order.promotionID = promotion.id
				WHERE customerID = :customerID";
		$stmt = PDOConnection::getConnection()->prepare($sql);
		$executeSQL = $stmt->execute([':customerID'=>$_SESSION['memberID']]);
		
		//While there are still orders/rows, execute the code in the block.
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			//Retrieve the items that were purchased in the order.
			$sql2 = "SELECT `menuitem`.itemName, `order_menuitem`.price, `order_menuitem`.quantity, `order_menuitem`.total
					FROM `order_menuitem` INNER JOIN `menuitem`
					ON `order_menuitem`.menuitemID = `menuitem`.id 
					WHERE orderID = :orderID";
			$stmt2 = PDOConnection::getConnection()->prepare($sql2);
			$executeSQL = $stmt2->execute([':orderID'=>$row['id']]);
			//While there are still items, return the row and its data.
			while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
				$orderItems[] = $row2;
			}
			//If a promotion was used, set $promoCode to the promocode and the percent discounted. Else set it to "N/A".
			$promoCode = ($row['promotionID'] != null) ? $row['code']." (".($row['discountRate']*100)."% off)" : "N/A";
			$discountedAmount = "0.00";
			if($row['discountRate'] != null){
				$discountedAmount = round((($row['grandTotal']/$row['discountRate']) - $row['grandTotal']),2);
			}
			$orders[] = ['orderID'=>$row['id'], 'trackingID'=>$row['trackingID'], 'promoCode'=>$promoCode, 'grandTotal'=>$row['grandTotal'], 
			'taxPaid'=>$row['taxPaid'], 'orderedItems'=>$orderItems, 'status'=>$row['status'], 'discountedAmount'=>$discountedAmount,
			'datePlaced'=>$row['datePlaced'], 'timePlaced'=>$row['timePlaced'], 'dateDelivered'=>$row['dateDelivered'], 'timeDelivered'=>$row['timeDelivered']];
			//Reset $orderItems so items from other orders are not included in the next loop.
			$orderItems = [];
		}
		return $orders;
	}
	
	function viewAllOrders(){
		
		$orders = [];
		$orderItems = [];
		//Retrieve all rows within order. Left outer join used to include rows in which their promotionID column is null.
		$sql = "SELECT `order`.id, `order`.grandTotal, `order`.taxPaid, `order`.trackingID, `order`.promotionID, `promotion`.discountRate, 
					`promotion`.code, `order`.status, DATE_FORMAT(`order`.datePlaced, '%M %D, %Y') AS datePlaced, 
					TIME_FORMAT(`order`.timePlaced, '%h:%i:%s %p') AS timePlaced, DATE_FORMAT(`order`.dateDelivered, '%M %D, %Y') AS dateDelivered,
					TIME_FORMAT(`order`.timeDelivered, '%h:%i:%s %p') AS timeDelivered
				FROM `order` LEFT OUTER JOIN `promotion`
				ON `order`.promotionID = `promotion`.id";
		$stmt = PDOConnection::getConnection()->prepare($sql);
		$executeSQL = $stmt->execute();
		
		//Retrieve all items that were purchased in the order.
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$sql2 = "SELECT `menuitem`.itemName, `order_menuitem`.price, `order_menuitem`.quantity, `order_menuitem`.total
					FROM `order_menuitem` INNER JOIN `menuitem`
					ON `order_menuitem`.menuitemID = `menuitem`.id 
					WHERE orderID = :orderID";
			$stmt2 = PDOConnection::getConnection()->prepare($sql2);
			$executeSQL = $stmt2->execute([':orderID'=>$row['id']]);
			
			//While there are still items, return the row and its data.
			while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
				$orderItems[] = $row2;
			}
			//If a promotion was used, set $promoCode to the promocode and the percent discounted. Else set it to "N/A".
			$promoCode = ($row['promotionID'] != null) ? $row['code']." (".($row['discountRate']*100)."% off)" : "N/A";
			$discountedAmount = "0.00";
			if($row['discountRate'] != null){
				$discountedAmount = round((($row['grandTotal']/$row['discountRate']) - $row['grandTotal']),2);
			}
			$orders[] = ['orderID'=>$row['id'], 'trackingID'=>$row['trackingID'], 'promoCode'=>$promoCode, 'grandTotal'=>$row['grandTotal'], 
						'taxPaid'=>$row['taxPaid'], 'orderedItems'=>$orderItems, 'status'=>$row['status'], 'discountedAmount'=>$discountedAmount, 
						'datePlaced'=>$row['datePlaced'], 'timePlaced'=>$row['timePlaced'], 'dateDelivered'=>$row['dateDelivered'], 
						'timeDelivered'=>$row['timeDelivered']];
			//Reset $orderItems so items from orders are not included in the next loop.
			$orderItems = [];
		}
		return $orders;
	}
	
	function findOrder($trackingID){
		
		$order = [];
		$orderItems = [];
		
		//Retrieve order with the provided tracking ID. Left outer join used to include rows in which their promotionID column is null.
		$sql = "SELECT `order`.id, `order`.grandTotal, `order`.taxPaid, `order`.trackingID, `order`.promotionID, `order`.status, 
				`promotion`.discountRate, `promotion`.code, DATE_FORMAT(`order`.datePlaced, '%M %D, %Y') AS datePlaced, 
					TIME_FORMAT(`order`.timePlaced, '%h:%i:%s %p') AS timePlaced, DATE_FORMAT(`order`.dateDelivered, '%M %D, %Y') AS dateDelivered,
					TIME_FORMAT(`order`.timeDelivered, '%h:%i:%s %p') AS timeDelivered
				FROM `order` LEFT OUTER JOIN `promotion`
				ON `order`.promotionID = `promotion`.id
				WHERE trackingID = :trackingID";
		$stmt = PDOConnection::getConnection()->prepare($sql);
		$executeSQL = $stmt->execute([':trackingID'=>$trackingID]);
		
		//Retrieve all items that were purchased in the order.
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$sql2 = "SELECT `menuitem`.itemName, `order_menuitem`.price, `order_menuitem`.quantity, `order_menuitem`.total
					FROM `order_menuitem` INNER JOIN `menuitem`
					ON `order_menuitem`.menuitemID = `menuitem`.id 
					WHERE orderID = :orderID";
			$stmt2 = PDOConnection::getConnection()->prepare($sql2);
			$executeSQL = $stmt2->execute([':orderID'=>$row['id']]);
			
			//While there are still items, return the row and its data.
			while($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)){
				$orderItems[] = $row2;
			}	
			//If a promotion was used, set $promoCode to the promocode and the percent discounted. Else set it to "N/A".
			$promoCode = ($row['promotionID'] != null) ? $row['code']." (".($row['discountRate']*100)."% off)" : "N/A";
			$discountedAmount = "0.00";
			if($row['discountRate'] != null){
				$discountedAmount = round((($row['grandTotal']/$row['discountRate']) - $row['grandTotal']),2);
			}
			$order = ['orderID'=>$row['id'], 'trackingID'=>$row['trackingID'], 'promoCode'=>$promoCode, 'grandTotal'=>$row['grandTotal'], 
						'taxPaid'=>$row['taxPaid'], 'orderedItems'=>$orderItems, 'status'=>$row['status'], 'discountedAmount'=>$discountedAmount, 
						'datePlaced'=>$row['datePlaced'], 'timePlaced'=>$row['timePlaced'], 'dateDelivered'=>$row['dateDelivered'], 
						'timeDelivered'=>$row['timeDelivered']];
		}
		return $order;
	}
	
	function makeRepeatingOrder($orderID){
		
		unset($_SESSION['cart']);
		$cartItems = [];
		
		//Retrieves all items in a particular order (based on $orderID).
		$sql = "SELECT `menuitem`.id, `menuitem`.itemName, `order_menuitem`.price, `order_menuitem`.quantity
				FROM `order_menuitem` INNER JOIN `menuitem`
				ON `order_menuitem`.menuitemID = `menuitem`.id 
				WHERE orderID = :orderID";
		$stmt = PDOConnection::getConnection()->prepare($sql);
		$executeSQL = $stmt->execute([':orderID'=>$orderID]);
		
		while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
			$cartItems[] = $row;
		}
		$_SESSION['cart'] = $cartItems;
		$_SESSION['numberOfCartItems'] = $numberOfCartItems = MenuItem::calculateTotalQuantity();
	}

	function updateOrderStatus($id, $status, $previousStatus){
		
		
		if($status == "Delivered"){
			//Sets new status and the date and time for when order was delivered.
			$sql = "UPDATE `order`
					SET status = :status, dateDelivered = :dateDelivered, timeDelivered = :timeDelivered
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$id, ':status'=>$status, ':dateDelivered'=>date("y-m-d"), ':timeDelivered'=>date("H:i:s")]);
			
			//Retrieve user id if order was made by a member
			$sql2 = "SELECT `user`.id as userID, `order`.grandTotal AS grandTotal, `user`.pizzaPoints as pizzaPoints
					FROM `user` INNER JOIN `order`
					ON `user`.id = `order`.`customerID`
					WHERE `order`.id = :id";
			$stmt2 = PDOConnection::getConnection()->prepare($sql2);
			$executeSQL2 = $stmt2->execute([':id'=>$id]);
			$row = $stmt2->fetch(PDO::FETCH_ASSOC);
			
			//Will only execute if order was made by a member.
			//If the grandtotal was greater than or equal to 30 (meaning pizza points were accumulated), add pizza points.
			if($row['grandTotal'] >= 30){
				$pizzaPoints =  floor($row['grandTotal']/30);
				$pizzaPoints += $row['pizzaPoints'];
				
				//Set users pizza points to amount before order.
				$sql3 = "UPDATE `user` 
						SET pizzaPoints = :pizzaPoints
						WHERE id = :id";
				$stmt3 = PDOConnection::getConnection()->prepare($sql3);
				$executeSQL3 = $stmt3->execute([':id'=>$row['userID'], ':pizzaPoints'=>$pizzaPoints]);
			}
		}
		//Remove pizza points from the account the order belongs to
		else if($previousStatus == "Delivered"){
			//If order status was previously delivered and reverts/changes to another status, set delivery details to null.
			$sql = "UPDATE `order`
					SET status = :status, dateDelivered = null, timeDelivered = null
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$id, ':status'=>$status]);
			
			//Retrieve user id if order was made by a member
			$sql2 = "SELECT `user`.id as userID, `order`.grandTotal AS grandTotal, `user`.pizzaPoints as pizzaPoints
					FROM `user` INNER JOIN `order`
					ON `user`.id = `order`.`customerID`
					WHERE `order`.id = :id";
			$stmt2 = PDOConnection::getConnection()->prepare($sql2);
			$executeSQL2 = $stmt2->execute([':id'=>$id]);
			$row = $stmt2->fetch(PDO::FETCH_ASSOC);
			
			//Will only execute if order was made by a member.
			//If the grandtotal was greater than or equal to 30 (meaning pizza points were accumulated), subtract their pizza points.
			if($row['grandTotal'] >= 30){
				$pizzaPoints = floor($row['grandTotal']/30);
				$pizzaPoints = $row['pizzaPoints'] - $pizzaPoints;
				
				//Set users pizza points to amount before order.
				$sql3 = "UPDATE `user` 
						SET pizzaPoints = :pizzaPoints
						WHERE id = :id";
				$stmt3 = PDOConnection::getConnection()->prepare($sql3);
				$executeSQL3 = $stmt3->execute([':id'=>$row['userID'], ':pizzaPoints'=>$pizzaPoints]);
			}
		}
		else{
			$sql = "UPDATE `order`
					SET status = :status
					WHERE id = :id";
			$stmt = PDOConnection::getConnection()->prepare($sql);
			$executeSQL = $stmt->execute([':id'=>$id, ':status'=>$status]);
		}
		return 1;
	}
	
	function deleteOrder($id){
		
		$sql = "DELETE FROM `order_menuitem`
				WHERE orderID = :id";
		$stmt = PDOConnection::getConnection()->prepare($sql);
		$executeSQL = $stmt->execute([':id'=>$id]);
		
		$sql2 = "DELETE FROM `order`
				WHERE id = :id";
		$stmt2 = PDOConnection::getConnection()->prepare($sql2);
		$executeSQL = $stmt2->execute([':id'=>$id]);
		
		return 1;
	}
}
?>