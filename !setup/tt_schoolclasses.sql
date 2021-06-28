-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2021. Jún 28. 14:35
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
-- Tábla szerkezet ehhez a táblához `tt_schoolclasses`
--

CREATE TABLE `tt_schoolclasses` (
  `id` int(11) UNSIGNED NOT NULL,
  `season_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `short_name` varchar(12) DEFAULT NULL,
  `day1` tinyint(1) DEFAULT 0,
  `day2` tinyint(1) DEFAULT 0,
  `day3` tinyint(1) DEFAULT 0,
  `day4` tinyint(1) DEFAULT 0,
  `day5` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- A tábla adatainak kiíratása `tt_schoolclasses`
--

INSERT INTO `tt_schoolclasses` (`id`, `season_id`, `name`, `short_name`, `day1`, `day2`, `day3`, `day4`, `day5`) VALUES
(1, 4, 'E/1/13.A', '13.A', 1, 1, 1, 0, 0),
(3, 4, 'E/1/13.B', '13.B', 0, 1, 1, 1, 0),
(4, 4, 'E/1/13.F', '13.F', 1, 1, 0, 1, 0),
(5, 4, 'E/1/13.K', '13.K', 1, 0, 1, 1, 0),
(6, 4, 'E/1/13.G', '13.G', 1, 1, 1, 0, 0),
(7, 4, 'E/2/14.A', '14.A', 0, 0, 0, 1, 0),
(8, 4, 'E/2/14.R1', '14.R1', 0, 0, 1, 0, 0),
(9, 4, 'E/2/14.R2', '14.R2', 0, 1, 0, 1, 0),
(10, 4, 'E/2/14.D', '14.D', 0, 1, 0, 1, 0),
(11, 4, 'E/2/14.F', '14.F', 0, 1, 0, 0, 0),
(12, 4, 'E/2/14.G', '14.G', 1, 0, 1, 0, 0),
(13, 4, 'E/3/15.M', '15.M', 0, 1, 0, 0, 0),
(14, 4, 'E/2/14.C', '14.C', 0, 1, 0, 0, 0),
(15, 4, 'E/2/14.K', '14.K', 0, 1, 0, 1, 0),
(16, 4, 'E/3/15.A', '15.A', 0, 0, 0, 1, 0),
(17, 4, 'E/3/15.B', '15.B', 1, 0, 0, 0, 0),
(18, 4, 'E/2/14.M', '14.M', 0, 0, 0, 1, 0),
(19, 4, 'E/3/15.D', '15.D', 0, 0, 0, 1, 0),
(22, 5, '20.a', '20.a', 1, 1, 0, 0, 0),
(25, 10, '20.a', '20.a', 1, 1, 0, 0, 0);

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `tt_schoolclasses`
--
ALTER TABLE `tt_schoolclasses`
  ADD PRIMARY KEY (`id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `tt_schoolclasses`
--
ALTER TABLE `tt_schoolclasses`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
