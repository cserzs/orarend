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
-- Tábla szerkezet ehhez a táblához `tt_teachers`
--

CREATE TABLE `tt_teachers` (
  `id` int(11) UNSIGNED NOT NULL,
  `season_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) DEFAULT NULL,
  `short_name` varchar(10) DEFAULT NULL,
  `day1` tinyint(1) NOT NULL DEFAULT 0,
  `day2` tinyint(1) NOT NULL DEFAULT 0,
  `day3` tinyint(1) NOT NULL DEFAULT 0,
  `day4` tinyint(1) NOT NULL DEFAULT 0,
  `day5` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- A tábla adatainak kiíratása `tt_teachers`
--

INSERT INTO `tt_teachers` (`id`, `season_id`, `name`, `short_name`, `day1`, `day2`, `day3`, `day4`, `day5`) VALUES
(1, 4, 'Almási Ágnes', 'AÁ', 0, 0, 0, 0, 0),
(2, 4, 'Bagány Istvánné', 'BI', 0, 0, 0, 0, 0),
(3, 4, 'Bálintné Ducsai Mónika', 'BDM', 0, 0, 0, 0, 0),
(4, 4, 'Baranyiné Jászfalvi Marianna', 'BJM', 0, 0, 0, 0, 0),
(5, 4, 'Barczaházi Andrea', 'BA', 0, 0, 0, 0, 0),
(6, 4, 'Barna Krisztina', 'BK', 0, 0, 0, 0, 0),
(7, 4, 'Barna Zsolt', 'BZs', 0, 0, 0, 0, 0),
(8, 4, 'Barta Tamás Ferenc', 'BT', 0, 0, 0, 0, 0),
(9, 4, 'Járási Mónika', 'JM', 0, 0, 0, 0, 0),
(10, 4, 'Bereginé Almási Erzsébet', 'BAE', 0, 0, 0, 0, 0),
(11, 4, 'Birkás Zsuzsanna', 'BiZs', 0, 0, 0, 0, 0),
(12, 4, 'Bolla Ildikó', 'BI', 0, 0, 0, 0, 0),
(13, 4, 'Borza Beáta', 'BB', 0, 0, 0, 0, 0),
(14, 4, 'Borzáné Kádár Marianna', 'BKM', 0, 0, 0, 0, 0),
(15, 4, 'Csernai Zsolt', 'CsZs', 0, 0, 0, 0, 0),
(16, 4, 'Cservenák Jenő László', 'CsJe', 0, 0, 0, 0, 0),
(17, 4, 'Csizmárné Tóth Katalin', 'CsTK', 0, 0, 0, 0, 0),
(18, 4, 'Csopják Judit', 'CsJ', 1, 0, 0, 0, 0),
(19, 4, 'Deákné Benke Judit', 'DBJ', 0, 0, 0, 0, 0),
(20, 4, 'Demeter János Szabolcs', 'DJ', 0, 0, 0, 0, 0),
(21, 4, 'Dobosi Mária', 'DM', 0, 0, 0, 0, 0),
(22, 4, 'Dolovics Anett', 'DA', 0, 0, 0, 0, 0),
(23, 4, 'Dr. Juhász-Pap Gréta', 'JPG', 0, 0, 0, 0, 0),
(24, 4, 'Dr. Vargáné Barna Tímea', 'VBT', 0, 0, 0, 0, 0),
(25, 4, 'Dr. Antalfi Bálint Károly', 'AB', 0, 0, 0, 0, 0),
(26, 4, 'Dr. Barláné Nahaj Bernadett', 'BNB', 0, 0, 0, 0, 0),
(27, 4, 'Dr. Bodroghy-Szabó Balázs', 'BSzB', 0, 0, 0, 0, 0),
(28, 4, 'Dr. Csollák István', 'CsI', 0, 0, 0, 0, 0),
(29, 4, 'Dr. Dabasi Halász Zsigmond', 'DHZs', 0, 0, 0, 0, 0),
(30, 4, 'Dr. Kovács Anetta Mária', 'KAM', 0, 0, 0, 0, 0),
(31, 4, 'Dr. Zsúdelné Dr. Lajos Edit', 'ZsLE', 0, 0, 0, 0, 0),
(32, 4, 'Dr. Mágoriné Orosz Bernadett', 'MOB', 0, 0, 0, 0, 0),
(33, 4, 'Dr. Nagy Ádám Géza', 'NÁG', 0, 0, 0, 0, 0),
(34, 4, 'Dr. Skapinyecz Tibor', 'ST', 0, 0, 0, 0, 0),
(35, 4, 'Dr. Tóth-Kelemen Szilvia', 'TKSz', 0, 0, 0, 0, 0),
(36, 4, 'Dudás Imréné', 'DI', 0, 0, 0, 0, 0),
(37, 4, 'Ecseghy Nóra', 'EN', 0, 0, 0, 0, 0),
(38, 4, 'Furuglyás Attila', 'FA', 0, 0, 0, 0, 0),
(39, 4, 'Füvesi Frigyes Józsefné', 'FFJ', 0, 0, 0, 0, 0),
(40, 4, 'Gálné Pataki Zsuzsanna Gabriella', 'GPZs', 0, 0, 0, 0, 0),
(41, 4, 'Gyöngy Tiborné', 'GyT', 0, 0, 0, 0, 0),
(42, 4, 'Hajduné Virágh Edit', 'HVE', 0, 0, 0, 0, 0),
(43, 4, 'Hoffmann Attiláné', 'HA', 0, 0, 0, 0, 0),
(44, 4, 'Horváth Emese', 'HE', 0, 0, 0, 0, 0),
(45, 4, 'Ignácz Magdolna', 'IM', 0, 0, 0, 0, 0),
(46, 4, 'Jakab-Kiss Orsolya', 'JKO', 0, 0, 0, 0, 0),
(47, 4, 'Jánosikné Szabó Tünde', 'JSzT', 0, 0, 0, 0, 0),
(48, 4, 'Juhász Ildikó', 'JI', 0, 0, 0, 0, 0),
(49, 4, 'Juhászné Kós Edit', 'JKE', 0, 0, 0, 0, 0),
(50, 4, 'Juza Adrienn', 'JA', 0, 0, 0, 0, 0),
(51, 4, 'Kádár-Brandlhofer Hajnal', 'KBH', 0, 0, 0, 0, 0),
(52, 4, 'Kiss Krisztina', 'KiK', 0, 0, 0, 0, 0),
(53, 4, 'Kissné Szmola Zsuzsanna', 'KSzZs', 0, 0, 0, 0, 0),
(54, 4, 'Kolozsváry Ágnes', 'KÁ', 0, 0, 0, 0, 0),
(55, 4, 'Koppányné Szendrák Mária Ida', 'KSzM', 0, 0, 0, 0, 0),
(56, 4, 'Kosztyu Tünde', 'KT', 0, 0, 0, 0, 0),
(57, 4, 'Kramcsák Mónika Erika', 'KME', 0, 0, 0, 0, 0),
(58, 4, 'Kuruczné Kiss Katalin', 'KKK', 0, 0, 0, 0, 0),
(59, 4, 'Lakatosné Varga Ágnes', 'LVÁ', 0, 0, 0, 0, 0),
(60, 4, 'László Jelena Viktorovna', 'LJV', 0, 0, 0, 0, 0),
(61, 4, 'Leszták Péter', 'LP', 0, 0, 0, 0, 0),
(62, 4, 'Lichtenstein Raymond', 'LR', 0, 0, 0, 0, 0),
(63, 4, 'Magyari Viktor', 'MV', 0, 0, 0, 0, 0),
(64, 4, 'Molnár Ádám', 'MÁ', 0, 0, 0, 0, 0),
(65, 4, 'Molnár Mária Terézia', 'MM', 0, 0, 0, 0, 0),
(66, 4, 'Nagy Nóra', 'NN', 0, 0, 0, 0, 0),
(67, 4, 'Nagyné Gulyás Erna', 'NGE', 0, 0, 0, 0, 0),
(68, 4, 'Nemeskéri Csilla', 'NCs', 0, 0, 0, 0, 0),
(69, 4, 'Németh Edit', 'NE', 0, 0, 0, 0, 0),
(70, 4, 'Novek Zsuzsa', 'NZs', 0, 0, 0, 0, 0),
(71, 4, 'Nyilas Zsuzsanna', 'NyZs', 0, 0, 0, 0, 0),
(72, 4, 'Orosz István', 'OI', 0, 0, 0, 0, 0),
(73, 4, 'Oroszné Urbán Katalin Julianna', 'OUK', 0, 0, 0, 0, 0),
(74, 4, 'Pál István', 'PI', 0, 0, 0, 0, 0),
(75, 4, 'Papp Jánosné', 'PJ', 0, 0, 0, 0, 0),
(76, 4, 'Paszternák Ténia Mária', 'PT', 0, 0, 0, 0, 0),
(77, 4, 'Perjésné Bodgál Judit', 'PBJ', 0, 0, 0, 0, 0),
(78, 4, 'Porcs Doszpoly Piroska', 'PDP', 0, 0, 0, 0, 0),
(79, 4, 'Roskóné Szilágyi Ágnes', 'RSzÁ', 0, 0, 0, 0, 0),
(80, 4, 'Sallai Anita', 'SaA', 0, 0, 0, 0, 0),
(81, 4, 'Seres Györgyné', 'SGy', 0, 0, 0, 0, 0),
(82, 4, 'Seres-Gál Judit', 'SGJ', 0, 0, 0, 0, 0),
(83, 4, 'Simonyi Antónia Ilona', 'SiA', 0, 0, 0, 0, 0),
(84, 4, 'Skaruppa Hajnalka', 'SH', 0, 0, 0, 0, 0),
(85, 4, 'Soósné Hável Zsuzsanna', 'SHZs', 0, 0, 0, 0, 0),
(86, 4, 'Szabóné Géczi Szilvia', 'SzGSz', 0, 0, 0, 0, 0),
(87, 4, 'Szalontai László', 'SzL', 0, 0, 0, 0, 0),
(88, 4, 'Szegedi Judit Katalin', 'SzJ', 0, 0, 0, 0, 0),
(89, 4, 'Szolnoki Beáta', 'SzB', 0, 0, 0, 0, 0),
(90, 4, 'Szöghy Hajnalka Csilla', 'SzH', 0, 0, 0, 0, 0),
(91, 4, 'Szűcsné Józsa Krisztina', 'SzJK', 0, 0, 0, 0, 0),
(92, 4, 'Tomasovszky Viktória', 'TV', 0, 0, 0, 0, 0),
(93, 4, 'Tóth Éva', 'TÉ', 0, 0, 0, 0, 0),
(94, 4, 'Tóth Sándorné', 'TS', 0, 0, 0, 0, 0),
(95, 4, 'Török Zsuzsanna', 'TZs', 0, 0, 0, 0, 0),
(96, 4, 'Vajdáné Homovics Dóra', 'VHD', 0, 0, 0, 0, 0),
(97, 4, 'Vass Zsoltné', 'VZs', 0, 0, 0, 0, 0),
(98, 4, 'Veres Edina', 'VE', 0, 0, 0, 0, 0),
(99, 4, 'Virágné Kaló Ágnes', 'VKÁ', 0, 0, 0, 0, 0),
(100, 4, 'Virányi Gábor ', 'VG', 0, 0, 0, 0, 0),
(101, 4, 'Vojtonovszki Gábor', 'VG', 0, 0, 0, 0, 0),
(102, 4, 'Zeller Gábor', 'ZG', 0, 0, 0, 0, 0),
(103, 4, 'Zsúdel Antónia Orsolya', 'ZsO', 0, 0, 0, 0, 0),
(105, 4, 'Kovács Éva', 'KÉ', 0, 0, 0, 0, 0),
(106, 4, 'Tóthné Dr. Takács Annamária', 'TTA', 0, 0, 0, 0, 0),
(107, 4, 'ifj. Dr. Skapinyecz Tibor', 'iST', 0, 0, 0, 0, 0),
(108, 4, 'Nagy Szilárd', 'NSz', 0, 0, 0, 0, 0),
(109, 4, 'Emődi Réka', 'ER', 0, 0, 0, 0, 0),
(110, 4, 'Vida Éva', 'VÉ', 0, 0, 0, 0, 0),
(111, 4, 'Dr. Baranyi Viktor', 'BV', 0, 0, 0, 0, 0),
(112, 4, 'Kocsis Kornélia', 'KoK', 0, 0, 0, 0, 0),
(113, 4, 'Dr. Kövics György', 'KGy', 0, 0, 0, 0, 0),
(114, 4, 'Dr. Kriegel Júlia', 'KJ', 0, 0, 0, 0, 0),
(115, 4, 'Senviczki Ágnes', 'SÁ', 0, 0, 0, 0, 0),
(116, 4, 'Barta + Demeter', 'Ba+De', 0, 0, 0, 0, 0),
(117, 4, 'Dr. Zsigmond Albert', 'ZsA', 0, 0, 0, 0, 0),
(118, 4, 'Szendrei Orsolya', 'SzO', 0, 0, 0, 0, 0),
(119, 4, 'Varga Frigyes', 'VF', 0, 0, 0, 0, 0),
(120, 4, 'Kassai Edit', 'KE', 0, 0, 0, 0, 0),
(121, 4, 'Illés Éva', 'IÉ', 0, 0, 0, 0, 0),
(122, 4, 'Izsó Dénes', 'ID', 0, 0, 0, 0, 0),
(123, 4, 'Demeter + Borza', 'De+BBe', 0, 0, 0, 0, 0),
(124, 4, 'Csopják + Illés', 'Cso+Il', 0, 0, 0, 0, 0),
(125, 4, 'Kiss+Porcs', 'Ki+Po', 0, 0, 0, 0, 0),
(126, 4, 'Almási + Hoffman', 'Al+Ho', 0, 0, 0, 0, 0),
(127, 4, 'Csató-Batári Eszter', 'CsBE', 0, 0, 0, 0, 0),
(128, 4, 'Vargáné + Borzáné', 'Va+BKM', 0, 0, 0, 0, 0),
(129, 4, 'Mélypataki Rita', 'MR', 0, 0, 0, 0, 0),
(130, 4, 'Lénárt Miklósné', 'LM', 0, 0, 0, 0, 0),
(131, 4, 'Dr. Adorjánné Dr. Balogh Marianna', 'ABM', 0, 0, 0, 0, 0),
(132, 4, 'Klemné Lőrinc Ildikó', 'KLI', 0, 0, 0, 0, 0),
(133, 4, 'Magyar Ágnes', 'MÁ', 0, 0, 0, 0, 0),
(134, 4, 'Dulibán Lászlóné', 'DL', 0, 0, 0, 0, 0),
(135, 4, 'Furákné Nyitrai Erzsébet', 'FNyE', 0, 0, 0, 0, 0),
(136, 4, 'Dr. Papp Miklós', 'PM', 0, 0, 0, 0, 0),
(137, 4, 'Dr. Almássy Sándor', 'AS', 0, 0, 0, 0, 0),
(138, 4, 'Dr. Ungvári Gábor', 'UG', 0, 0, 0, 0, 0),
(139, 4, 'Dr. Lenkei Balázs', 'LB', 0, 0, 0, 0, 0),
(140, 4, 'Czakó Hajnalka', 'CH', 0, 0, 0, 0, 0),
(141, 4, 'Juhászné Szalai Adrienn', 'JSzA', 0, 0, 0, 0, 0),
(142, 4, 'Kolláth Gáborné', 'KG', 0, 0, 0, 0, 0),
(143, 4, 'Dr. Reusz Géza', 'RG', 0, 0, 0, 0, 0),
(144, 4, 'Dr. Sipos József', 'SJ', 0, 0, 0, 0, 0),
(145, 4, 'Dr. Nagy Attila', 'NA', 0, 0, 0, 0, 0),
(146, 4, 'Dr. Schütt Dániel', 'SD', 0, 0, 0, 0, 0),
(147, 4, 'E/2/14.m külső gyakorlati oktató', 'ko14m', 0, 0, 0, 0, 0),
(148, 4, 'E/3/15.a külső gyakorlati oktató', 'ko15a', 0, 0, 0, 0, 0),
(149, 4, 'E/2/14.a külső gyakorlati oktató', 'ko14a', 0, 0, 0, 0, 0),
(150, 4, 'E/2/14.c külső gyakorlati oktató', 'ko14c', 0, 0, 0, 0, 0),
(151, 4, 'E/2/14.d külső gyakorlati oktató', 'ko14d', 0, 0, 0, 0, 0),
(152, 4, 'E/2/14.f külső gyakorlati oktató', 'ko14f', 0, 0, 0, 0, 0),
(153, 4, 'E/2/14.g külső gyakorlati oktató', 'ko14g', 0, 0, 0, 0, 0),
(154, 4, 'E/2/14.k külső gyakorlati oktató', 'ko14k', 0, 0, 0, 0, 0),
(155, 4, 'E/2/14.r1 külső gyakorlati oktató', 'ko14r1', 0, 0, 0, 0, 0),
(156, 4, 'E/2/14.r2 külső gyakorlati oktató', 'ko14r2', 0, 0, 0, 0, 0),
(157, 4, 'Dr. Szőllőssy Marianna', 'SzM', 0, 0, 0, 0, 0),
(158, 4, 'E/3/15.m külső gyakorlati oktató', 'ko15m', 0, 0, 0, 0, 0),
(159, 4, 'Sípos Ágnes', 'SÁ', 0, 0, 0, 0, 0),
(161, 4, 'E/3/15.b külső gyakorlati oktató', 'ko15b', 0, 0, 0, 0, 0),
(162, 4, 'E/3/15.d külső gyakorlati oktató', 'ko15d', 0, 0, 0, 0, 0),
(163, 4, 'Hudák Ibolya', 'HuI', 0, 0, 0, 0, 0),
(164, 4, 'Dr. Csontos Gergely', 'CsoG', 0, 0, 0, 0, 0),
(165, 4, 'Dr. Varga Rita', 'VaR', 0, 0, 0, 0, 0),
(166, 4, 'Dr. Fejes Zoltán', 'FeZ', 0, 0, 0, 0, 0),
(167, 4, 'Valaki 1', 'xy', 0, 0, 0, 0, 0),
(168, 4, 'Valaki 2', 'xy', 0, 0, 0, 0, 0),
(170, 5, 'Valaki', 'vki', 0, 0, 0, 0, 0),
(173, 10, 'Valaki', 'vki', 0, 0, 0, 0, 0),
(174, 6, 'Valaki', 'vki', 0, 0, 0, 0, 0);

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `tt_teachers`
--
ALTER TABLE `tt_teachers`
  ADD PRIMARY KEY (`id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `tt_teachers`
--
ALTER TABLE `tt_teachers`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
