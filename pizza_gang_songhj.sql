-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2020 at 03:52 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pizza_gang_songhj`
--

-- --------------------------------------------------------

--
-- Table structure for table `menuitem`
--

CREATE TABLE `menuitem` (
  `id` int(11) NOT NULL,
  `itemName` text NOT NULL,
  `category` text NOT NULL,
  `price` double NOT NULL,
  `isListed` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `menuitem`
--

INSERT INTO `menuitem` (`id`, `itemName`, `category`, `price`, `isListed`) VALUES
(1, 'Cheese Pizza', 'Pizza', 15.9, 1),
(2, 'Hawaiian Pizza', 'Pizza', 15.9, 1),
(3, 'Pepperoni Pizza', 'Pizza', 15.9, 1),
(4, 'BBQ Chicken Pizza', 'Pizza', 17.9, 1),
(5, 'Vegetarian Pizza', 'Pizza', 15.9, 1),
(6, 'Smoked Mushroom Beef Pizza', 'Pizza', 17.9, 1),
(7, 'Garlic Bread', 'Side', 5.9, 1),
(8, 'BBQ Chicken Wings', 'Side', 9.9, 1),
(9, 'Spaghetti', 'Side', 7.9, 1),
(11, 'Sprite', 'Pizza', 7.91, 0),
(12, 'Ice Lemon Tea', 'Beverage', 7.9, 1),
(13, 'Mtn. Dew', 'Beverage', 7.9, 1),
(14, 'Dr. Pepper', 'Beverage', 7.9, 1),
(15, 'Chocolate Cake', 'Dessert', 6.9, 1),
(16, 'Chocolate Chip Cookies', 'Dessert', 4.9, 1),
(17, 'Neopolitan Ice Cream Sundae', 'Dessert', 8.9, 1),
(18, 'Caesar Salad', 'Side', 9.9, 1),
(19, 'Fruit Salad', 'Side', 9.9, 1),
(20, 'Italian Herb Salad', 'Side', 9.9, 1),
(21, 'Apple Pie', 'Dessert', 4.99, 1),
(22, 'Apple Juice', 'Beverage', 2.99, 1);

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `id` int(11) NOT NULL,
  `customerID` int(11) DEFAULT NULL,
  `promotionID` int(11) DEFAULT NULL,
  `grandTotal` double NOT NULL,
  `taxPaid` double NOT NULL,
  `trackingID` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `status` text NOT NULL DEFAULT 'Preparing',
  `datePlaced` date NOT NULL,
  `dateDelivered` date DEFAULT NULL,
  `timePlaced` time NOT NULL,
  `timeDelivered` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`id`, `customerID`, `promotionID`, `grandTotal`, `taxPaid`, `trackingID`, `status`, `datePlaced`, `dateDelivered`, `timePlaced`, `timeDelivered`) VALUES
(1, 3, 1, 29.42, 3.33, 'AECONEr', 'Delivered', '2017-06-15', '2020-12-04', '00:00:00', '21:38:11'),
(2, NULL, NULL, 39.75, 2.25, '1oN7dwY', 'Preparing', '2020-12-05', NULL, '11:26:40', NULL),
(3, 3, NULL, 72.5, 4.1, '0c0n1Zj', 'Preparing', '2020-12-05', NULL, '11:27:37', NULL),
(5, 3, NULL, 455.06, 25.76, 'xsU18uM', 'Delivered', '2020-12-05', '2020-12-05', '12:10:34', '12:12:26');

-- --------------------------------------------------------

--
-- Table structure for table `order_menuitem`
--

CREATE TABLE `order_menuitem` (
  `id` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `menuItemID` int(11) NOT NULL,
  `price` double NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `order_menuitem`
--

INSERT INTO `order_menuitem` (`id`, `orderID`, `menuItemID`, `price`, `quantity`, `total`) VALUES
(1, 1, 1, 15.9, 1, 15.9),
(2, 1, 2, 15.9, 1, 15.9),
(3, 1, 7, 5.9, 1, 5.9),
(4, 1, 20, 9.9, 1, 9.9),
(5, 1, 13, 7.9, 1, 7.9),
(6, 2, 18, 9.9, 1, 9.9),
(7, 2, 9, 7.9, 1, 7.9),
(8, 2, 7, 5.9, 2, 11.8),
(9, 2, 12, 7.9, 1, 7.9),
(10, 3, 15, 6.9, 1, 6.9),
(11, 3, 3, 15.9, 2, 31.8),
(12, 3, 8, 9.9, 3, 29.7),
(14, 5, 2, 15.9, 16, 254.4),
(15, 5, 3, 15.9, 11, 174.9);

-- --------------------------------------------------------

--
-- Table structure for table `promotion`
--

CREATE TABLE `promotion` (
  `id` int(11) NOT NULL,
  `discountRate` double NOT NULL,
  `isValid` tinyint(1) NOT NULL DEFAULT 0,
  `code` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `promotion`
--

INSERT INTO `promotion` (`id`, `discountRate`, `isValid`, `code`) VALUES
(1, 0.5, 1, 'ILOVEPIZZA'),
(2, 0.15, 1, 'PizzaGang'),
(3, 1, 0, 'FREEPIZZAWOW'),
(4, 0.75, 1, 'WOWVERYNICE');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `firstName` text DEFAULT NULL,
  `lastName` text DEFAULT NULL,
  `memberType` text NOT NULL DEFAULT 'member',
  `pizzaPoints` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `firstName`, `lastName`, `memberType`, `pizzaPoints`) VALUES
(1, 'admin1', '5e5ce329fcb56042d6e6ba22a80024e99e395dae96fc15148d695bce6344330ab049b6292b4fd616a0f47bfa65cf83476ddd75e6d4f7399d04275bd644541008', NULL, NULL, 'admin', 0),
(2, 'admin2', '5e5ce329fcb56042d6e6ba22a80024e99e395dae96fc15148d695bce6344330ab049b6292b4fd616a0f47bfa65cf83476ddd75e6d4f7399d04275bd644541008', NULL, NULL, 'admin', 0),
(3, 'hungjsong', '6cfa8706e6d3d585c1c914125b9d4687fa3047f287935c1b65e410a1138bb7554bcc2d7e30154173970fc17f3b77563171d9ef34090eea7063dbb759f522565f', 'Hung', 'Song', 'member', 18);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menuitem`
--
ALTER TABLE `menuitem`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_orderCustomer` (`customerID`),
  ADD KEY `FK_orderPromotion` (`promotionID`);

--
-- Indexes for table `order_menuitem`
--
ALTER TABLE `order_menuitem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_orderMenu` (`orderID`),
  ADD KEY `FK_orderMenuItem` (`menuItemID`);

--
-- Indexes for table `promotion`
--
ALTER TABLE `promotion`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `menuitem`
--
ALTER TABLE `menuitem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `order`
--
ALTER TABLE `order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_menuitem`
--
ALTER TABLE `order_menuitem`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `promotion`
--
ALTER TABLE `promotion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `FK_orderCustomer` FOREIGN KEY (`customerID`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `FK_orderPromotion` FOREIGN KEY (`promotionID`) REFERENCES `promotion` (`id`);

--
-- Constraints for table `order_menuitem`
--
ALTER TABLE `order_menuitem`
  ADD CONSTRAINT `FK_orderMenu` FOREIGN KEY (`orderID`) REFERENCES `order` (`id`),
  ADD CONSTRAINT `FK_orderMenuItem` FOREIGN KEY (`menuItemID`) REFERENCES `menuitem` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
