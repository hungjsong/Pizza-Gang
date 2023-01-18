# Pizza Gang Delivery Website
## Description
Pizza Gang is a website that allows users to order pizzas or other food items, track orders as visitors or customers or update various items or orders as an admin.

### GENERAL NOTES:
1. There are three user types: visitor, admin, and member.
2. Some actions are only accessible by certain member types.
3. Only admins can create, update, or delete menu items and promotions along with viewing all orders.
4. Only members can access "my orders" (viewing orders that belong to them based on their user id).
5. Only members and visitors can add items to cart and place an order.
6. Only admins and members can access the promotion page.
7. Only members can apply promo codes to their order.

## Demo
### Register For Account and Logging In
#### Registration will fail if the desired username is already in use
<img src="https://user-images.githubusercontent.com/75509901/213091485-e875cb27-2707-4f6b-8078-2ffffd877ceb.gif" height="370">

### Customer Functionalities
#### Add Items to Cart
<img src="https://user-images.githubusercontent.com/75509901/213100036-0ac60ab3-0263-48b6-9e5d-9f5748707017.gif" height="370">

#### Remove Items From Cart
<img src="https://user-images.githubusercontent.com/75509901/213082065-db7844da-f9da-42a1-a424-a2d68dbffd4c.gif" height="370">

#### Update Quantity of Item(s) in Cart
<img src="https://user-images.githubusercontent.com/75509901/213082092-d97e0e32-96c8-479b-a057-c1e0e69e6649.gif" height="370">

#### View Empty Cart
<img src="https://user-images.githubusercontent.com/75509901/213082104-5b3ba1bc-3da3-4350-af10-b477227bf5d9.gif" height="370">

#### Checkout
<img src="https://user-images.githubusercontent.com/75509901/213082116-ad749425-36d2-4adc-bbba-51d8ae7e156a.gif" height="370">

#### Track Order
<img src="https://user-images.githubusercontent.com/75509901/213082127-3f0b0b8d-b24b-4c7b-8434-98ddf8a9a254.gif" height="370">

### Pizza Gang Member Functionalities
#### Apply Promo Code
<img src="https://user-images.githubusercontent.com/75509901/213082196-82639c78-fb2f-4731-bf16-89b3c9191ac3.gif" height="370">

#### Remove Promo Code
<img src="https://user-images.githubusercontent.com/75509901/213082206-d3684101-42d7-4907-abbf-e9320584bc2d.gif" height="370">

#### My Orders View
##### Members are able to view previously made orders and are able to reorder them, which places all of the items of the order into the cart
#### Reorder
<img src="https://user-images.githubusercontent.com/75509901/213082223-aa24f23c-8260-4db6-956e-9153783c0c8a.gif" height="370">

#### Redeem Pizza Points
##### Pizza points are refunded if the free pizza added to the cart is removed and are only consumed when checkout is confirmed
<img src="https://user-images.githubusercontent.com/75509901/213082253-9ba9c8d0-d5a7-48fa-9fdf-0f99bfba7f2e.gif" height="370">

### Admin Functionalities
##### Certain buttons will be greyed out for the admin and will display a warning when clicked. The reason for this (with the exception of orders) are because of foreign key constraints. If an item has been previously purchased, that is recorded in a previous order, then deleting it would cause issues. This is the same for promotions. Orders, on the other hand, are able to be deleted but only when its status is cancelled.

#### Add Menu Item
<img src="https://user-images.githubusercontent.com/75509901/213091621-ee406f2a-8d5f-495e-ba65-b14a3c45bfb4.gif" height="370">

#### Update Menu Item
<img src="https://user-images.githubusercontent.com/75509901/213091633-d7bb0069-8f82-49fb-840b-e354ad08684d.gif" height="370">

#### Delete Menu Item
<img src="https://user-images.githubusercontent.com/75509901/213091644-54879de5-6739-4ead-9730-abbf8f568db3.gif" height="370">

#### Add Promo Code
<img src="https://user-images.githubusercontent.com/75509901/213091658-f9cc6ee0-81fc-488d-8e92-19a73ec3858c.gif" height="370">

#### Update Promo Code
<img src="https://user-images.githubusercontent.com/75509901/213091666-3615dab5-2a15-4462-95a7-c0cdb9692b51.gif" height="370">

#### Delete Promo Code
<img src="https://user-images.githubusercontent.com/75509901/213091683-b547011e-1678-4a6b-bd6f-a2cea354c77d.gif" height="370">

#### Update Order Status
<img src="https://user-images.githubusercontent.com/75509901/213100054-9cc4fe06-baa7-4c39-8f72-420393bbeb6a.gif" height="370">

#### Delete Order
<img src="https://user-images.githubusercontent.com/75509901/213091716-079b8b22-45a1-4479-856c-5433dcc40203.gif" height="370">

## Instructions to Run Locally

### CREDENTIALS:
There are three accounts currently created. If you'd like, you can also create your own account with the sign-up page,
which can be accessed from the login page. The following are the credentials for the three accounts, two of which
are admin accounts.

> Account 1 (ADMIN)
> Username: admin1
> Password: password123

> Account 2 (ADMIN)
> Username: admin2
> Password: password123

> Account 3 (MEMBER)
> Username: hungjsong
> Password: 12345

### DATABASE INSTRUCTIONS:
The database file name is "pizza_gang_songhj.sql". The name of the database is pizza_gang_songhj.
When imported there should be 5 tables: menuitem, order, order_menuitem, promotion, and user. To import the database,
make sure you have xampp up and running (see other instructions point 2). Once that's done,
type localhost/phpmyadmin into the url of your browser. On the top left, there should be a button that says new.
Click on that and name the database "pizza_gang_songhj". After that, click on the database, click the import tab at the tab,
choose file, and select the database file "pizza_gang_songhj.sql"

### OTHER INSTRUCTIONS:

1. Please drag all files in the repo into the file titled "htdocs" within the folder "xampp".

2. You will need to turn on the modules Apache and MySQL, which can be accessed via the XAMPP Control Panel
   (launched via the executable "xampp-control.exe" within the folder "xampp".

3. Click on the folder containing the files to open the website.

4. Just as a pre-caution, make sure to have an internet connection when running the website on an incognito window.
   Without the internet, it can cause one of the CDNs to give an error, which is absent on a non-incognito window.

### SIDE NOTES:
Opening an incognito window and accessing the site once more can allow you to login as an admin or customer,
whichever one is the opposite of the one currently logged in. This way it's easier to test out functionalities like
placing orders as a customer, updating its status as an admin, and checking how many points has been earned under the
"Redeem Points" tab as a customer without having to constantly log out and log back in.

KNOWN BUGS:

> There is a bug in which when you press on redeem points it will refresh the page. This will usually occur the first time you
> interact with the button, so subsequent interactions will load the page properly.
