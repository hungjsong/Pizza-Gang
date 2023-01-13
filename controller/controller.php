<?php
error_reporting(0);
require_once("../models/User.php");
require_once("../models/MenuItem.php");
require_once("../models/Order.php");
require_once("../models/Promotion.php");
date_default_timezone_set("Asia/Kuala_Lumpur");

switch($_POST['currentAction']){
	case "onLoad":
		if($_SESSION['memberID'] != null){
			http_response_code(200);
			echo json_encode(['message'=>"Account Details Retrieved", 'memberID'=>$_SESSION['memberID'],'firstName'=>$_SESSION['firstName'], 
								'lastName'=>$_SESSION['lastName'], 'memberType'=>$_SESSION['memberType'], 'numberOfCartItems'=>$_SESSION['numberOfCartItems']]);
			return;
		}
		else if($_SESSION['memberID'] == null){
			http_response_code(200);
			echo json_encode(['message'=>"Cart Retrieved", 'numberOfCartItems'=>$_SESSION['numberOfCartItems']]);
			return;
		}
		break;
	
	case "login":
		//Checks if user is currently logged in.
		if($_SESSION['memberID'] == null){
			//Checks if a username and password were provided.
			if (isset($_POST['username']) && isset($_POST['password'])) {
				$accountDetails = User::login($_POST['username'], $_POST['password']); 
				//If an account with a matching username and password exist on the database, executes the if block.
				if ($accountDetails != null) {
					http_response_code(200);
					echo json_encode(['message'=>"Logged in with account details: ", 'accountDetails'=>$accountDetails, 'firstName'=>$_SESSION['firstName']]);
					return;
				}
				//If no account was found with a matching username and password, executes else block.
				else{
					http_response_code(403);
					echo json_encode(['message'=>'Unable to login. Incorrect username or password.']);
					return;
				}
			}
		}
		//If user is currently logged in, prevents logging in again.
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Login failed. You are already logged in.', 'currentAccount'=>$_SESSION['username']]);
			return;
		}
		break;
		
	case "signUp":
		//Checks if user is currently logged in.
		if($_SESSION['memberID'] == null){
			//Checks if all details have been provided and are not null.
			if ($_POST['username'] != null && $_POST['password'] != null && $_POST['firstName'] != null && $_POST['lastName'] != null && $_POST['passwordConfirm'] != null) {
				//If password and retyped password match, execute signUp() function in User.php model.
				if($_POST['password'] == $_POST['passwordConfirm']){
					//Checks to see if username is currently in use.
					$accountDetails = User::signUp($_POST['username'], $_POST['password'], $_POST['firstName'], $_POST['lastName']); 
					//If username is currently available, signUp() successfully executed, and new row inserted into User table.
					if ($accountDetails == 1) {
						http_response_code(200);
						echo json_encode(['message'=>'Sign up successful.', 'accountDetails'=>$accountDetails]);
						return;
					}
					//If username is already in use, halts sign up process.
					else{
						http_response_code(403);
						echo json_encode(['message'=>'Username entered is currently in use.']);
						return;
					}
				}
				//If password and retyped password do not match, send back error with message.
				else{
					http_response_code(403);
					echo json_encode(['message'=>'Entered passwords do not match.']);
					return;
				}
			}
		}
		//If user is currently logged in, halts the sign up process.
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sign up failed. You are already logged in.', 'currentAccount'=>$_SESSION['username']]);
			return;
		}
		break;
		
	case "logout":
		//If no account is currently logged in, stops logout process.
		if($_SESSION['memberID'] == null){
			http_response_code(403);
			echo json_encode(['message'=>'Logout failed. Already logged out']);
			return;
		}
		else{
			$accountDetails = $_SESSION['username'];
			unset($_SESSION['username']);
			unset($_SESSION['memberID']);
			unset($_SESSION['firstName']);
			unset($_SESSION['lastName']);
			unset($_SESSION['memberType']);
			http_response_code(200);
			echo json_encode(['message'=>"Logged out from account: ", 'username'=>$accountDetails]);
			return;
		}
		break;
		
	case "repeatOrder":
		if($_POST['orderID'] != null){
			Order::makeRepeatingOrder($_POST['orderID']);
			http_response_code(200);
			echo json_encode(['message'=>'Successfully retrieved previously ordered items. Items added to cart.', 'referencedOrderItems'=>$_SESSION['cart']]);
			return;
			break;
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Unable to retrieve previous ordered items. No orderID provided']);
			return;
		}
		break;
		
	case "loadCartItems":
		//If cart is not empty, execute the following code.
		if($_SESSION['cart'] != null){
		$tax = 0.06;
		$orderedItems = [];
		$cartItems = $_SESSION['cart'];
		foreach($cartItems as $item){
			$itemTotal = round(($item['quantity']*$item['price']), 2);
			$orderedItems[] = ['id'=>$item['id'], 'itemName'=>$item['itemName'],'price'=>$item['price'], 'quantity'=>$item['quantity'], 'total'=>$itemTotal];
		}
		$total = Order::calculateTotal();
		$taxedAmount = round(($total*$tax), 2);
		$promoDiscount = $_SESSION['discountRate'];
		$grandTotal = round(($total + $taxedAmount)*(1 - $promoDiscount), 2);
		
		//If no promo code was applied, set $promoCode to "N/A" - else set to the code.
		$promoCode = ($_SESSION['promoCode'] == null) ? "N/A" : $_SESSION['promoCode'];
		//If there is no discount (i.e. no promo code applied), set value of $promoDiscount to "N/A" - else to how much was discounted.
		$promoDiscount = ($promoDiscount == null) ? "N/A" : round(($total + $taxedAmount)-(($total + $taxedAmount)*(1 - $promoDiscount)),2);
		
		http_response_code(200);
		echo json_encode(['orderedItems'=>$orderedItems, 'grandTotal'=>$grandTotal, 'taxedAmount'=>$taxedAmount, 'discountedAmount'=>$promoDiscount, 'promoCode'=>$promoCode], JSON_UNESCAPED_SLASHES);
		return;
		}
		else{
			http_response_code(200);
			echo json_encode(['message'=>'Oops - Looks like your cart is empty. Why not add some items?']);
			return;
		}
		break;

	case "retrievePizzas":
		MenuItem::loadAllItems();
		$allItems = MenuItem::getAll();
		$pizzas=[];
		
		foreach($allItems as $item){
			$dataAA = $item->getAssociativeArray();
			//Returns array with only pizzas
			if($dataAA['category'] == "Pizza"){
				$pizzas[] = ['id'=>$dataAA['id'], 'itemName'=>$dataAA['itemName'], 'category'=>$dataAA['category'], 'price'=>$dataAA['price']];
			}
		}
		http_response_code(200);
		echo json_encode($pizzas);
		return;
		break;

	case "getPizzaPoints":
		//Checks if user is a member
		if($_SESSION['memberType'] == "member"){
			$pizzaPoints = User::getPizzaPoints();
			if($pizzaPoints != null){
				http_response_code(200);
				echo json_encode(['message'=>'Pizza points retrieved.', 'pizzaPoints'=>$pizzaPoints]);
				return;
			}
			else{
				http_response_code(403);
				echo json_encode(['message'=>'Sorry. You have to be logged in check your points.']);
				return;
			}
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. You have to be a member to earn/redeem points.']);
			return;
		}	
		break;
		
	case "subtractPizzaPoints":
		//Checks if user is a member
		if($_SESSION['memberType'] == "member"){
			$pizzaPoints = User::subtractPizzaPoints($_POST['pizzaPoints']);
			if($pizzaPoints != null){
				http_response_code(200);
				echo json_encode(['message'=>'Pizza points subtracted.', 'pizzaPoints'=>$pizzaPoints]);
				return;
			}
			else{
				http_response_code(403);
				echo json_encode(['message'=>'Sorry. You have to be logged in check your points (SUB).']);
				return;
			}
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. You have to be a member to use points.']);
			return;
		}	
		break;

	case "applyPromoCode":
		//Checks if cart is empty.
		if($_SESSION['cart'] != null && $_SESSION['numberOfCartItems'] > 0){		
			$appliedPromoCode = Promotion::applyPromoCode($_POST['promoCode']);
			//If a promocode was found in the database and applied, execute if block.
			if($appliedPromoCode != null && $appliedPromoCode != "used"){
				http_response_code(200);
				echo json_encode(['message'=>'Promotion applied to your order.','appliedPromoCode'=>$appliedPromoCode]);
				return;
			}
			else if($appliedPromoCode == "used"){
				http_response_code(403);
				echo json_encode(['message'=>'Oops. Sorry. You have already used this voucher.']);
				return;
			}
			//If a promocode with the provided code not found in the database, execute else block.
			else{
				http_response_code(403);
				echo json_encode(['message'=>'Sorry. The provided promotion is not valid.']);
				return;
			}
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. Unable to apply promotion to an empty cart.']);
			return;
		}	
		break;
		
	case "removePromoCode":
		unset($_SESSION['promotion']);
		unset($_SESSION['promoCode']);
		unset($_SESSION['discountRate']);
		echo json_encode(['message'=>'Promo code removed.']);
		return;
		break;
		
	case "removeFromCart":
		$cartItems = $_SESSION['cart'];
		//Checks if item is in the cart.
		$index = array_search($_POST['name'], array_column($cartItems, 'itemName'));
		
		//If item is in the cart, remove it.
		if($index > -1){
			$quantity = $cartItems[$index][quantity];
			//Refunds pizza points
			if(strpos($_POST['name'], "FREE") != null){
				User::refundPizzaPoints($quantity);
			}
			$cartItems[$index] = [];
			$_SESSION['cart'] = array_values(array_filter($cartItems));
			$_SESSION['numberOfCartItems'] = $numberOfCartItems = MenuItem::calculateTotalQuantity();
			http_response_code(200);
			echo json_encode(['message'=>'Successfully removed item from cart.', 'itemName'=>$_POST['name']]);
			return;
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Item to remove not found in cart.']);
			return;
		}
		break;
	
		
	case "emptyCart":
		unset($_SESSION['numberOfCartItems']);
		unset($_SESSION['cart']);
		echo json_encode(['message'=>'Cart emptied.', 'cart'=>$_SESSION['numberOfCartItems']]);
		return;
		break;
		
	case "getNumberOfCartItems":
		$_SESSION['numberOfCartItems'] = $numberOfCartItems = MenuItem::calculateTotalQuantity();
		http_response_code(200);
		echo json_encode(['message'=>'Retrieved cart count', 'numberOfCartItems'=>$_SESSION['numberOfCartItems']]);
		return;
		break;
		
	case "addToCart":
		//checks if all values are not null. If all are set, executes if block.
		if($_POST['quantity'] != null && $_POST['id'] != null && $_POST['name'] != null && $_POST['price'] != null){
			$cartItems = $_SESSION['cart'];
			//Checks if item was previously in the cart.
			$index = array_search($_POST['name'], array_column($cartItems, 'itemName'));
			//If item was previously in cart, increase quantity instead of creating separate index.
			if($index > -1){
				$cartItems[$index]['quantity'] += $_POST['quantity'];
			}
			else{
				$cartItems[] = ['quantity'=>$_POST['quantity'], 'id'=>$_POST['id'], 'itemName'=>$_POST['name'], 'price'=>$_POST['price']];
			}
			$_SESSION['cart'] = $cartItems;
			$_SESSION['numberOfCartItems'] = $numberOfCartItems = MenuItem::calculateTotalQuantity();
			http_response_code(200);
			echo json_encode(['message'=>'Successfully added item.', 'itemAdded'=>$_POST['name']]);
			return;
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. Item could not be added.']);
			return;
		}
		break;
		
	case "viewMyOrders":
		//Checks to see if user is logged in.
		if($_SESSION['memberID'] != null){
			//Retrieves a user's previous orders.
			$myOrders = Order::viewPreviousOrder();
			//Checks if any orders were returned.
			if($myOrders != null){
				http_response_code(200);
				//Without JSON_UNESCAPED_SLASHES the value "N/A" in the promoCode key would look like "N\/A"
				echo json_encode($myOrders, JSON_UNESCAPED_SLASHES);
				return;
				break;
			}
			else{
				http_response_code(403);
				echo json_encode(['message'=>'Oops. Seems like you haven\'t made any orders yet.']);
				return;
				break;
			}
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'You need to login to see your orders.']);
			return;
			break;
		}
		
	case "loadAllOrders":
		//Checks to see if the user is an admin
		if($_SESSION['memberType'] == "admin"){
			$allOrders = Order::viewAllOrders();
			//Checks to see if any orders have been placed.
			if($allOrders != null){
				http_response_code(200);
				//Without JSON_UNESCAPED_SLASHES the value "N/A" in the promoCode key would look like "N\/A"
				echo json_encode($allOrders, JSON_UNESCAPED_SLASHES);
				return;
				break;
			}
			else{
				http_response_code(403);
				echo json_encode(['message'=>'No orders have been made yet.']);
				return;
				break;
			}
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. You have to be an admin to view all orders.']);
			return;
			break;
		}
		
	case "updateOrderStatus":
		//Checks to see if the user is an admin
		if($_SESSION['memberType'] == "admin"){
			if($_POST['status'] != $_POST['previousStatus']){
				$updatedOrder = Order::updateOrderStatus($_POST['orderID'], $_POST['status'], $_POST['previousStatus']);
				//Checks to see if order was updated.
				if($updatedOrder == 1){
					http_response_code(200);
					echo json_encode(['message'=>'Order updated.']);
					return;
					break;
				}
				else{
					http_response_code(403);
					echo json_encode(['message'=>'Order could not be updated.']);
					return;
					break;
				}
			}
			else{
				http_response_code(200);
				echo json_encode(['message'=>'No changes detected.']);
				return;
				break;
			}
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. You have to be an admin to update orders.']);
			return;
			break;
		}
		break;
		
	case "cancelOrder":
		$cancelledOrder = Order::updateOrderStatus($_POST['orderID'], $_POST['status']);
		//Checks to see if order was updated.
		if($cancelledOrder == 1){
			http_response_code(200);
			echo json_encode(['message'=>'Order cancelled.']);
			return;
			break;
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Order could not be cancelled.']);
			return;
			break;
		}
		break;
		
	case "deleteOrder";
		//Checks to see if the user is an admin
		if($_SESSION['memberType'] == "admin"){
			$deletedOrder = Order::deleteOrder($_POST['orderID']);
			//Checks to see if order was updated.
			if($deletedOrder == 1){
				http_response_code(200);
				echo json_encode(['message'=>'Order updated.']);
				return;
				break;
			}
			else{
				http_response_code(403);
				echo json_encode(['message'=>'Order could not be delete.']);
				return;
				break;
			}
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. You have to be an admin to delete orders.']);
			return;
			break;
		}
		break;
		
	case "findOrder":
		//Checks if a tracking ID was provided.
		if($_POST['trackingID'] != null){
			$foundOrder = Order::findOrder($_POST['trackingID']);
			if($foundOrder != null){
				http_response_code(200);
				//Without JSON_UNESCAPED_SLASHES the value "N/A" in the promoCode key would look like "N\/A"
				echo json_encode($foundOrder, JSON_UNESCAPED_SLASHES);
				return;

			}
			else{
				http_response_code(403);
				echo json_encode(['message'=>'No order with that tracking ID has been found.']);
				return;
			}
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. You must provide a tracking ID to search for an order.']);
			return;
		}
		break;
		
	case "checkOut":
		//If the cart has items in it, perform calculations and place order.
		if($_SESSION['cart'] != null){
			$tax = 0.06;
			$orderedItems = [];
			$cartItems = array_filter($_SESSION['cart']);
			foreach($cartItems as $item){
				$itemTotal = round(($item['quantity']*$item['price']), 2);
				$orderedItems[] = ['id'=>$item['id'], 'price'=>$item['price'], 'quantity'=>$item['quantity'], 'total'=>$itemTotal];
			}
			$total = Order::calculateTotal();
			$taxedAmount = round(($total*$tax), 2);
			$promoDiscount = $_SESSION['discountRate'];
			
			$grandTotal = round(($total + $taxedAmount)*(1 - $promoDiscount), 2);
			
			$trackingID = Order::createOrder($orderedItems, $grandTotal, $taxedAmount);
			
			http_response_code(200);
			echo json_encode(['message'=>'Order successfully placed.', 'trackingID'=>$trackingID]);
			return;
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Cannot place order with an empty cart.']);
			return;
		}
		break;
		
	case "loadMenu":
		MenuItem::loadAllItems();
		$allItems = MenuItem::getAll();
		$menuItems=[];
		
		if($_SESSION['memberType'] == "admin"){
			foreach($allItems as $item){
				$dataAA = $item->getAssociativeArray();
				$menuItems[] = ['id'=>$dataAA['id'], 'itemName'=>$dataAA['itemName'], 'category'=>$dataAA['category'], 'price'=>$dataAA['price'], 
								'isListed'=>$dataAA['isListed'],'previouslyPurchased'=>$dataAA['previouslyPurchased']];
			}
		}
		else{
			foreach($allItems as $item){
				$dataAA = $item->getAssociativeArray();
				
				//Returns array with only listed items
				if($dataAA['isListed'] == "Yes"){
					$menuItems[] = ['id'=>$dataAA['id'], 'itemName'=>$dataAA['itemName'], 'category'=>$dataAA['category'], 'price'=>$dataAA['price']];
				}
			}
		}
		http_response_code(200);
		echo json_encode($menuItems);
		return;
		break;
	
	case "addMenuItem":
		//Checks to see if the user is currently logged in as an admin.
		if($_SESSION['memberType'] == "admin"){
			$item = MenuItem::addMenuItem($_POST['itemName'], $_POST['category'], $_POST['price']);
			//If a menu item with the same name already exists, prevents the item from being added.
			if($item == "duplicate"){
				http_response_code(403);
				echo json_encode(['message'=>'Item with the same name already exists.']);
				return;
				break;
			}
			//If data was returned (and is not "duplicate") then the try block was successfully executed.
			else if($item != null && $item != "duplicate"){
				http_response_code(200);
				echo json_encode(['message'=>'Item successfully added.', 'item'=>$item]);
				return;
				break;
			}
			else{
				http_response_code(403);
				echo json_encode(['message'=>'Item could not be added.']);
				return;
				break;
			}
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. You have to be an admin to add menu items.']);
			return;
			break;
		}
		
	case "updateMenuItem":
		//Checks to see if the user is currently logged in as an admin.
		if($_SESSION['memberType'] == "admin"){
			$updatedItem = MenuItem::updateMenuItem($_POST['itemID'], $_POST['itemName'], $_POST['category'], $_POST['price'], $_POST['isListed']);
			//Checks if the item succesfully updated (i.e. != null) and the item name provided is not in use by another menu item (i.e. != "duplicate").
			if($updatedItem != null && $updatedItem != "duplicate"){
				http_response_code(200);
				echo json_encode(['message'=>'Item successfully updated with details: ', 'updatedItem'=>$updatedItem]);
				return;
				break;
			}
			else if($updatedItem == "duplicate"){
				http_response_code(403);
				echo json_encode(['message'=>"Sorry. Another menu item with that name already exists."]);
				return;
				break;
			}
			else{
				http_response_code(403);
				echo json_encode(['message'=>'Update failed.']);
				return;
				break;
			}
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. You have to be an admin to update menu items.']);
			return;
			break;
		}
		
	case "deleteMenuItem":
		//Checks to see if the user is currently logged in as an admin.
		if($_SESSION['memberType'] == "admin"){
			$deletedItem = MenuItem::deleteMenuItem($_POST['itemID']);
			if($deletedItem != null){
				http_response_code(200);
				echo json_encode(['message'=>"Item successfully deleted.", 'deletedItem'=>$deletedItem]);
				return;
				break;
			}
			else{
				http_response_code(403);
				echo json_encode(['message'=>'Deletion failed.']);
				return;
				break;
			}
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. You have to be an admin to delete menu items.']);
			return;
			break;
		}
	
	case "viewValidPromotions":	
		//Loads all promotions that are valid.
		Promotion::loadValidPromotions();
		$allPromos = Promotion::getAll();
		$promotions=[];
		
		foreach($allPromos as $promo){
			$dataAA = $promo->getAssociativeArray();
			$promotions[] = ['id'=>$dataAA['id'], 'discountRate'=>$dataAA['discountRate'], 'code'=>$dataAA['code']];
		}
		http_response_code(200);
		echo json_encode($promotions);
		return;
		break;
	
	case "loadAllPromotions":
		//Loads all promotions both valid and invalid.	
		Promotion::loadAllPromotions();
		$allPromos = Promotion::getAll();
		$promotions=[];
		
		foreach($allPromos as $promo){
			$dataAA = $promo->getAssociativeArray();
			$promotions[] = ['id'=>$dataAA['id'], 'isValid'=>$dataAA['isValid'], 'discountRate'=>$dataAA['discountRate'], 'code'=>$dataAA['code'],
							'previouslyUsed'=>$dataAA['previouslyUsed']];
		}
		http_response_code(200);
		echo json_encode($promotions);
		return;
		break;
	
	case "createPromotion":
		//Checks to see if the user is currently logged in as an admin.
		if($_SESSION['memberType'] == "admin"){
			//If discount rate given makes the order more expensive or discounts more than the total of the order (or is null), do not add promotion.
			if($_POST['discountRate'] < 0 || $_POST['discountRate'] > 1 || $_POST['discountRate'] == null){
				http_response_code(403);
				echo json_encode(['message'=>'Sorry. Discount rate must be a value between 0 and 1.']);
				return;
				break;
			}
			else if($_POST['code'] == null){
				http_response_code(403);
				echo json_encode(['message'=>'Sorry. A promo code must be provided.']);
				return;
				break;
			}
			else{
				$addedPromoCode = Promotion::addPromotion($_POST['discountRate'], $_POST['code']);
				if($addedPromoCode != null){
					http_response_code(200);
					echo json_encode(['message'=>'Promo code added.', 'promoCode'=>$addedPromoCode]);
					return;
					break;
				}
				else{
					http_response_code(403);
					echo json_encode(['message'=>'Sorry, that code is currently in use.']);
					return;
					break;
				}
			}
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. You have to be an admin to add new promotions.']);
			return;
			break;
		}
		
	case "updatePromotion":
		//Checks to see if the user is currently logged in as an admin.
		if($_SESSION['memberType'] == "admin"){
			//If discount rate given makes the order more expensive or discounts more than the total of the order (or is null), do not add promotion.
			if($_POST['discountRate'] < 0 || $_POST['discountRate'] > 1 || $_POST['discountRate'] == null){
				http_response_code(403);
				echo json_encode(['message'=>'Sorry. Discount rate must be a value between 0 and 1.']);
				return;
				break;
			}
			else if($_POST['code'] == null){
				http_response_code(403);
				echo json_encode(['message'=>'Sorry. A promo code must be provided.']);
				return;
				break;
			}
			else{
				$updatedPromotion = Promotion::updatePromotion($_POST['promotionID'], $_POST['isValid'], $_POST['discountRate'], $_POST['code']);
				if($updatedPromotion != null){
					http_response_code(200);
					echo json_encode(['message'=>'Promotion updated.', 'promoCode'=>$updatedPromotion]);
					return;
					break;
				}
				else{
					http_response_code(403);
					echo json_encode(['message'=>'Sorry, that code is currently in use.']);
					return;
					break;
				}
			}
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. You have to be an admin to update promotions.']);
			return;
			break;
		}
		
		
	case "deletePromotion":
		//Checks to see if the user is currently logged in as an admin.
		if($_SESSION['memberType'] == "admin"){
			//Data of the deleted promotion will be returned if successfully deleted.
			$deletedPromotion = Promotion::deletePromotion($_POST['promotionID']);
			//If data of the deleted promotion was returned, if block executes.
			if($deletedPromotion != null){
				http_response_code(200);
				echo json_encode(['message'=>'Promotion deleted','deletePromotion'=>$deletedPromotion]);
				return;
				break;
			}
			else{
				http_response_code(403);
				echo json_encode(['message'=>'Sorry. Provided promotion could not be deleted.']);
				return;
				break;
			}
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. You have to be an admin to delete promotions.']);
			return;
			break;
		}
		
	case "updateQuantity":
		//checks if all values are not null. If all are set, executes if block.
		if($_POST['quantity'] != null && $_POST['id'] != null && $_POST['name'] != null){
			$cartItems = $_SESSION['cart'];
			//Checks if item was previously in the cart.
			$index = array_search($_POST['name'], array_column($cartItems, 'itemName'));
			//If item was previously in cart, increase quantity instead of creating separate index.
			if($index > -1){
				$cartItems[$index]['quantity'] = $_POST['quantity'];
			}
			else{
				http_response_code(403);
				echo json_encode(['message'=>'Sorry. Item could quantity could not be adjusted.']);
				return;
			}
			$_SESSION['cart'] = $cartItems;
			$_SESSION['numberOfCartItems'] = $numberOfCartItems = MenuItem::calculateTotalQuantity();
			http_response_code(200);
			echo json_encode(['message'=>'Successfully adjusted item.', 'itemAdjusted'=>$_POST['name']]);
			return;
		}
		else{
			http_response_code(403);
			echo json_encode(['message'=>'Sorry. Item could quantity could not be adjusted.']);
			return;
		}
		break;

	default:
		http_response_code(401);
		echo json_encode(['message'=>'Something went wrong.']);
		return;
		break;
}
?>