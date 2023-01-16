# Pizza Gang Delivery Website
## Description
Pizza Gang is a website that allows users to order pizzas or other food items, track orders as visitors or customers or update various items or orders as an admin.

### GENERAL NOTES:
1. There are three user types: visitor, admin, and member.
2. Some actions are only accessible by certain member types.
3. Only admins can create, update, or delete menu items and promotions along with viewing all orders.
4. Only members can access "my orders" (viewing orders that belong to them based on their user id).
5. Only members and guests can add items to cart and place an order.
6. Only admins and members can access the promotion page.
7. Only members can apply promo codes to their order.

## Demo

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
"Redeem Points" tab as a customer without having to constantly log out and log back in. Also, redeeming pizza points
will use up the points, but they can be refunded if the free pizza is removed from the cart. Points are only committed
when the checkout is confirmed.

Certain buttons will be greyed out for the admin accompanied by a warning. The reason for this (with the exception of orders)
are because of foreign key constraints. If an item has been previously purchased, that is recorded in a previous order,
then deleting it would cause issues. This is the same for promotions. Orders, on the other hand, are able to be deleted but
only when its status is cancelled. Members are able to cancel their orders from the "My Orders" tab. Since visitors do not
have access to that tab, the only way for them to cancel is through contacting the fictional hotline on order details
of a tracked order on the tracking page.

KNOWN BUGS:

> There is a bug in which when you press on redeem points it will refresh the page. This will usually occur the first time you
> interact with the button, so subsequent interactions will load the page properly.
