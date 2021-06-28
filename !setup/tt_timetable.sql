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
-- Tábla szerkezet ehhez a táblához `tt_timetable`
--

CREATE TABLE `tt_timetable` (
  `season_id` int(10) UNSIGNED NOT NULL,
  `ttdate` datetime NOT NULL,
  `position` int(11) NOT NULL,
  `lesson_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- A tábla adatainak kiíratása `tt_timetable`
--

INSERT INTO `tt_timetable` (`season_id`, `ttdate`, `position`, `lesson_id`) VALUES
(4, '2021-01-25 00:00:00', 0, 15),
(4, '2021-01-25 00:00:00', 1, 15),
(4, '2021-01-25 00:00:00', 2, 15),
(4, '2021-01-25 00:00:00', 3, 13),
(4, '2021-01-25 00:00:00', 4, 13),
(4, '2021-01-25 00:00:00', 5, 13),
(4, '2021-01-25 00:00:00', 6, 13),
(4, '2021-01-25 00:00:00', 7, 13),
(5, '2021-01-25 00:00:00', 0, 322),
(5, '2021-01-25 00:00:00', 1, 322),
(5, '2021-01-25 00:00:00', 2, 322),
(10, '2021-01-25 00:00:00', 0, 324),
(10, '2021-02-01 00:00:00', 0, 324),
(10, '2021-02-08 00:00:00', 0, 324);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
