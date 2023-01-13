let firstName, lastName, memberType, memberID, numberOfCartItems, grandTotal, promoCodeRemoved, pizzaPoints, pizzaArray;

	function onLoadPage(){
		event.preventDefault();
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:"onLoad"},
			dataType: "JSON"
		})
		.done(function(sessionData) {
			//Registers when a user clicks the button and sends then to the login page.
			firstName = sessionData.firstName;
			lastName = sessionData.lastName;
			memberType = sessionData.memberType;
			memberID = sessionData.memberID;
			if(memberType != null && memberType != "admin"){showMemberNavBar(); showHomePage();}
			else if(memberType == "admin"){showadminNavBar(); showAdminDashboard();}
			else{showGuestNavBar(); showHomePage();}
			
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			//Nothing
		});
	}

//LOGIN, SIGN UP, AND LOGOUT FUNCTIONS
	//Submitting Sign Up Form
	function signUp(){
		event.preventDefault();
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("signUp"), firstName:$("#firstNameID").val(), lastName:$("#lastNameID").val(), username:$("#usernameID2").val(), 
					password: $("#passwordID2").val(), passwordConfirm: $("#confirmPasswordID").val()},
			dataType: "JSON"
		})
		.done(function(data) {
			//Clears out all details from text fields upon successful registration.
			$("#firstNameID").val("");
			$("#lastNameID").val(""); 
			$("#usernameID2").val(""); 
			$("#passwordID2").val("");
			$("#confirmPasswordID").val(""); 
			
			//Registers when a user clicks the button and sends then to the login page.
			vex.dialog.open({
				message: "Registration successful!",
				overlayClosesOnClick: false,
				input: [].join(''),
				buttons: [
					$.extend({}, vex.dialog.buttons.YES, { text: 'Okay' })
				],
				callback: function (data) {
					if (data) {
						showLogin();
					}
				}
			})
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}

	//Submitting Login Form
	function login(){
		event.preventDefault();
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:"login", username:$("#usernameID1").val(), password: $("#passwordID1").val()},
			dataType: "JSON"
		})
		.done(function(accountDetails) {
			//Clears out all details from text fields upon successful login.
			$("#usernameID1").val(""); 
			$("#passwordID1").val("");
			
			//Registers when a user clicks the button and sends then to the login page.
			vex.dialog.open({
				message: "Login Successful",
				overlayClosesOnClick: false,
				input: [].join(''),
				buttons: [
					$.extend({}, vex.dialog.buttons.YES, { text: 'Okay' })
				],
				callback: function (data) {
					if (data) {
						firstName = accountDetails.firstName;
						$("#welcomeTitle").empty();
						$("#welcomeTitle").append("Welcome to Pizza Gang, " + firstName + "!");
						$("#myOrders").show();
						$("#login").hide();
						$("#logout").show();
						onLoadPage();
					}
				}
			})
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}

	//Logging Out
	function logout(){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:"logout"},
			dataType: "JSON"
		})
		.done(function(data) {
			vex.dialog.open({
				message: "Successfully Logged out.",
				overlayClosesOnClick: false,
				input: [].join(''),
				buttons: [
					$.extend({}, vex.dialog.buttons.YES, { text: 'Okay' })
				],
				callback: function (data) {
					if (data) {
						firstName = null;
						lastName = null;
						memberType = null;
						memberID = null;
						showGuestNavBar();
						showHomePage();
					}
				}
			})
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}
	
	
	
//CREATE/ADD FUNCTIONS
	//Create New Promotion
	function requestAddPromotion(){
		event.preventDefault();
		vex.dialog.open({
			message: 'Input details for the new promotion below',
			overlayClosesOnClick: false,
			input: [
				'<label for="promoCode">Promo Code:</label>' +
				'<input type="text" name="promoCode" id="promoCode" placeholder="enter promo code"' + 
				'pattern="[A-Za-z0-9\-]{1,}" title="Please don\'t insert special characters" required/>' +
				'<label for="discountRate">Discount Rate (%):</label>' +
				'<input type="number" name="discountRate" id="discountRate" value="" placeholder="enter price" min="1" max="100" step="0.5" required/>'
			].join(''),
			buttons: [
				$.extend({}, vex.dialog.buttons.YES, { text: 'Confirm' }),
				$.extend({}, vex.dialog.buttons.NO, { text: 'Cancel' })
			],
			callback: function (data) {
				if (data) {
					data.discountRate /= 100;
					addPromotion(data.promoCode, data.discountRate);
				}
			}
		})
	}
	
	function addPromotion(promoCode, discountRate){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("createPromotion"), code: promoCode, discountRate: discountRate},
			dataType: "JSON"
		})
		.done(function(data) {
			vex.dialog.alert("Promo code " + data.promoCode.code + " succesfully added.")
			loadAllPromotions();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}

	//Create New Menu Item
	function requestAddItem(){
		event.preventDefault();
		vex.dialog.open({
			message: 'Input details for the new menu item below',
			overlayClosesOnClick: false,
			input: [
				'<label for="itemName">Item Name:</label>' +
				'<input type="text" name="itemName" value="" placeholder="enter item name"' + 
				'pattern="[A-Za-z0-9\-]{1,}" title="Please don\'t insert special characters" required />' +
				
				'<label for="category">Category:</label>' +
				'<select name="category" id="category" required>' + 
					'<option value="" selected disabled>select category</option>' +
					'<option value="Beverage">Beverage</option>' +
					'<option value="Dessert">Dessert</option>' +
					'<option value="Pizza">Pizza</option>' +
					'<option value="Side">Side</option>' +
				'</select>' +
				
				'<label for="price">Price:</label>' +
				'<input type="number" name="price" id="price" value="" placeholder="enter price" min="1" max="100" step=".01" required/>'
			].join(''),
			buttons: [
				$.extend({}, vex.dialog.buttons.YES, { text: 'Confirm' }),
				$.extend({}, vex.dialog.buttons.NO, { text: 'Cancel' })
			],
			callback: function (data) {
				if (data) {
					addMenuItem(data.itemName, data.price, data.category);
				}
			}
		})
	}
	
	function addMenuItem(itemName, price, category){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("addMenuItem"), itemName: itemName, price: price, category: category},
			dataType: "JSON"
		})
		.done(function(data) {
			vex.dialog.alert("Item " + data.item.itemName + " succesfully added.")
			loadAdminMenu();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}

	//Create New Order
	function checkOutConfirmation(){
		vex.dialog.open({
			message: 'Confirm order totaling RM ' + grandTotal + "?",
			overlayClosesOnClick: false,
			input: [].join(''),
			buttons: [
				$.extend({}, vex.dialog.buttons.YES, { text: 'Confirm' }),
				$.extend({}, vex.dialog.buttons.NO, { text: 'Cancel' })
			],
			callback: function (data) {
				if (data) {
					checkout();
				}
			}
		})
	}
	
	function checkout(){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("checkOut")},
			dataType: "JSON"
		})
		.done(function(order) {
			vex.dialog.open({
			message: 'Order successfully placed! The tracking ID for your order is ' + order.trackingID,
			overlayClosesOnClick: false,
			input: [].join(''),
			buttons: [
				$.extend({}, vex.dialog.buttons.YES, { text: 'Okay' }),
			],
			callback: function (data) {
				if (data) {
					loadCartCount();
					showTrackingPage();
					trackingID:$("#trackingID").val(order.trackingID);
					trackOrder();
				}
			}
			})
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}
	
	//Adds Items From Previous Order To Cart
	function repeatOrder(orderID){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("repeatOrder"), orderID: orderID},
			dataType: "JSON"
		})
		.done(function(data) {
			vex.dialog.open({
				message: 'Previously ordered items placed in cart!',
				overlayClosesOnClick: false,
				input: [].join(''),
				buttons: [
					$.extend({}, vex.dialog.buttons.YES, { text: 'Okay' }),
				],
				callback: function (data) {
					if (data) {
						loadCartCount();
						loadCart();
					}
				}
			})
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}
	
	//Select Quantity Of Item To Add To Cart
	function selectQuantity(itemID, itemName, itemPrice){
		vex.dialog.open({
			message: 'How many ' + itemName + "'s would you like to buy?",
			overlayClosesOnClick: false,
			input: [				
			'<input type="number" id="quantity" name="quantity" min="1" max="99" value="1" required>'
			].join(''),
			buttons: [
				$.extend({}, vex.dialog.buttons.YES, { text: 'Confirm' }),
				$.extend({}, vex.dialog.buttons.NO, { text: 'Cancel' })
			],
			callback: function (data) {
				if (data) {
					addToCart(itemID, itemName, itemPrice, data.quantity)
				}
			}
		})
	}
	
	function addToCart(itemID, itemName, itemPrice, quantity){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("addToCart"), quantity: quantity, id: itemID, name: itemName, price: itemPrice},
			dataType: "JSON"
		})
		.done(function(data) {
			loadCartCount();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}

	//Apply Promo Code To Order
	function applyPromoCode(){
		event.preventDefault();
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:"applyPromoCode", promoCode: $("#promoCode").val()},
			dataType: "JSON"
		})
		.done(function(accountDetails) {
			loadCart();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}
	
	function requestRedeemPizza(){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction: ("retrievePizzas")},
			dataType: "JSON"
		})
		.done(function(pizzas) {
			var pizzaArray = [];
			var i = 2;
			var itemName = "";
			event.preventDefault();
				vex.dialog.open({
				message: 'Choose which pizza you want to redeem!',
				overlayClosesOnClick: false,
				input: [
					'<label for="redeemPizza">Pizzas:</label>' +
					'<select name="pizzasRedeem" id="pizzasRedeem" required>' + 
					'</select>'
				].join(''),
				buttons: [
					$.extend({}, vex.dialog.buttons.YES, { text: 'Confirm' }),
					$.extend({}, vex.dialog.buttons.NO, { text: 'Cancel' })
				],
				callback: function (data) {
					if (data) {
						//Retrieves id of pizza chosen
						var id = data.pizzasRedeem[0];
						
						//Retrieves the name of the pizza chosen.
						for(i = 2; i < data.pizzasRedeem.length; i++){
							itemName += data.pizzasRedeem[i];
						}
						
						subtractPizzaPoints(pizzaPoints);
						addToCart(id, itemName, 0.00, 1);
						vex.dialog.alert("Pizza "+ itemName + " has been added to your cart!");
					}
				}
				
			})
			//Attaches options to <select>
			$.each(pizzas, function(index, pizza) {
				pizzaArray[index] = [pizza.id, pizza.itemName + " (FREE)"];
				$("#pizzasRedeem").append('<option value="'+pizzaArray[index]+'">'+pizzaArray[index][1]+'</option>');
			});
		})
		.fail(function(jqXHR, textStatus, errorThrown ){
			vex.dialog.alert(jqXHR.responseJSON);
		})
	}



	
//READ FUNCTIONS
	//Loads Order Based On Provided Tracking ID
	function trackOrder(){
		event.preventDefault();
		let deliveryDetails = "";
		let orderStatus = "";
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("findOrder"), trackingID:$("#trackingID").val()},
			dataType: "JSON"
		})
		.done(function(trackedOrder) {
			//Clears out all details from text fields upon successful registration.
			$("#trackOrderPageTitle").empty();
			$("#trackOrderPageDetails").empty();
			$("#status").empty();
			$("#trackerOrderStatus").empty();
			$("#orderDetailsTitle").empty();
			
			$("#trackOrderPageTitle").append("Order: " + trackedOrder.trackingID);
			
			if(trackedOrder.status == "Delivered"){
				orderStatus = '<p style="color: #85e036">' + trackedOrder.status + '</p>';
			}
			else if(trackedOrder.status == "Cancelled"){
				orderStatus = '<p style="color: red" id="orderStatus">' + trackedOrder.status + '</p>';
			}
			else{
				orderStatus = trackedOrder.status;
			}
			
			"<br>" + $("#status").append("Status: ");
			"<br>" + $("#trackerOrderStatus").append("Status: " + orderStatus);
			
			
			$.each(trackedOrder.orderedItems, function(index, item){
				if(item.total == 0){
					$("#trackOrderPageDetails").append(item.quantity + "x " + item.itemName + " (FREE)<br>");
				}
				else{
					$("#trackOrderPageDetails").append(item.quantity + "x " + item.itemName + " (RM " + parseFloat(item.total).toFixed(2) + ")<br>");
				}
			});
			
			if(trackedOrder.dateDelivered != null && trackedOrder.timeDelivered != null){
				deliveryDetails = trackedOrder.dateDelivered + " - " + trackedOrder.timeDelivered;
			}
			else if(trackedOrder.status == "Cancelled"){
				deliveryDetails = 'Cancelled';
			}
			else{
				deliveryDetails = 'N/A';
			}
	
			$("#orderDetailsTitle").append("Order Details: ");
			$("#trackOrderPageDetails").append("<br>Ordered: " + trackedOrder.datePlaced + " - " + trackedOrder.timePlaced + "<br>" +
												"Delivered: " + deliveryDetails + "<br>" + 
												"<br>Promo Code: " + trackedOrder.promoCode + "<br>" + 
												"Taxed Amount: RM " + parseFloat(trackedOrder.taxPaid).toFixed(2) + "<br>" + 
												"Discounted Amount: RM " + parseFloat(trackedOrder.discountedAmount).toFixed(2) + "<br>" + 
												"Grand Total: RM " + parseFloat(trackedOrder.grandTotal).toFixed(2) + "<br>" +
												"<br>If you need assistance with your order, please call <br> 1-800-PIZZA-GANG");
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}
	
	function loadAdminMenu(){
		let deleteButton = "";
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("loadMenu")},
			dataType: "JSON"
		})
		.done(function(menuItems) {
				$("#adminMenuBody").empty();
				$("#adminMenuHead").empty();
				$("#adminMenuHead").append('<tr>' +
				'<th>No.</th>' +
				'<th>Item</th>' +
				'<th>Category</th>' +
				'<th>Price (RM)</th>' +
				'<th>Listed?<a style="color:red;" href="#" data-toggle="tooltip" data-placement="top"'+
					'title="If listed, customers can see this item on the menu.">* </a></th>' +
				'<th>Update</th>' +
				'<th>Delete</th>' +
				'</tr>');
			
				$.each(menuItems, function(index, item) {
					if(item.previouslyPurchased != 1){
						deleteButton = '<td><input type="image" src=./img/delete.png width="24" height="24" onclick=\'requestMenuItemDeletion('+item.id+',"'+item.itemName+'")\';/></td>';
					}
					else{
						deleteButton = '<td><input type="image" src=./img/undeleteable.png width="24" height="24" onclick=\'vex.dialog.alert' + 
							'("Sorry, this item cannot be deleted as it has been purchased before. If you want to remove it from the menu, it can be unlisted.")\';/></td>'
					}
				
					$("#adminMenuBody").append('<tr><th scope="row">'+(index+1)+'</th>'+
						'<td>'+item.itemName+'</td>'+
						'<td>'+item.category+'</td>'+
						'<td>'+parseFloat(item.price).toFixed(2)+'</td>'+
						'<td>'+item.isListed+'</td>'+
						'<td><input type="image" src=./img/update.png width="24" height="24" onclick=\'requestUpdateMenuItem('+item.id+',"'+
									item.itemName+'","'+item.category+'",'+item.price+',"'+item.isListed+'")\';/></td>'+ deleteButton +
					'</tr>');
				});
				hideAllPages();
				$("#addMenuItem").show();
				showAdminMenu();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}
	
	function loadAllPromotions(){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("loadAllPromotions")},
			dataType: "JSON"
		})
		.done(function(promotions) {
			let deleteButton = "";
			$("#promotionsHead").empty();
			$("#promotionsBody").empty();
			$("#promotionsHead").append('<tr>' +
			'<th>No.</th>' +
			'<th>Code</th>' +
			'<th>Discount Rate</th>' +
			'<th>Currently Valid?<a style="color:red;" href="#" data-toggle="tooltip" data-placement="top"'+
				'title="If valid, customers can use and see this promotion on the promotions page.">* </a></th>'+
			'<th>Update</th>'+
			'<th>Delete</th>'+
			'</tr>');
		
			$.each(promotions, function(index, promotion) {
				if(promotion.previouslyUsed == 1){
					deleteButton = '<input type="image" src=./img/undeleteable.png width="24" height="24" onclick=\'vex.dialog.alert' + 
						'("Sorry, this promotion cannot be deleted as it has been used before. If you want to remove it from the promotions page,' + 
						' it can be set to invalid.")\';/></td>';
				}
				else{
					deleteButton = '<input type="image" src=./img/delete.png width="24" height="24" onclick=\'requestPromotionDeletion'+
						'('+promotion.id+',"'+promotion.code+'")\';/>';
				}
				
				$("#promotionsBody").append('<tr><th scope="row">'+(index+1)+'</th>'+
				'<td>'+promotion.code+'</td>'+
				'<td>'+(promotion.discountRate*100)+'%</td>'+
				'<td>'+promotion.isValid+'</td>'+
				'<td><input type="image" src=./img/update.png width="24" height="24" onclick=\'requestUpdatePromotion('+promotion.id+',"'+
						promotion.code+'","'+promotion.discountRate+'","'+promotion.isValid+'")\';/></td>'+
				'<td>'+ deleteButton +'</td>'+
				'</tr>');
			});
			hideAllPages();
			$("#addPromotion").show();
			showPromotions();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}
	
	function loadAllOrders(){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:"loadAllOrders"},
			dataType: "JSON"
		})
		.done(function(orders){
			$("#ordersHead").empty();
			$("#ordersBody").empty();
			$("#ordersHead").append('<tr>' +
			'<th>No.</th>' +
			'<th>Order Details</th>' +
			'<th>Tracking ID:</th>' +
			'<th>Promo Code</th>' +
			'<th>Status</th>' +
			'<th>Placed</th>' +
			'<th>Delivered</th>' +
			'<th>Update Status</th>' +
			'<th>Delete</th>' +
			'</tr>');

			$.each(orders, function(index, order) {
				let orderedItems = "";
				let deleteOrderButton = "";
				let deliveryDetails = "";
				$.each(order.orderedItems, function(index2, item){
					orderedItems += item.quantity + "x " + item.itemName + ' (RM ' + parseFloat(item.total).toFixed(2) + ')<br>';
				});
				
				if(order.status == "Cancelled"){
					deleteOrderButton = '<td><input type="image" src=./img/delete.png width="24" height="24"' +
					'onclick=\'requestDeleteOrder("'+order.orderID+'","'+order.trackingID+'")\';/></td></tr>'
				}
				else{
					deleteOrderButton = '<td><input type="image" src=./img/undeleteable.png width="24" height="24" onClick=' +
					'\'vex.dialog.alert("Sorry, only cancelled orders can be deleted.")\'/></td></tr>';
				}
				if(order.dateDelivered != null && order.timeDelivered != null){
					deliveryDetails = '<td>' + order.dateDelivered + " - " + order.timeDelivered + '</td>';
				}
				else if(order.status == "Cancelled"){
					deliveryDetails = '<td>Cancelled</td>';
				}
				else{
					deliveryDetails = '<td>N/A</td>';
				}
				
				$("#ordersBody").append('<tr><th>'+(index+1)+'</th>' + 
								'<td>' + orderedItems + '<br>Tax Paid: RM ' + parseFloat(order.taxPaid).toFixed(2) + '<br>Amount Discounted: RM ' + 
									parseFloat(order.discountedAmount).toFixed(2) + '<br>Grand Total: RM ' + parseFloat(order.grandTotal).toFixed(2) + '</td>' + 
								'<td>' + order.trackingID + '</td>'+
								'<td>' + order.promoCode + '</td>' + 
								'<td>' + order.status + '</td>' +
								'<td>' + order.datePlaced + " - " + order.timePlaced + '</td>' + deliveryDetails +
								'<td><input type="image" src=./img/update.png width="24" height="24"' +
									'onclick=\'requestUpdateStatus("'+order.orderID+'","'+order.status+'","'+order.trackingID+'")\';/></td>' + deleteOrderButton);
			});
			
			showMyOrders();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}	
	
	function loadMyOrders(){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:"viewMyOrders"},
			dataType: "JSON"
		})
		.done(function(orders){
			let orderStatus = "";
			$("#ordersHead").empty();
			$("#ordersBody").empty();
			$("#ordersHead").append('<tr>' +
			'<th>No.</th>' +
			'<th>Order Details</th>' +
			'<th>Tracking ID:</th>' +
			'<th>Promo Code</th>' +
			'<th>Status</th>' +
			'<th>Placed</th>' +
			'<th>Delivered</th>' +
			'<th>Reorder</th>' +
			'</tr>');

			$.each(orders, function(index, order) {
				let orderedItems = "";
				let deliveryDetails = "";
				$.each(order.orderedItems, function(index2, item){
					orderedItems += item.quantity + "x " + item.itemName + ' (RM ' + parseFloat(item.total).toFixed(2) + ')<br>';
				});
				
				//Orders can only be canceled when order status is "Preparing"
				if(order.status == "Preparing"){
					orderStatus = '<td>' + order.status + ' (<a href="#" onClick=\'requestCancelOrder('+order.orderID+',"'+order.trackingID+'")\';><u>Cancel</u></a>)</td>';
				}
				else{orderStatus = '<td>' + order.status + '</td>';}
				
				if(order.dateDelivered != null && order.timeDelivered != null){
					deliveryDetails = '<td>' + order.dateDelivered + " - " + order.timeDelivered + '</td>';
				}
				else if(order.status == "Cancelled"){
					deliveryDetails = '<td>Cancelled</td>';
				}
				else{
					deliveryDetails = '<td>N/A</td>';
				}
				
				$("#ordersBody").append('<tr><th>'+(index+1)+'</th>' + '<td>' + orderedItems + '<br>Tax Paid: RM ' + parseFloat(order.taxPaid).toFixed(2) + '<br>' + 
								'Amount Discounted: RM ' + parseFloat(order.discountedAmount).toFixed(2) + 
								'<br>Grand Total: RM ' + parseFloat(order.grandTotal).toFixed(2) + '</td>' +	 
								'<td>' + order.trackingID + '</td>' +
								'<td>' + order.promoCode + '</td>' + orderStatus +
								'<td>' + order.datePlaced + " - " + order.timePlaced + '</td>' + deliveryDetails +
								'<td><input type="image" src=./img/repeat.png width="24" height="24" onclick=\'repeatOrder('+order.orderID+')\';/></td></tr>');
				
			});
			
			showMyOrders();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}
	
	function loadCartCount(){
	event.preventDefault();
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:"getNumberOfCartItems"},
			dataType: "JSON"
		})
		.done(function(cartCount) {
			//Registers when a user clicks the button and sends then to the login page.
			numberOfCartItems = cartCount.numberOfCartItems;
			$("#cartLabel").empty();
			if(numberOfCartItems > 0){$("#cartLabel").append("Cart (" + numberOfCartItems + ")");}
			else{$("#cartLabel").append("Cart (0)");}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			//Nothing
		});
	}
	
	function loadCart(){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("loadCartItems")},
			dataType: "JSON"
		})
		.done(function(cart) {
			if(cart.orderedItems == null){
				loadMenu();
				vex.dialog.alert("Oops - Looks like your cart is empty. Why not add some items?");
				showMenu();
			}
			else{
				$("#cartBody").empty();
				$("#cartHead").empty();
				$("#cartHead").append('<tr>' +
				'<th>No.</th>' +
				'<th>Item</th>' +
				'<th>Price</th>' +
				'<th>Quantity</th>' +
				'<th>Total</th>' +
				'<th>Update</th>' +
				'<th>Remove</th>' +
				'</tr>');
			
				$.each(cart.orderedItems, function(index, cartItems) {
					$("#cartBody").append('<tr><th scope="row">'+(index+1)+'</th>'+
						'<td>'+cartItems.itemName+'</td>'+
						'<td>RM '+cartItems.price+'</td>'+
						'<td>'+cartItems.quantity+'</td>'+
						'<td>RM '+cartItems.total+'</td>'+
						'<td><input type="image" src=./img/update.png width="24" height="24" onclick=\'requestUpdateQuantity('+cartItems.id+',"'+
							cartItems.itemName+'",'+cartItems.quantity+')\';/></td>'+
						'<td><input type="image" src=./img/delete.png width="24" height="24" onclick=\'requestRemoval("'+cartItems.itemName+'")\';/></td>'+
					'</tr>');
				});
				
				$("#grandTotal").empty();
				$("#taxedAmount").empty();
				$("#discountedAmount").empty();
				$("#currentPromoCode").empty();
				$("#taxedAmount").append("Taxed Amount: RM " + cart.taxedAmount);
				
				//Ensures only members are allowed to use promotions.
				if(memberType == "member"){
					promoCodeRemoved = 0;
					$("#enteredPromoCode").show();
					if(cart.discountedAmount == "N/A"){$("#discountedAmount").append("Discounted Amount: " + cart.discountedAmount);} 
					else{$("#discountedAmount").append("Discounted Amount: RM " + cart.discountedAmount);}
					if(cart.promoCode != "N/A"){$("#currentPromoCode").append("Applied Promo Code: " + cart.promoCode + 
												' (' + '<a onClick="removePromoCode()"><u>Remove</u></a>' + ')');}
					else{$("#currentPromoCode").append("Applied Promo Code: " + cart.promoCode);}
				}
				//promoCodeRemoved prevents an infinite loops as loadCart calls removePromoCode, which then calls loadCart.
				else if(memberType != "member" && promoCodeRemoved != 1){
					promoCodeRemoved = 1;
					$("#enteredPromoCode").hide();
					removePromoCode();
				}
				
				$("#grandTotal").append("Grand Total: RM " + cart.grandTotal);
				grandTotal = cart.grandTotal;
				showCart();
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}
	
	function loadValidPromotions(){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:"viewValidPromotions"},
			dataType: "JSON"
		})
		.done(function(promotions) {
			if(	promotions == null){
				$("#promotionsHead").empty();
				$("#promotionsBody").empty();
				$("#promotionsHead").append('<tr>' +
				'<th>Oops - Looks like there aren\'t any promotions running currently.</th>' +
				'</tr>');
			}
			else{
				$("#promotionsHead").empty();
				$("#promotionsBody").empty();
				$("#promotionsHead").append('<tr>' +
				'<th>No.</th>' +
				'<th>Code</th>' +
				'<th>Discount Rate</th>' +
				'</tr>');
			
				$.each(promotions, function(index, promotion) {
					$("#promotionsBody").append('<tr><th scope="row">'+(index+1)+'</th>'+
						'<td>'+promotion.code+'</td>'+
						'<td>'+(promotion.discountRate*100)+'%</td>'+
					'</tr>');
				});
				hideAllPages();
				showPromotions();
			}
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}

	function loadMenu(){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("loadMenu")},
			dataType: "JSON"
		})
		.done(function(allMenuItems) {
			$("#pizzas").empty();
			$("#sides").empty();
			$("#beverages").empty();
			$("#desserts").empty();
			
			$.each(allMenuItems, function(index, item) {
				switch(item.category){
					case "Pizza":
						$("#pizzas").append('<div style="float:left; padding-left: 10px; padding-bottom: 100px; width: 33%;">'+
						'<img src=./img/'+ item.itemName.replace(/\s/g, "") +'.jpg width="200" height="200">' +
						'<dd>' + item.itemName + ' - ' + parseFloat(item.price).toFixed(2) + ' RM</dd>' +
						'<dd><input type="button" value="Add to cart" onclick=\'selectQuantity(' + item.id +',"' + item.itemName +'",'+ item.price +')'+
						'\';/></dd></div>');
						break;
					case "Side":
						$("#sides").append('<div style="float:left; padding-left: 10px; padding-bottom: 100px; width: 33%;">'+
						'<img src=./img/'+ item.itemName.replace(/\s/g, "") +'.jpg width="200" height="200">' +
						'<dd>' + item.itemName + ' - ' + parseFloat(item.price).toFixed(2) + ' RM</dd>' +
						'<dd><input type="button" value="Add to cart" onclick=\'selectQuantity(' + item.id +',"' + item.itemName +'",'+ item.price +')'+
						'\';/></dd></div>');
						break;
					case "Beverage":
						$("#beverages").append('<div style="float:left; padding-left: 10px; padding-bottom: 100px; width: 33%;">'+
						'<img src=./img/'+ item.itemName.replace(/\s/g, "") +'.jpg width="200" height="200">' +
						'<dd>' + item.itemName + ' - ' + parseFloat(item.price).toFixed(2) + ' RM</dd>' +
						'<dd><input type="button" value="Add to cart" onclick=\'selectQuantity(' + item.id +',"' + item.itemName +'",'+ item.price +')'+
						'\';/></dd></div>');
						break;
					case "Dessert":
						$("#desserts").append('<div style="float:left; padding-left: 10px; padding-bottom: 100px; width: 33%;">'+
						'<img src=./img/'+ item.itemName.replace(/\s/g, "") +'.jpg width="200" height="200">' +
						'<dd>' + item.itemName + ' - ' + parseFloat(item.price).toFixed(2) + ' RM</dd>' +
						'<dd><input type="button" value="Add to cart" onclick=\'selectQuantity(' + item.id +',"' + item.itemName +'",'+ item.price +')'+
						'\';/></dd></div>');
						break;
					default:
						break;
				}
			});
			
			showMenu();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}

	function loadPizzaPoints(){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("getPizzaPoints")},
			dataType: "JSON"
		})
		.done(function(data) {
			let remainingPoints = 6;
			let pizzaGraphic = "";
			$("#pizzaPointsPageTitle").empty();
			$("#pizzaPointsCounter").empty();

			if(data.pizzaPoints == 0){
				pizzaGraphic = '<img src=./img/0.png width="150" height="150">'
			}
			else if(data.pizzaPoints == 1){
				pizzaGraphic = '<img src=./img/1.png width="150" height="150">'
			}
			else if(data.pizzaPoints == 2){
				pizzaGraphic = '<img src=./img/2.png width="150" height="150">'
			}
			else if(data.pizzaPoints == 3){
				pizzaGraphic = '<img src=./img/3.png width="150" height="150">'
			}
			else if(data.pizzaPoints == 4){
				pizzaGraphic = '<img src=./img/4.png width="150" height="150">'
			}
			else if(data.pizzaPoints == 5){
				pizzaGraphic = '<img src=./img/5.png width="150" height="150">'
			}		
			else if(data.pizzaPoints >= 6){
				pizzaGraphic = '<img src=./img/6.png width="150" height="150">'
			}
			else{
				pizzaGraphic = '<img src=./img/0.png width="150" height="150">'
			}

			$("#pizzaPointsPageTitle").append("Hi there, " + firstName + "!");
			if(data.pizzaPoints >= 6){
				$("#redeemButton").show();
				$("#pizzaPointsCounter").append("Congratulations, you have " + data.pizzaPoints + " pizza points!" +
												"<br>You can redeem " + Math.floor(data.pizzaPoints/6) + " free pizzas!" +
												'<br><br>' + pizzaGraphic);
			}
			else{
				$("#redeemButton").hide();
				remainingPoints -= data.pizzaPoints;
				$("#pizzaPointsCounter").append("You currently have " + data.pizzaPoints + " pizza points." +
												"<br>Earn "+ remainingPoints + " more points to get a free pizza!" +
												"<br><br>Earn 1 pizza point per every RM 30 spent in an order." +
												'<br><br>' + pizzaGraphic);
			}
			pizzaPoints = data.pizzaPoints;
			showRedeemPage();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});	
	}

	//Substracts pizza points when pizza is redeemed.
	function subtractPizzaPoints(pizzaPoints){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("subtractPizzaPoints"), pizzaPoints: pizzaPoints},
			dataType: "JSON"
		})
		.done(function(data) {
			pizzaPoints = data.pizzaPoints;
			loadPizzaPoints();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});	
	}

//UPDATE FUNCTIONS
	//Update Menu Item
	function requestUpdateMenuItem(itemID, itemName, currentCategory, currentPrice, currentListStatus){
		event.preventDefault();
		let listStatusValue = currentListStatus == "Yes" ? 1 : 0;
		vex.dialog.open({
			message: 'Update details for menu item ' + itemName,
			overlayClosesOnClick: false,
			input: [
				'<label for="itemName">Item Name:</label>' +
				'<input type="text" name="itemName" value="'+itemName+'" placeholder="enter item name"' +
				'pattern="[A-Za-z0-9\-]{1,}" title="Please don\'t insert special characters" required />' +
				
				'<label for="category">Category:</label>' +
				'<select name="category" id="category" required>' + 
					'<option value="'+ currentCategory +'" selected disabled>'+currentCategory+'</option>' +
					'<option value="Beverage">Beverage</option>' +
					'<option value="Dessert">Dessert</option>' +
					'<option value="Pizza">Pizza</option>' +
					'<option value="Side">Side</option>' +
				'</select>' +
				
				'<label for="price">Price:</label>' +
				'<input type="number" name="price" id="price" value="'+currentPrice+'" placeholder="enter price" min="1" max="100" step=".01" required/>' +
				
				'<label for="isListed">List Item?</label>' +
				'<select name="isListed" id="isListed" required>' + 
					'<option value="'+ listStatusValue +'" selected disabled>'+currentListStatus+'</option>' +
					'<option value="1">Yes</option>' +
					'<option value="0">No</option>' +
				'</select>'
			].join(''),
			buttons: [
				$.extend({}, vex.dialog.buttons.YES, { text: 'Confirm' }),
				$.extend({}, vex.dialog.buttons.NO, { text: 'Cancel' })
			],
			callback: function (data) {
				if (data) {
					updateMenuItem(itemID, data.itemName, data.price, data.category, data.isListed);
				}
			}
		})
	}
	
	function updateMenuItem(itemID, itemName, price, category, isListed){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("updateMenuItem"), itemID: itemID, itemName: itemName, price: price, category: category, isListed: isListed},
			dataType: "JSON"
		})
		.done(function(data) {
			vex.dialog.alert("Item succesfully updated to "+data.updatedItem.itemName+".")
			loadAdminMenu();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}

	//Update Promotion
	function requestUpdatePromotion(promotionID, promoCode, discountRate, validity){
		event.preventDefault();
		currentValidity = validity == "Yes" ? 1 : 0;
		discountRate *= 100;
		vex.dialog.open({
			message: 'Update details for  promotion \"' + promoCode + "\"",
			overlayClosesOnClick: false,
			input: [
				'<label for="promoCode">Promo Code:</label>' +
				'<input type="text" name="promoCode" id="promoCode" placeholder="enter promo code" value="'+promoCode+'"' + 
				'pattern="[A-Za-z0-9\-]{1,}" title="Please don\'t insert special characters" required />' +
			
				'<label for="discountRate">Discount Rate (%):</label>' +
				'<input type="number" name="discountRate" id="discountRate" value="'+discountRate+'"'+
						'placeholder="enter price" min="1" max="100" step="0.5" required/>' +
				
				'<label for="validity">Make Promo Valid?:</label>' +
				'<select name="validity" id="validity" required>' + 
					'<option value="'+ currentValidity +'" selected disabled>'+validity+'</option>' +
					'<option value="1">Yes</option>' +
					'<option value="0">No</option>' +
				'</select>'
			].join(''),
			buttons: [
				$.extend({}, vex.dialog.buttons.YES, { text: 'Confirm' }),
				$.extend({}, vex.dialog.buttons.NO, { text: 'Cancel' })
			],
			callback: function (data) {
				if (data) {
					data.discountRate /= 100;
					updatePromotion(promotionID, data.promoCode, data.discountRate, data.validity);
				}
			}
		})
	}
	
	function updatePromotion(promotionID, promoCode, discountRate, validity){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("updatePromotion"), promotionID: promotionID, code: promoCode, discountRate: discountRate, isValid: validity},
			dataType: "JSON"
		})
		.done(function(data) {
			vex.dialog.alert("Promo code succesfully updated to " + data.promoCode.code + ".")
			loadAllPromotions();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}
	
	//Update Status
	function requestUpdateStatus(orderID, status, trackingID){
		event.preventDefault();
		vex.dialog.open({
			message: 'Update status for order \"' + trackingID + "\"",
			overlayClosesOnClick: false,
			input: [
				'<label for="status">Status:</label>' +
				'<select name="status" id="status" required>' + 
					'<option value="'+ status +'" selected disabled>'+status+'</option>' +
					'<option value="Preparing">Preparing</option>' +
					'<option value="Cooking">Cooking</option>' +
					'<option value="Packing">Packing</option>' +
					'<option value="Out For Delivery">Out For Delivery</option>' +
					'<option value="Delivered">Delivered</option>' +
					'<option value="Cancelled">Cancelled</option>' +
				'</select>'
			].join(''),
			buttons: [
				$.extend({}, vex.dialog.buttons.YES, { text: 'Confirm' }),
				$.extend({}, vex.dialog.buttons.NO, { text: 'Cancel' })
			],
			callback: function (data) {
				if (data) {
					if(data.status != status){
						updateOrderStatus(orderID, data.status, trackingID, status);
					}
					else{
						console.log("No changes needed");
					}
				}
			}
		})
	}
	
	function updateOrderStatus(orderID, status, trackingID, previousStatus){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("updateOrderStatus"), orderID: orderID, status: status, previousStatus: previousStatus},
			dataType: "JSON"
		})
		.done(function(data) {
			vex.dialog.alert("Status for order \"" + trackingID + "\" succesfully updated.");
			loadAllOrders();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}

	//Cancel Order (does not require being an admin)
	function requestCancelOrder(orderID, trackingID){
		event.preventDefault();
		vex.dialog.open({
			message: 'Are you sure you want to cancel your order "' + trackingID + '" ?',
			overlayClosesOnClick: false,
			input: [
				'<input type="checkbox" id="confirm" required> <label for="confirm"> Confirm Cancellation</label>'
			].join(''),
			buttons: [
				$.extend({}, vex.dialog.buttons.YES, { text: 'Confirm' }),
				$.extend({}, vex.dialog.buttons.NO, { text: 'Cancel' })
			],
			callback: function (data) {
				if (data) {
					cancelOrder(orderID, trackingID);
				}
			}
		})
	}
	
	function cancelOrder(orderID, trackingID){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("cancelOrder"), orderID: orderID, status: "Cancelled"},
			dataType: "JSON"
		})
		.done(function(data) {
			vex.dialog.alert("Order \"" + trackingID + "\" succesfully cancelled.");
			loadMyOrders();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}
	
	//Update Quantity
	function requestUpdateQuantity(itemID, itemName, previousQuantity){
		vex.dialog.open({
			message: 'How many ' + itemName + "'s would you like to buy?",
			overlayClosesOnClick: false,
			input: [				
			'<input type="number" id="quantity" name="quantity" value="'+ previousQuantity +'" min="1" max="99" value="1" required>'
			].join(''),
			buttons: [
				$.extend({}, vex.dialog.buttons.YES, { text: 'Confirm' }),
				$.extend({}, vex.dialog.buttons.NO, { text: 'Cancel' })
			],
			callback: function (data) {
				if (data) {
					updateQuantity(itemID, itemName, data.quantity);
				}
			}
		})
	}

	function updateQuantity(itemID, itemName, quantity){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("updateQuantity"), quantity: quantity, id: itemID, name: itemName},
			dataType: "JSON"
		})
		.done(function(data) {
			loadCartCount();
			loadCart();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}

	
	
//DELETE/REMOVE FUNCTIONS
	//Delete Menu Item
	function requestMenuItemDeletion(itemID, itemName){
		event.preventDefault();
		vex.dialog.open({
			message: 'Are you sure you want to delete ' + itemName + "?",
			overlayClosesOnClick: false,
			input: [
				'<input type="checkbox" id="confirm" required> <label for="confirm"> Confirm Deletion</label>'
			].join(''),
			buttons: [
				$.extend({}, vex.dialog.buttons.YES, { text: 'Confirm' }),
				$.extend({}, vex.dialog.buttons.NO, { text: 'Cancel' })
			],
			callback: function (data) {
				if (data) {
					deleteMenuItem(itemID);
				}
			}
		})
	}
	
	function deleteMenuItem(itemID){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("deleteMenuItem"), itemID: itemID},
			dataType: "JSON"
		})
		.done(function(data) {
			vex.dialog.alert("Item " + data.deletedItem.itemName + " has been deleted.")
			loadAdminMenu();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}

	//Delete Promotion
	function requestPromotionDeletion(promotionID, promoCode){
		event.preventDefault();
		vex.dialog.open({
			message: 'Are you sure you want to delete promo code \"' + promoCode + "\"?",
			overlayClosesOnClick: false,
			input: [
				'<input type="checkbox" id="confirm" required> <label for="confirm">Confirm Deletion</label>'
			].join(''),
			buttons: [
				$.extend({}, vex.dialog.buttons.YES, { text: 'Confirm' }),
				$.extend({}, vex.dialog.buttons.NO, { text: 'Cancel' })
			],
			callback: function (data) {
				if (data) {
					deletePromotion(promotionID);
				}
			}
		})
	}
	
	function deletePromotion(promotionID){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("deletePromotion"), promotionID: promotionID},
			dataType: "JSON"
		})
		.done(function(data) {
			vex.dialog.alert("Promo code \"" + data.deletePromotion.code + "\" succesfully deleted.");
			loadAllPromotions();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}
	
	//Delete Order
	function requestDeleteOrder(orderID, trackingID){
		event.preventDefault();
		vex.dialog.open({
			message: 'Are you sure you want to delete order \"' + trackingID + "\"?",
			overlayClosesOnClick: false,
			input: [
				'<input type="checkbox" id="confirm" required> <label for="confirm">Confirm Deletion</label>'
			].join(''),
			buttons: [
				$.extend({}, vex.dialog.buttons.YES, { text: 'Confirm' }),
				$.extend({}, vex.dialog.buttons.NO, { text: 'Cancel' })
			],
			callback: function (data) {
				if (data) {
					deleteOrder(orderID, trackingID);
				}
			}
		})
	}
	
	function deleteOrder(orderID, trackingID){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("deleteOrder"), orderID: orderID},
			dataType: "JSON"
		})
		.done(function(data) {
			vex.dialog.alert("Order \"" + trackingID + "\" succesfully deleted.");
			loadAllOrders();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}

	//Removes Promo Code From Order
	function removePromoCode(){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("removePromoCode")},
			dataType: "JSON"
		})
		.done(function(data) {
			$("#promoCode").val("");
			loadCart();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}
	
	//Removes Item From Cart
	function requestRemoval(itemName){
		vex.dialog.open({
			message: 'Are you sure you want to remove ' + itemName + " from the cart?",
			overlayClosesOnClick: false,
			input: [].join(''),
			buttons: [
				$.extend({}, vex.dialog.buttons.YES, { text: 'Confirm' }),
				$.extend({}, vex.dialog.buttons.NO, { text: 'Cancel' })
			],
			callback: function (data) {
				if (data) {
					removeItem(itemName)
				}
			}
		})
	}
	
	function removeItem(itemName){
		$.ajax({
			type: 'post',
			url: 'controller/controller.php',
			data: {currentAction:("removeFromCart"), name: itemName},
			dataType: "JSON"
		})
		.done(function(data) {
			loadCartCount();
			loadCart();
		})
		.fail(function( jqXHR, textStatus, errorThrown ) {
			vex.dialog.alert(jqXHR.responseJSON);
		});
	}



//VIEW FUNCTIONS
	function showPromotions(){
		$("#promotionsPage").show();
	}

	function showHomePage(){
		if(firstName != null){$("#welcomeTitle").empty().append("Welcome to Pizza Gang, " + firstName + "!");}
		else{$("#welcomeTitle").empty().append("Welcome to Pizza Gang!");}
		hideAllPages();
		$("#homePage").show();
	}
	
	function showAdminDashboard(){
		hideAllPages();
		$("#adminDashboard").show();
	}
	
	function showadminNavBar(){
		hideAllPages();
		showAdminNavBar();
		$("#adminNavBar").show();
	}
	
	function showAdminMenu(){
		$("#adminMenuPage").show();
	}
	
	function showTrackingPage(){
		//Clears view of any previously displayed orders
		$("#trackingID").val("");
		$("#trackOrderPageTitle").empty();
		$("#trackOrderPageDetails").empty();
		$("#status").empty();
		$("#trackerOrderStatus").empty();
		$("#orderDetailsTitle").empty();
		hideAllPages();
		$("#trackOrderPage").show();
	}
	
	function showSignUp(){
		hideAllPages();
		$("#signUpPage").show();
	}
	
	function showLogin(){
		hideAllPages();
		$("#loginPage").show();
	}
	
	function showMenu(){
		hideAllPages();
		$("#menuPage").show();
	}
	
	function showCart(){
		hideAllPages();
		$("#cartPage").show();
	}
	
	function showMyOrders(){
		hideAllPages();
		$("#ordersPage").show();
	}
	
	function showRedeemPage(){
		hideAllPages();
		$("#pizzaPointsPage").show();
	}
	
	function hideAllPages(){
		$("#pizzaPointsPage").hide();
		$("#addMenuItem").hide();
		$("#addPromotion").hide();
		$("#adminDashboard").hide();
		$("#homePage").hide();
		$("#loginPage").hide();
		$("#signUpPage").hide();
		$("#menuPage").hide();
		$("#cartPage").hide();
		$("#promotionsPage").hide();
		$("#ordersPage").hide();
		$("#trackOrderPage").hide();
		$("#adminMenuPage").hide();
	}
	
	function showMemberNavBar(){
		loadCartCount();
		$("#pizzaPointsTab").show();
		$("#promotionsTab").show();
		$("#adminNavBar").hide();
		$("#myOrders").show()
		$("#login").hide();
		$("#logout").show();
	}
	
	function showAdminNavBar(){
		$("#customerNavBar").hide();
		$("#adminNavBar").show();
	}
	
	function showGuestNavBar(){
		loadCartCount();
		$("#pizzaPointsTab").hide();
		$("#promotionsTab").hide();
		$("#adminNavBar").hide();
		$("#customerNavBar").show();
		$("#myOrders").hide()
		$("#login").show();
		$("#logout").hide();
	}