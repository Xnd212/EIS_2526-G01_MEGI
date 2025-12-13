-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 13-Dez-2025 às 21:02
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `sie`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `attends`
--

CREATE TABLE `attends` (
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `collection_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `attends`
--

INSERT INTO `attends` (`user_id`, `event_id`, `collection_id`) VALUES
(1, 4, 1),
(2, 1, 2),
(1, 1, 3),
(1, 5, 5),
(1, 8, 5),
(3, 4, 6),
(5, 6, 7),
(9, 10, 18),
(9, 11, 18),
(10, 10, 19),
(10, 12, 19),
(8, 10, 21);

-- --------------------------------------------------------

--
-- Estrutura da tabela `collection`
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
-- Extraindo dados da tabela `collection`
--

INSERT INTO `collection` (`collection_id`, `user_id`, `Theme`, `image_id`, `name`, `starting_date`, `description`) VALUES
(1, 1, 'Pokemon Trading Cards', 1, 'Pokémon Cards', '2025-10-03', 'Pokémon cards from my childhood, rediscovered at home.'),
(2, 2, 'Pokémon Vintage Cards', 34, 'Pokemon Vintage Cards', '2025-03-27', 'Main Pokémon TCG collection with vintage and modern cards.'),
(3, 1, 'Music', 2, 'Vinyl Collection', '2021-07-06', NULL),
(4, 4, 'Stamps', 9, 'Stamps Collections', '2023-09-30', 'Collection of illustrated stamps representing various iconic locations and landmarks. The collection brings together different landscapes, buildings, and cultural highlights.'),
(5, 1, 'Drinking Water', 14, 'Water Bottles', '2025-11-30', 'Bottles that help me stay hydrated!'),
(6, 3, 'Pokemon', 12, 'Pokemon', NULL, NULL),
(7, 5, 'Vinyl ', 2, 'Vinyl Collections', '2021-12-01', NULL),
(8, 5, 'Marvel', 23, 'Marvel Collections', '2022-05-29', 'A curated collection of Marvel memorabilia featuring iconic characters, comic-inspired artwork and exclusive items from the Marvel universe. It brings together pieces that celebrate superheroes, storylines, and moments that have shaped generations of fans.'),
(9, 3, 'Pokemon Cards', 4, 'Pokemon Cards', '2016-12-03', 'A diverse collection of Pokémon cards featuring different generations, rarities, and artwork styles. It brings together iconic creatures, special editions and unique finds gathered over time.'),
(10, 2, 'Football Ball Collection', 26, 'Football Ball Collection', '2016-06-25', 'A collection of footballs featuring different designs, sizes, and eras, highlighting the evolution of the sport through its most iconic equipment.'),
(17, 8, 'Funko Pop', 12, 'Funko Pop Heroes', '2024-02-10', 'Funko Pop figures focused on superhero characters.'),
(18, 9, 'LEGO Sets', 12, 'LEGO Sets', '2020-11-05', 'LEGO sets including Technic and Star Wars themes.'),
(19, 10, 'Manga', 12, 'Manga Shelf', '2019-06-01', 'Personal manga collection with different genres.'),
(20, 8, 'Posters', 12, 'Concert Posters', '2022-04-22', 'Posters collected from concerts and music festivals.'),
(21, 9, 'Trading Cards', 12, 'Trade Binder', '2025-01-15', 'Duplicate cards used mainly for trading at events.'),
(22, 2, 'Football Memorabilia', 12, 'Matchday Keepsakes', '2021-08-01', 'Tickets, wristbands, small souvenirs from matches and trips.'),
(23, 2, 'Football Memorabilia', 12, 'Boots & Gear', '2020-01-10', 'Boots, shin guards, and training accessories.'),
(24, 17, 'Football Memorabilia', 12, 'Portugal Tournament Box', '2004-06-01', 'National team souvenirs across tournaments.'),
(25, 17, 'Football Memorabilia', 12, 'Signed Collectibles', '2008-09-01', 'Autographs, signed shirts, and limited items.'),
(26, 18, 'Football Memorabilia', 12, 'Defender Essentials', '2017-02-15', 'Shin guards, captain armbands, and match accessories.'),
(27, 18, 'Football Memorabilia', 12, 'Away Days', '2018-09-01', 'Scarves, programs, and travel souvenirs.'),
(28, 19, 'Football Memorabilia', 12, 'Midfield Moments', '2019-07-01', 'Match balls, assist highlights, and training tokens.'),
(29, 19, 'Football Memorabilia', 12, 'Stadium Collection', '2020-10-10', 'Programs, badges, and small stadium souvenirs.'),
(30, 20, 'Football Memorabilia', 12, 'Goalkeeper Wall', '2019-05-01', 'Gloves, patches, and keeper-specific memorabilia.'),
(31, 20, 'Football Memorabilia', 12, 'Clean Sheet Shelf', '2021-08-15', 'Items linked to clean sheets and keeper awards.'),
(32, 21, 'Football Memorabilia', 12, 'First Team Debut', '2022-09-01', 'Debut season keepsakes and early milestones.'),
(33, 21, 'Football Memorabilia', 12, 'Fan Corner', '2023-01-20', 'Fan gifts, letters, and custom cards.'),
(34, 22, 'Football Memorabilia', 12, 'Long Range Memories', '2016-08-01', 'Items connected to iconic long-range goals.'),
(35, 22, 'Football Memorabilia', 12, 'Match Balls & Replicas', '2015-11-01', 'Special balls and collectible replicas.');

-- --------------------------------------------------------

--
-- Estrutura da tabela `collection_tags`
--

CREATE TABLE `collection_tags` (
  `collection_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `collection_tags`
--

INSERT INTO `collection_tags` (`collection_id`, `tag_id`) VALUES
(1, 2),
(1, 3),
(5, 1),
(6, 2),
(7, 5),
(17, 6),
(17, 10),
(18, 7),
(18, 10),
(19, 8),
(21, 9);

-- --------------------------------------------------------

--
-- Estrutura da tabela `contains`
--

CREATE TABLE `contains` (
  `collection_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `contains`
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
(3, 14),
(17, 15),
(18, 16),
(19, 17),
(20, 18),
(22, 19),
(22, 20),
(22, 21),
(23, 22),
(23, 23),
(23, 24),
(24, 25),
(24, 26),
(24, 27),
(25, 28),
(25, 29),
(25, 30),
(26, 31),
(26, 32),
(26, 33),
(27, 34),
(27, 35),
(27, 36),
(28, 37),
(28, 38),
(28, 39),
(29, 40),
(29, 41),
(29, 42),
(30, 43),
(30, 44),
(30, 45),
(31, 46),
(31, 47),
(31, 48),
(32, 49),
(32, 50),
(32, 51),
(33, 52),
(33, 53),
(33, 54),
(34, 55),
(34, 56),
(34, 57),
(35, 58),
(35, 59),
(35, 60);

-- --------------------------------------------------------

--
-- Estrutura da tabela `event`
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
-- Extraindo dados da tabela `event`
--

INSERT INTO `event` (`event_id`, `user_id`, `image_id`, `name`, `date`, `theme`, `place`, `description`, `teaser_url`) VALUES
(1, 2, 5, 'Comic Con Portugal', '2025-10-03', 'Pop Culture', 'EXPONOR – Porto', 'The biggest pop culture event in Portugal.\r\n2025 edition', 'https://www.youtube.com/watch?v=6mw8rvBWbYE'),
(4, 3, 17, 'Iberanime 2025', '2025-10-11', 'Anime', 'EXPONOR - Porto', 'The **Iberanime Porto** is one of the biggest Japanese culture events in Portugal, bringing together anime, manga, cosplay, gaming, and themed activities all in one place. It takes place annually at Exponor and attracts fans from across the region. Come visit us!\r\n', 'https://youtu.be/32r5ZUGvmu0?si=3fukM8yJNt3niLxg'),
(5, 1, 18, 'AquaCollect Porto – Exposição de Garrafas de Água', '2026-08-29', 'Bottles', 'Alfandega do Porto', 'AquaCollect Porto is an event dedicated to water-bottle collectors, showcasing limited editions, historical designs, and themed collections. It also features a trading area, short talks, and interactive spaces where participants can share their pieces and stories: a perfect gathering for anyone who enjoys collecting and design.', NULL),
(6, 5, 21, 'Porto Vinyl Market', '2026-01-03', 'Music', 'Maus hábitos, Porto', 'From records to CDs, cassettes, books, and magazines about music and musical culture, the Porto Vinyl Market is returning to Maus Hábitos. This fair of records, books, and musical memorabilia will feature several shops, dealers, and distributors from Porto and the surrounding area.', NULL),
(8, 1, 40, 'SIC Fam Feud', '2025-12-07', 'Contest', 'Estúdios Sic', 'César Mourão na apresentação garante a diversão', 'https://www.youtube.com/watch?v=nFbu4CbGy8I'),
(10, 8, 13, 'Trall-E Swap Meet Porto', '2026-02-14', 'Trading', 'Mercado do Bolhão – Porto', 'Event focused on trading collectibles and meeting other collectors.', NULL),
(11, 9, 13, 'LEGO Display Day', '2026-03-09', 'LEGO', 'FEUP – Porto', 'Informal exhibition of LEGO sets and custom builds.', NULL),
(12, 10, 13, 'Manga & Coffee Meetup', '2026-01-25', 'Manga', 'Porto City Center', 'Casual meetup to discuss manga and exchange volumes.', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `favourite`
--

CREATE TABLE `favourite` (
  `user_id` int(11) NOT NULL,
  `collection_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `favourite`
--

INSERT INTO `favourite` (`user_id`, `collection_id`) VALUES
(1, 1),
(1, 3),
(8, 17),
(9, 18),
(10, 19);

-- --------------------------------------------------------

--
-- Estrutura da tabela `friends`
--

CREATE TABLE `friends` (
  `user_id` int(11) NOT NULL,
  `friend_id` int(11) NOT NULL,
  `start_date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `friends`
--

INSERT INTO `friends` (`user_id`, `friend_id`, `start_date`) VALUES
(1, 2, '2025-12-07'),
(1, 4, '2025-12-07'),
(1, 8, '2025-12-13'),
(2, 1, '2025-12-07'),
(2, 4, '2025-12-07'),
(2, 17, '2025-12-13'),
(2, 18, '2025-12-13'),
(2, 19, '2025-12-13'),
(2, 20, '2025-12-13'),
(2, 21, '2025-12-13'),
(2, 22, '2025-12-13'),
(4, 10, '2025-12-13'),
(8, 1, '2025-12-13'),
(8, 2, '2025-12-13'),
(9, 2, '2025-12-13'),
(10, 2, '2025-12-13'),
(10, 4, '2025-12-13'),
(17, 2, '2025-12-13'),
(18, 2, '2025-12-13'),
(19, 2, '2025-12-13'),
(20, 2, '2025-12-13'),
(21, 2, '2025-12-13'),
(22, 2, '2025-12-13');

-- --------------------------------------------------------

--
-- Estrutura da tabela `image`
--

CREATE TABLE `image` (
  `image_id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `image`
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
(41, 'images/event_1765149047_69360977c736a.jpg'),
(42, 'images/placeholderitempicture.png'),
(49, 'images/cris.png'),
(50, 'images/rubendias.png'),
(51, 'images/vitinha.png'),
(52, 'images/diogocosta.png'),
(53, 'images/joaoneves.png'),
(54, 'images/rubenneves.png');

-- --------------------------------------------------------

--
-- Estrutura da tabela `item`
--

CREATE TABLE `item` (
  `item_id` int(11) NOT NULL,
  `type_id` int(11) DEFAULT NULL,
  `image_id` int(11) DEFAULT 42,
  `name` varchar(100) NOT NULL,
  `price` float DEFAULT NULL,
  `importance` int(11) DEFAULT NULL,
  `acc_date` date DEFAULT NULL,
  `acc_place` varchar(120) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `registration_date` date DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `item`
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
(14, 5, 39, 'Afro Fado', 25, 7, '2025-03-17', 'Tubitec', 'Ainda \'tás comigo? Se eu \'tou longe, se eu me afasto\r\nQuando tiro o pé do fogo, quantos tropas eu arrasto?\r\nLá p\'a cima vais ver como é que a vista traz paz\r\nDepois diz-me, vais querer passar a vida a baixares-te?', '2025-12-07'),
(15, 6, 42, 'Funko Pop Spider-Man (Special Edition)', 35, 7, '2024-02-12', 'Fnac', 'Special edition Spider-Man Funko Pop in good condition.', '2025-12-13'),
(16, 7, 42, 'LEGO Technic McLaren F1', 180, 9, '2023-12-18', 'LEGO Store', 'Complete LEGO Technic set with manual.', '2025-12-13'),
(17, 8, 42, 'Jujutsu Kaisen Volume 1', 9.9, 5, '2021-05-03', 'Bookstore', 'First volume of the Jujutsu Kaisen manga series.', '2025-12-13'),
(18, 9, 42, 'Music Festival Poster (A2)', 12, 4, '2019-07-10', 'Merch Stand', 'A2-sized poster from a summer music festival.', '2025-12-13'),
(19, 4, 42, 'Ticket Stub (UCL Night)', 15, 6, '2022-11-02', 'Stadium', 'Ticket from a Champions League match.', '2025-12-13'),
(20, 4, 42, 'Derby Wristband', 5, 5, '2023-02-05', 'Stadium', 'Access wristband from a derby match.', '2025-12-13'),
(21, 4, 42, 'Matchday Program', 8, 4, '2023-03-12', 'Stadium', 'Official program kept as souvenir.', '2025-12-13'),
(22, 4, 42, 'Training Boots Pair', 60, 6, '2021-09-01', 'Training Center', 'Boots used during training sessions.', '2025-12-13'),
(23, 4, 42, 'Match Shin Guards', 40, 7, '2022-10-10', 'Locker Room', 'Match-used shin guards.', '2025-12-13'),
(24, 4, 42, 'Captain Armband (Replica)', 20, 5, '2022-05-22', 'Club Store', 'Replica armband for display.', '2025-12-13'),
(25, 4, 42, 'Portugal Scarf (Tournament)', 18, 6, '2012-06-15', 'Fan Shop', 'Tournament scarf in great condition.', '2025-12-13'),
(26, 4, 42, 'Flag Pin Set', 6, 4, '2016-06-10', 'Fan Zone', 'Small pin set from tournament fan area.', '2025-12-13'),
(27, 4, 42, 'Commemorative Program', 12, 6, '2016-07-10', 'Final Venue', 'Program from a major final day.', '2025-12-13'),
(28, 4, 42, 'Signed Jersey (Framed)', 250, 10, '2018-08-01', 'Club Event', 'Signed jersey stored in a frame.', '2025-12-13'),
(29, 4, 42, 'Signed Photo Card', 45, 8, '2017-05-20', 'Fan Event', 'Autographed photo card.', '2025-12-13'),
(30, 4, 42, 'Signed Armband', 140, 9, '2016-07-10', 'Final Venue', 'Signed armband from an international final.', '2025-12-13'),
(31, 4, 42, 'Defender Gloves (Winter)', 12, 4, '2021-12-01', 'Training Center', 'Warm-up gloves for winter sessions.', '2025-12-13'),
(32, 4, 42, 'Shin Guard Set (Match)', 35, 7, '2022-04-10', 'Locker Room', 'Match-used shin guard set.', '2025-12-13'),
(33, 4, 42, 'Captain Notes Card', 3, 4, '2023-03-12', 'Training Center', 'Small card with pre-match notes.', '2025-12-13'),
(34, 4, 42, 'Away Scarf', 15, 5, '2022-12-01', 'Stadium Shop', 'Scarf bought on an away trip.', '2025-12-13'),
(35, 4, 42, 'Stadium Badge', 4, 4, '2022-12-01', 'Stadium', 'Souvenir badge from a stadium visit.', '2025-12-13'),
(36, 4, 42, 'Travel Tag (Team Bus)', 2, 3, '2021-09-18', 'Away Trip', 'Small travel tag kept as memorabilia.', '2025-12-13'),
(37, 4, 42, 'Assist Match Ball (Replica)', 45, 6, '2023-01-15', 'Match', 'Replica ball from a match with a key assist.', '2025-12-13'),
(38, 4, 42, 'Heatmap Print (A4)', 10, 4, '2023-05-05', 'Online', 'Printed heatmap artwork.', '2025-12-13'),
(39, 4, 42, 'Training Bib', 8, 4, '2022-08-20', 'Training Center', 'Training bib saved as keepsake.', '2025-12-13'),
(40, 4, 42, 'Matchday Program (Away)', 8, 4, '2023-02-01', 'Stadium', 'Program from an away game.', '2025-12-13'),
(41, 4, 42, 'Seat Number Token', 1.5, 3, '2023-02-01', 'Stadium', 'Small seat token kept as souvenir.', '2025-12-13'),
(42, 4, 42, 'Club Sticker Pack', 3, 3, '2022-10-10', 'Fan Shop', 'Stickers collected across matches.', '2025-12-13'),
(43, 4, 42, 'Match Gloves Pair #1', 95, 9, '2022-09-18', 'Match', 'Match-used gloves.', '2025-12-13'),
(44, 4, 42, 'Training Gloves Pair #1', 35, 6, '2021-08-20', 'Training Center', 'Training gloves in good condition.', '2025-12-13'),
(45, 4, 42, 'Keeper Patch', 12, 5, '2023-04-02', 'Club Store', 'Patch from a keeper-themed release.', '2025-12-13'),
(46, 4, 42, 'Clean Sheet Patch', 12, 5, '2023-04-02', 'Club Store', 'Patch linked to a clean sheet match.', '2025-12-13'),
(47, 4, 42, 'Mini Award Plaque', 25, 6, '2023-06-01', 'Club Event', 'Small award plaque for display.', '2025-12-13'),
(48, 4, 42, 'Signed Match Sheet (Copy)', 18, 6, '2023-06-01', 'Club Event', 'Signed match sheet copy.', '2025-12-13'),
(49, 4, 42, 'Debut Match Ticket', 10, 6, '2022-10-01', 'Stadium', 'Ticket from first-team debut.', '2025-12-13'),
(50, 4, 42, 'First Goal Newspaper Cutout', 3, 4, '2023-02-12', 'Newspaper', 'Newspaper cutout stored in sleeve.', '2025-12-13'),
(51, 4, 42, 'Academy Medal (Replica)', 9, 5, '2021-06-01', 'Academy', 'Replica medal from youth competitions.', '2025-12-13'),
(52, 4, 42, 'Fan Letter Bundle', 0, 5, '2023-05-01', 'Mail', 'Letters from fans saved in a binder.', '2025-12-13'),
(53, 4, 42, 'Custom Fan Card', 2, 4, '2023-05-01', 'Meetup', 'Handmade fan card.', '2025-12-13'),
(54, 4, 42, 'Sticker Album Page', 1, 3, '2023-04-20', 'Meetup', 'Sticker album page with signatures.', '2025-12-13'),
(55, 4, 42, 'Goal Photo Print', 18, 6, '2018-03-10', 'Online', 'Photo print of a long-range goal moment.', '2025-12-13'),
(56, 4, 42, 'Signed Ball (Long Shot)', 160, 9, '2018-03-10', 'Match', 'Ball signed after a memorable long shot.', '2025-12-13'),
(57, 4, 42, 'Match Boots Display Tag', 4, 4, '2017-04-15', 'League Match', 'Small display tag from a boots set.', '2025-12-13'),
(58, 4, 42, 'Special Edition League Ball', 70, 7, '2017-08-20', 'Club Store', 'Limited design league ball.', '2025-12-13'),
(59, 4, 42, 'Cup Ball Replica', 45, 6, '2019-02-01', 'Collector Fair', 'Replica ball from a cup match.', '2025-12-13'),
(60, 4, 42, 'Training Ball', 25, 5, '2016-09-01', 'Training Center', 'Ball used during training sessions.', '2025-12-13');

-- --------------------------------------------------------

--
-- Estrutura da tabela `rating`
--

CREATE TABLE `rating` (
  `user_id` int(11) NOT NULL,
  `collection_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `rating`
--

INSERT INTO `rating` (`user_id`, `collection_id`, `event_id`, `rating`) VALUES
(1, 6, 4, 5),
(8, 17, 10, 5),
(9, 18, 11, 4),
(10, 19, 12, 5);

-- --------------------------------------------------------

--
-- Estrutura da tabela `tags`
--

CREATE TABLE `tags` (
  `tag_id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `tags`
--

INSERT INTO `tags` (`tag_id`, `name`) VALUES
(1, 'Bottles'),
(2, 'Pokemon'),
(3, 'Cards'),
(4, 'Anime'),
(5, 'Music'),
(6, 'Marvel'),
(7, 'LEGO'),
(8, 'Manga'),
(9, 'Trading'),
(10, 'Meetup');

-- --------------------------------------------------------

--
-- Estrutura da tabela `type`
--

CREATE TABLE `type` (
  `type_id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `type`
--

INSERT INTO `type` (`type_id`, `name`) VALUES
(1, 'Card'),
(2, 'Bottle'),
(3, 'Marvel'),
(4, 'Football'),
(5, 'Disc'),
(6, 'Figure'),
(7, 'LEGO'),
(8, 'Manga'),
(9, 'Poster');

-- --------------------------------------------------------

--
-- Estrutura da tabela `user`
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
-- Extraindo dados da tabela `user`
--

INSERT INTO `user` (`user_id`, `image_id`, `username`, `dob`, `email`, `country`, `password`) VALUES
(1, 38, 'Susana_Andrade123', '2003-02-04', 'susana_andrade123@gmail.com', 'Portugal', 'susaninha'),
(2, 3, 'Rafael_Leao17', '1998-05-10', 'rafaelleao.17na.selecao@hotmail.com', 'Portugal', 'opapidelas'),
(3, 11, 'Boneco_Russo', NULL, 'bonecorusso@hotmail.com', NULL, 'nutecracker'),
(4, 7, 'Ana_Rita_Lopes', NULL, 'anaritalopes@gmail.com', NULL, 'rita'),
(5, 20, 'David_Ramos', '2003-03-17', 'david.ramos170303@gmail.com', 'Portugal', 'davidramos'),
(8, 11, 'Marta_Sousa', '2002-09-14', 'marta.sousa@test.com', 'Portugal', 'marta123'),
(9, 11, 'Joao_Pereira', '1999-01-22', 'joao.pereira@test.com', 'Portugal', 'joao123'),
(10, 11, 'Ines_Costa', '2001-06-08', 'ines.costa@test.com', 'Portugal', 'ines123'),
(17, 49, 'Cristiano_Ronaldo', '1985-02-05', 'cristiano.ronaldo@test.com', 'Portugal', 'cr7'),
(18, 50, 'Ruben_Dias', '1997-05-14', 'ruben.dias@test.com', 'Portugal', 'rubendias'),
(19, 51, 'Vitinha', '2000-02-13', 'vitinha@test.com', 'Portugal', 'vitinha'),
(20, 52, 'Diogo_Costa', '1999-09-19', 'diogo.costa@test.com', 'Portugal', 'diogocosta'),
(21, 53, 'Joao_Neves', '2004-09-27', 'joao.neves@test.com', 'Portugal', 'joaoneves'),
(22, 54, 'Ruben_Neves', '1997-03-13', 'ruben.neves@test.com', 'Portugal', 'rubenneves');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `attends`
--
ALTER TABLE `attends`
  ADD PRIMARY KEY (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `takes_collection` (`collection_id`);

--
-- Índices para tabela `collection`
--
ALTER TABLE `collection`
  ADD PRIMARY KEY (`collection_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `image_id` (`image_id`);

--
-- Índices para tabela `collection_tags`
--
ALTER TABLE `collection_tags`
  ADD PRIMARY KEY (`collection_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Índices para tabela `contains`
--
ALTER TABLE `contains`
  ADD KEY `collection` (`collection_id`),
  ADD KEY `item` (`item_id`);

--
-- Índices para tabela `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `image_id` (`image_id`),
  ADD KEY `creator` (`user_id`);

--
-- Índices para tabela `favourite`
--
ALTER TABLE `favourite`
  ADD PRIMARY KEY (`user_id`,`collection_id`),
  ADD KEY `collection_id` (`collection_id`);

--
-- Índices para tabela `friends`
--
ALTER TABLE `friends`
  ADD PRIMARY KEY (`user_id`,`friend_id`),
  ADD KEY `friend_id` (`friend_id`);

--
-- Índices para tabela `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`image_id`);

--
-- Índices para tabela `item`
--
ALTER TABLE `item`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `image_id` (`image_id`);

--
-- Índices para tabela `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`user_id`,`collection_id`,`event_id`),
  ADD KEY `collection_id` (`collection_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Índices para tabela `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`tag_id`);

--
-- Índices para tabela `type`
--
ALTER TABLE `type`
  ADD PRIMARY KEY (`type_id`);

--
-- Índices para tabela `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `image_id` (`image_id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `collection`
--
ALTER TABLE `collection`
  MODIFY `collection_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de tabela `event`
--
ALTER TABLE `event`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `image`
--
ALTER TABLE `image`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT de tabela `item`
--
ALTER TABLE `item`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT de tabela `tags`
--
ALTER TABLE `tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `type`
--
ALTER TABLE `type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de tabela `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `attends`
--
ALTER TABLE `attends`
  ADD CONSTRAINT `attends_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attends_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `takes_collection` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`);

--
-- Limitadores para a tabela `collection`
--
ALTER TABLE `collection`
  ADD CONSTRAINT `collection_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_ibfk_2` FOREIGN KEY (`image_id`) REFERENCES `image` (`image_id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `collection_tags`
--
ALTER TABLE `collection_tags`
  ADD CONSTRAINT `collection_tags_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collection_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `contains`
--
ALTER TABLE `contains`
  ADD CONSTRAINT `collection` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`),
  ADD CONSTRAINT `item` FOREIGN KEY (`item_id`) REFERENCES `item` (`item_id`);

--
-- Limitadores para a tabela `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `creator` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  ADD CONSTRAINT `event_ibfk_1` FOREIGN KEY (`image_id`) REFERENCES `image` (`image_id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `favourite`
--
ALTER TABLE `favourite`
  ADD CONSTRAINT `favourite_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favourite_ibfk_2` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `friends`
--
ALTER TABLE `friends`
  ADD CONSTRAINT `friends_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friends_ibfk_2` FOREIGN KEY (`friend_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `item`
--
ALTER TABLE `item`
  ADD CONSTRAINT `item_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `type` (`type_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `item_ibfk_3` FOREIGN KEY (`image_id`) REFERENCES `image` (`image_id`) ON DELETE SET NULL;

--
-- Limitadores para a tabela `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`collection_id`) REFERENCES `collection` (`collection_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rating_ibfk_3` FOREIGN KEY (`event_id`) REFERENCES `event` (`event_id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`image_id`) REFERENCES `image` (`image_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
