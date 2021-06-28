-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2021. Jún 28. 14:36
-- Kiszolgáló verziója: 10.4.19-MariaDB
-- PHP verzió: 7.4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `tt_settings`
--

CREATE TABLE `tt_settings` (
  `id` tinytext CHARACTER SET utf8mb4 NOT NULL,
  `value` text CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- A tábla adatainak kiíratása `tt_settings`
--

INSERT INTO `tt_settings` (`id`, `value`) VALUES
('active_season', 'i:10;');

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `tt_settings`
--
ALTER TABLE `tt_settings`
  ADD UNIQUE KEY `id` (`id`) USING HASH;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
