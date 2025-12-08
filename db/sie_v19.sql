-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2025 at 01:00 AM
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
(1, 9, 3),
(1, 7, 5),
(1, 8, 5),
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
(2, 2, 'Pokémon Vintage Cards', 34, 'Pokemon Vintage Cards', '2025-03-27', 'Main Pokémon TCG collection with vintage and modern cards.'),
(3, 1, 'Music', 2, 'Vinyl Collection', '2021-07-06', NULL),
(4, 4, 'Stamps', 9, 'Stamps Collections', '2023-09-30', 'Collection of illustrated stamps representing various iconic locations and landmarks. The collection brings together different landscapes, buildings, and cultural highlights.'),
(5, 1, 'Drinking Water', 14, 'Water Bottles', '2025-11-30', 'Bottles that help me stay hydrated!'),
(6, 3, 'Pokemon', 19, '1st Edition Machamp', NULL, NULL),
(7, 5, 'Vinyl ', 2, 'Vinyl Collections', '2021-12-01', NULL),
(8, 5, 'Marvel', 23, 'Marvel Collections', '2022-05-29', 'A curated collection of Marvel memorabilia featuring iconic characters, comic-inspired artwork and exclusive items from the Marvel universe. It brings together pieces that celebrate superheroes, storylines, and moments that have shaped generations of fans.'),
(9, 3, 'Pokemon Cards', 4, 'Pokemon Cards', '2016-12-03', 'A diverse collection of Pokémon cards featuring different generations, rarities, and artwork styles. It brings together iconic creatures, special editions and unique finds gathered over time.'),
(10, 2, 'Football Ball Collection', 26, 'Football Ball Collection', '2016-06-25', 'A collection of footballs featuring different designs, sizes, and eras, highlighting the evolution of the sport through its most iconic equipment.');

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
(5, 5),
(8, 6),
(9, 3),
(10, 7),
(10, 8),
(10, 9),
(2, 10),
(2, 11),
(2, 12),
(2, 13),
(3, 14);

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
(7, 1, 25, 'Aniversário Surpresa da Rita', '2026-05-29', 'Birthday', 'Casa da Rita', 'A birthday party for my dear friend Rita', 'https://www.youtube.com/watch?v=_fw8AUMed7A'),
(8, 1, 40, 'SIC Fam Feud', '2025-12-07', 'Contest', 'Estúdios Sic', 'César Mourão na apresentação garante a diversão', 'https://www.youtube.com/watch?v=nFbu4CbGy8I'),
(9, 1, 41, 'Véspera de Feriado 8 de julho', '2025-12-07', 'Religious', 'Sé do Porto', 'Seeenhooor, vós tendes palaaavras', 'https://www.youtube.com/watch?v=uiPej4lQzZo&list=RDuiPej4lQzZo&start_radio=1');

-- --------------------------------------------------------

--
-- Table structure for table `favourite`
--

CREATE TABLE `favourite` (
  `user_id` int(11) NOT NULL,
  `collection_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favourite`
--

INSERT INTO `favourite` (`user_id`, `collection_id`) VALUES
(1, 1),
(1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

CREATE TABLE `friends` (
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  `start_date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `friends`
--

INSERT INTO `friends` (`user_id`, `friend_id`, `start_date`) VALUES
(1, 2, '2025-12-07'),
(1, 4, '2025-12-07'),
(2, 1, '2025-12-07'),
(2, 4, '2025-12-07');

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
(25, 'images/event_1764943929_6932e8394a19e.jpg'),
(26, 'images/bolascolecao.png'),
(27, 'images/telstar.png'),
(28, 'images/adidasjabulani.png'),
(29, 'images/nikeordem.png'),
(30, 'images/charmander.png'),
(31, 'images/eevee.png'),
(32, 'images/pika.png'),
(33, 'images/squirtle.png'),
(34, 'images/pokemon.png'),
(35, 'uploads/profile_1_1765136583.jpg'),
(36, 'uploads/profile_1_1765136604.png'),
(37, 'uploads/profile_1_1765138260.jpg'),
(38, 'uploads/profile_1_1765138345.png'),
(39, 'images/Slow_J_-_Afro_Fado.jpeg'),
(40, 'images/event_1765148491_6936074b1c499.jpeg'),
(41, 'images/event_1765149047_69360977c736a.jpg');

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
(6, 3, 23, 'Magic the Gathering Marvel\'s', 662.97, 10, '2025-12-04', 'Amazon', NULL, '2025-12-04'),
(7, 4, 27, 'Adidas Telstar 2018', 80, 8, '2020-12-27', 'Adidas', 'This commemorative ball features the iconic design of the original 1970s model, reinterpreted for the 2018 tournament.', '2021-01-08'),
(8, 4, 28, 'Adidas Jabulani 2010', 60, 7, '2025-12-01', 'eBay', 'The Adidas Jabulani, the official match ball of the 2010 FIFA World Cup, is known for its distinctive panel design and vibrant graphics. Celebrated for its iconic look and debated for its unique flight behavior, it remains one of the most recognizable and collectible balls in modern football history.', '2025-12-05'),
(9, 4, 29, 'Nike Ordem', 30, 4, '2025-08-02', 'Nike', 'The Nike Ordem is a premium match ball used across top leagues and international competitions. Designed for precision, control, and visibility, it features advanced panel construction and Aerow Trac technology, making it a standout piece for collectors who value performance-driven football equipment.', '2025-12-05'),
(10, 1, 32, 'Pikachu', 1, 1, '2017-12-03', 'eBay', 'A classic and widely printed card featuring the franchise’s mascot. Simple, iconic, and easily recognizable in any collection.', '2025-12-01'),
(11, 1, 30, 'Charmander', 0.5, 1, '2017-12-03', 'eBay', 'A common and popular starter Pokémon card, often included in beginner sets and known for its nostalgic appeal.', '2025-12-01'),
(12, 1, 31, 'Eevee', 1, 1, '2017-12-03', 'eBay', 'A beloved and versatile Pokémon card available in many sets, valued for its cute artwork and collectible evolution line.', '2025-12-01'),
(13, 1, 33, 'Squirtle', 1, 1, '2017-12-03', 'eBay', 'A simple and iconic starter card frequently found in modern reprints and beginner-friendly expansions.', '2025-12-01'),
(14, 5, 39, 'Afro Fado', 25, 7, '2025-03-17', 'Tubitec', 'Ainda \'tás comigo? Se eu \'tou longe, se eu me afasto\r\nQuando tiro o pé do fogo, quantos tropas eu arrasto?\r\nLá p\'a cima vais ver como é que a vista traz paz\r\nDepois diz-me, vais querer passar a vida a baixares-te?', '2025-12-07');

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
(2, 'Bottle'),
(3, 'Marvel'),
(4, 'Football'),
(5, 'Disc');

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
  `password` text NOT NULL,
  `notify_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `theme` enum('light','dark') NOT NULL DEFAULT 'light'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `image_id`, `username`, `dob`, `email`, `country`, `password`, `notify_enabled`, `theme`) VALUES
(1, 38, 'Susana_Andrade123', '2003-02-04', 'susana_andrade123@gmail.com', 'Portugal', 'susaninha', 1, 'light'),
(2, 3, 'Rafael_Leao17', '1998-05-10', 'rafaelleao.17na.selecao@hotmail.com', 'Portugal', 'opapidelas', 1, 'light'),
(3, 11, 'Boneco_Russo', NULL, 'bonecorusso@hotmail.com', NULL, 'nutecracker', 1, 'light'),
(4, 7, 'Ana_Rita_Lopes', NULL, 'anaritalopes@gmail.com', NULL, 'rita', 1, 'light'),
(5, 20, 'David_Ramos', '2003-03-17', 'david.ramos170303@gmail.com', 'Portugal', 'davidramos', 1, 'light'),
(6, 11, '', NULL, NULL, NULL, '', 1, 'light');

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
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `image`
--
ALTER TABLE `image`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `type`
--
ALTER TABLE `type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
