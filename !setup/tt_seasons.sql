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
-- Tábla szerkezet ehhez a táblához `tt_seasons`
--

CREATE TABLE `tt_seasons` (
  `season_id` int(10) UNSIGNED NOT NULL,
  `nev` varchar(50) NOT NULL,
  `elso_tanitasi_nap` varchar(10) NOT NULL,
  `utolso_tanitasi_nap` varchar(10) NOT NULL,
  `napi_oraszam` tinyint(4) NOT NULL,
  `kezdo_oraszam` tinyint(4) NOT NULL,
  `nincs_tanitas` text NOT NULL,
  `hetek_szama` tinyint(4) NOT NULL,
  `tanitasi_hetek` tinyint(4) NOT NULL,
  `utolso_mentes` varchar(16) NOT NULL,
  `heti_max_oraszam` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- A tábla adatainak kiíratása `tt_seasons`
--

INSERT INTO `tt_seasons` (`season_id`, `nev`, `elso_tanitasi_nap`, `utolso_tanitasi_nap`, `napi_oraszam`, `kezdo_oraszam`, `nincs_tanitas`, `hetek_szama`, `tanitasi_hetek`, `utolso_mentes`, `heti_max_oraszam`) VALUES
(4, '2020/2021 2. félév', '2021.01.25', '2021.06.20', 8, 8, 'a:4:{i:0;s:10:\"2021.06.14\";i:1;s:10:\"2021.06.16\";i:2;s:10:\"2021.06.17\";i:3;s:10:\"2021.06.18\";}', 21, 18, '', 18),
(5, 'proba', '2021.01.25', '2021.05.07', 8, 8, 'a:2:{i:0;s:10:\"2021.05.06\";i:1;s:10:\"2021.05.07\";}', 15, 18, '', 18),
(6, 'proba2', '2021.06.07', '2021.07.04', 8, 8, 'a:0:{}', 4, 18, '', 18),
(10, 'proba klon2b', '2021.01.25', '2021.05.07', 8, 8, 'a:2:{i:0;s:10:\"2021.05.06\";i:1;s:10:\"2021.05.07\";}', 15, 18, '', 18);

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `tt_seasons`
--
ALTER TABLE `tt_seasons`
  ADD PRIMARY KEY (`season_id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `tt_seasons`
--
ALTER TABLE `tt_seasons`
  MODIFY `season_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
