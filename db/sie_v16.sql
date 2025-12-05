-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2025 at 03:24 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sie`
--

-- --------------------------------------------------------

--
-- Table structure for table `attends`
--

CREATE TABLE `attends` (
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `collection_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attends`
--

INSERT INTO `attends` (`user_id`, `event_id`, `collection_id`) VALUES
(3, 2, NULL),
(1, 4, 1),
(2, 1, 2),
(1, 1, 3),
(1, 7, 5),
(3, 4, 6),
(5, 6, 7);

-- --------------------------------------------------------

--
-- Table structure for table `collection`
--

CREATE TABLE `collection` (
  `collection_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `Theme` text DEFAULT NULL,
  `image_id` int(11) DEFAULT 12,
  `name` varchar(100) NOT NULL,
  `starting_date` date DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collection`
--

INSERT INTO `collection` (`collection_id`, `user_id`, `Theme`, `image_id`, `name`, `starting_date`, `description`) VALUES
(1, 1, 'Pokemon Trading Cards', 1, 'Pokémon Cards', '2025-10-03', 'Pokémon cards from my childhood, rediscovered at home.'),
(2, 2, 'Pokémon Trading Cards', 4, 'Pokemon Vintage Cards', '2025-03-27', 'Main Pokémon TCG collection with vintage and modern cards.'),
(3, 1, 'Music', 2, 'Vinyl Collection', '2021-07-06', NULL),
(4, 4, 'Stamps', 9, 'Stamps Collections', '2023-09-30', 'Collection of illustrated stamps representing various iconic locations and landmarks. The collection brings together different landscapes, buildings, and cultural highlights.'),
(5, 1, 'Drinking Water', 14, 'Water Bottles', '2025-11-30', 'Bottles that help me stay hydrated!'),
(6, 3, 'Pokemon', 19, '1st Edition Machamp', NULL, NULL),
(7, 5, 'Vinyl ', 2, 'Vinyl Collections', '2021-12-01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `collection_tags`
--

CREATE TABLE `collection_tags` (
  `collection_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `collection_tags`
--

INSERT INTO `collection_tags` (`collection_id`, `tag_id`) VALUES
(1, 2),
(1, 3),
(5, 1),
(6, 2),
(7, 5);

-- --------------------------------------------------------

--
-- Table structure for table `contains`
--

CREATE TABLE `contains` (
  `collection_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contains`
--

INSERT INTO `contains` (`collection_id`, `item_id`) VALUES
(1, 1),
(2, 2),
(5, 5);

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image_id` int(11) DEFAULT 13,
  `name` varchar(100) NOT NULL,
  `date` date DEFAULT NULL,
  `theme` varchar(100) DEFAULT NULL,
  `place` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `teaser_url` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`event_id`, `user_id`, `image_id`, `name`, `date`, `theme`, `place`, `description`, `teaser_url`) VALUES
(1, 2, 5, 'Comic Con Portugal', '2025-10-03', 'Pop Culture', 'EXPONOR – Porto', 'The biggest pop culture event in Portugal.\r\n2025 edition', 'https://www.youtube.com/watch?v=6mw8rvBWbYE'),
(2, 1, 6, 'Report Gent', '2025-12-04', 'Studying', 'Gent - De Krook', 'Studying in Gnet', 'https://www.youtube.com/watch?v=r6ehZUsjLJ4'),
(3, 1, 16, 'Feup week', '2025-12-25', 'feup', 'feup', 'feup', NULL),
(4, 3, 17, 'Iberanime 2025', '2025-10-11', 'Anime', 'EXPONOR - Porto', 'The **Iberanime Porto** is one of the biggest Japanese culture events in Portugal, bringing together anime, manga, cosplay, gaming, and themed activities all in one place. It takes place annually at Exponor and attracts fans from across the region. Come visit us!\r\n', 'https://youtu.be/32r5ZUGvmu0?si=3fukM8yJNt3niLxg'),
(5, 1, 18, 'AquaCollect Porto – Exposição de Garrafas de Água', '2026-08-29', 'Bottles', 'Alfandega do Porto', 'AquaCollect Porto is an event dedicated to water-bottle collectors, showcasing limited editions, historical designs, and themed collections. It also features a trading area, short talks, and interactive spaces where participants can share their pieces and stories: a perfect gathering for anyone who enjoys collecting and design.', NULL),
(6, 5, 21, 'Porto Vinyl Market', '2026-01-03', 'Music', 'Maus hábitos, Porto', 'From records to CDs, cassettes, books, and magazines about music and musical culture, the Porto Vinyl Market is returning to Maus Hábitos. This fair of records, books, and musical memorabilia will feature several shops, dealers, and distributors from Porto and the surrounding area.', NULL),
(7, 1, 25, 'Aniversário Surpresa da Rita', '2026-05-29', 'Birthday', 'Casa da Rita', 'A birthday party for my dear friend Rita', 'https://www.youtube.com/watch?v=_fw8AUMed7A');

-- --------------------------------------------------------

--
-- Table structure for table `favourite`
--

CREATE TABLE `favourite` (
  `user_id` int(11) NOT NULL,
  `collection_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

CREATE TABLE `friends` (
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `friends`
--

INSERT INTO `friends` (`user_id`, `friend_id`) VALUES
(1, 2),
(1, 4),
(2, 1),
(2, 4);

-- --------------------------------------------------------

--
-- Table structure for table `image`
--

CREATE TABLE `image` (
  `image_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `image`
--

INSERT INTO `image` (`image_id`, `url`) VALUES
(1, 'images/pokemon-pikachu.png'),
(2, 'images/vinil.png'),
(3, 'images/rafael.png'),
(4, 'images/pokémon_logo.png'),
(5, 'images/comiccon.png'),
(6, 'images/event_1764462193_692b8e71c26eb.png'),
(7, 'images/anaritalopes.jpg'),
(8, 'images/userimage.png'),
(9, 'images/stamps.png'),
(10, 'images/CharizardV.png'),
(11, 'images/placeholderuserpicture.png'),
(12, 'images/placeholdercollectionpicture.png'),
(13, 'images/placeholdereventpicture.png\r\n'),
(14, 'images/tupperware.jpg'),
(15, 'images/stanley.jpg'),
(16, 'images/feup.jpg'),
(17, 'images/iberanime.png'),
(18, 'images/agua.jpg'),
(19, 'images/1st_Edition_Machamp.png'),
(20, 'images/davidramos.jpg'),
(21, 'images/portovinil.png'),
(22, 'images/1.png'),
(23, 'images/2.png'),
(24, 'images/3.png'),
(25, 'images/event_1764943929_6932e8394a19e.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `item`
--

CREATE TABLE `item` (
  `item_id` int(11) NOT NULL,
  `type_id` int(11) DEFAULT NULL,
  `image_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `price` float DEFAULT NULL,
  `importance` int(11) DEFAULT NULL,
  `acc_date` date DEFAULT NULL,
  `acc_place` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `registration_date` date DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item`
--

INSERT INTO `item` (`item_id`, `type_id`, `image_id`, `name`, `price`, `importance`, `acc_date`, `acc_place`, `description`, `registration_date`) VALUES
(1, 1, 10, 'Champion\'s Path Charizard V (PSA 10)\r\n', 950, 10, '2025-10-03', 'Comic Con 2025', 'A rare and highly graded Charizard card from the Champion\'s Path set. This card was one of the highlights of my 2025 Comic Con haul. It holds sentimental value and is a key item in my collection.', '2025-11-28'),
(2, 1, 19, '1st Edition Machamp', 2000, 10, '2025-10-03', 'Comic Con 2023', 'First Edition Machamp holographic card. One of the most iconic vintage Pokémon cards, highly valued by collectors. Excellent condition with clean borders and strong holographic shine.', '2025-11-29'),
(3, 1, 22, 'One Piece Card Game', 999, 10, '2025-11-30', 'Iberanime 2025', NULL, '2025-12-04'),
(4, 1, 24, 'Charizard 1st Edition Holo', 651.17, 10, '2023-11-26', 'Iberanime 2023', NULL, '2025-12-04'),
(5, 2, 15, 'Stanley cup', 10, 6, '2025-10-22', 'Aamzon', 'It is pink and I really like it', '2025-12-01'),
(6, NULL, 23, 'Magic the Gathering Marvel\'s', 662.97, 10, '2025-04-05', 'Amazon', NULL, '2025-12-04');

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `user_id` int(11) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rating`
--

INSERT INTO `rating` (`user_id`, `collection_id`, `event_id`, `rating`) VALUES
(1, 6, 4, 5);

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `tag_id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`tag_id`, `name`) VALUES
(1, 'Bottles'),
(2, 'Pokemon'),
(3, 'Cards'),
(4, 'Anime'),
(5, 'Music');

-- --------------------------------------------------------

--
-- Table structure for table `type`
--

CREATE TABLE `type` (
  `type_id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `type`
--

INSERT INTO `type` (`type_id`, `name`) VALUES
(1, 'Card'),
(2, 'Bottle');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `image_id` int(11) DEFAULT 11,
  `username` varchar(100) NOT NULL,
  `dob` date DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `country` varchar(80) DEFAULT NULL,
  `password` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `image_id`, `username`, `dob`, `email`, `country`, `password`) VALUES
(1, 8, 'Susana_Andrade123', '2003-04-02', 'susana_andrade123@gmail.com', 'Portugal', 'susaninha'),
(2, 3, 'Rafael_Leao17', '1998-05-10', 'rafaelleao.17na.selecao@hotmail.com', 'Portugal', 'opapidelas'),
(3, 11, 'Boneco_Russo', NULL, 'bonecorusso@hotmail.com', NULL, 'nutecracker'),
(4, 7, 'Ana_Rita_Lopes', NULL, 'anaritalopes@gmail.com', NULL, 'rita'),
(5, 20, 'David_Ramos', '2003-03-17', 'david.ramos170303@gmail.com', 'Portugal', 'davidramos'),
(6, 11, '', NULL, NULL, NULL, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attends`
--
ALTER TABLE `attends`
  ADD PRIMARY KEY (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `takes_collection` (`collection_id`);

--
-- Indexes for table `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`collection_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `image_id` (`image_id`);

--
-- Indexes for table `collection_tags`
--
ALTER TABLE `collection_tags`
  ADD PRIMARY KEY (`collection_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `contains`
--
ALTER TABLE `contains`
  ADD KEY `collection` (`collection_id`),
  ADD KEY `item` (`item_id`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `image_id` (`image_id`),
  ADD KEY `creator` (`user_id`);

--
-- Indexes for table `favourite`
--
ALTER TABLE `favourite`
  ADD PRIMARY KEY (`user_id`,`collection_id`),
  ADD KEY `collection_id` (`collection_id`);

--
-- Indexes for table `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`user_id`,`friend_id`),
  ADD KEY `friend_id` (`friend_id`);

--
-- Indexes for table `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`image_id`);

--
-- Indexes for table `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `image_id` (`image_id`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`user_id`,`collection_id`,`event_id`),
  ADD KEY `collection_id` (`collection_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`tag_id`);

--
-- Indexes for table `type`
--
ALTER TABLE `type`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `image_id` (`image_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `collection`
--
ALTER TABLE `collection`
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `image`
--
ALTER TABLE `image`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `type`
--
ALTER TABLE `type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attends`
--
ALTER TABLE `attends`
  ADD CONSTRAINT `attends_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attends_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `takes_collection` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`);

--
-- Constraints for table `collection`
--
ALTER TABLE `collection`
  ADD CONSTRAINT `collection_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `image` (`image_id`) ON DELETE SET NULL;

--
-- Constraints for table `collection_tags`
--
ALTER TABLE `collection_tags`
  ADD CONSTRAINT `collection_tags_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE;

--
-- Constraints for table `contains`
--
ALTER TABLE `contains`
  ADD CONSTRAINT `collection` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`),
  ADD CONSTRAINT `item` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`);

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `creator` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`image_id`) REFERENCES `image` (`image_id`) ON DELETE SET NULL;

--
-- Constraints for table `favourite`
--
ALTER TABLE `favourite`
  ADD CONSTRAINT `favourite_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favourite_ibfk_2` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`) ON DELETE CASCADE;

--
-- Constraints for table `friends`
--
ALTER TABLE `friends`
  ADD CONSTRAINT `friends_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friends_ibfk_2` FOREIGN KEY (`friend_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `item_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `type` (`type_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `item_ibfk_3` FOREIGN KEY (`image_id`) REFERENCES `image` (`image_id`) ON DELETE SET NULL;

--
-- Constraints for table `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rating_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`image_id`) REFERENCES `image` (`image_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
