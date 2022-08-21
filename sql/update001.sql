-- Adminer 4.7.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `albums`;
CREATE TABLE `albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `thumbnail` tinytext COLLATE utf8_slovak_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `assistances`;
CREATE TABLE `assistances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fight_id` int(11) NOT NULL,
  `player_season_group_team_id` int(11) NOT NULL,
  `number` int(11) DEFAULT '0',
  `is_home_player` tinyint(4) NOT NULL DEFAULT '0',
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fight_id` (`fight_id`),
  KEY `player_season_group_team_id` (`player_season_group_team_id`),
  CONSTRAINT `assistances_ibfk_1` FOREIGN KEY (`fight_id`) REFERENCES `fights` (`id`),
  CONSTRAINT `assistances_ibfk_2` FOREIGN KEY (`player_season_group_team_id`) REFERENCES `players_seasons_groups_teams` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `season_id` int(11) DEFAULT NULL,
  `content` text COLLATE utf8_slovak_ci NOT NULL,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `season_id` (`season_id`),
  CONSTRAINT `events_ibfk_2` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`) ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `fights`;
CREATE TABLE `fights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `round_id` int(11) NOT NULL,
  `team1_id` int(11) NOT NULL,
  `team2_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL DEFAULT '1',
  `score1` int(11) NOT NULL DEFAULT '0',
  `score2` int(11) NOT NULL DEFAULT '0',
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `round_id` (`round_id`),
  KEY `team1_id` (`team1_id`),
  KEY `team2_id` (`team2_id`),
  KEY `score1` (`score1`),
  KEY `score2` (`score2`),
  KEY `table_id` (`table_id`),
  CONSTRAINT `fights_ibfk_21` FOREIGN KEY (`round_id`) REFERENCES `rounds` (`id`),
  CONSTRAINT `fights_ibfk_22` FOREIGN KEY (`team1_id`) REFERENCES `teams` (`id`),
  CONSTRAINT `fights_ibfk_23` FOREIGN KEY (`team2_id`) REFERENCES `teams` (`id`),
  CONSTRAINT `fights_ibfk_24` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `goals`;
CREATE TABLE `goals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fight_id` int(11) NOT NULL,
  `player_season_group_team_id` int(11) NOT NULL,
  `number` int(11) DEFAULT NULL,
  `is_home_player` tinyint(4) NOT NULL DEFAULT '0',
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fight_id` (`fight_id`),
  KEY `player_season_group_team_id` (`player_season_group_team_id`),
  KEY `number` (`number`),
  CONSTRAINT `goals_ibfk_7` FOREIGN KEY (`fight_id`) REFERENCES `fights` (`id`),
  CONSTRAINT `goals_ibfk_8` FOREIGN KEY (`player_season_group_team_id`) REFERENCES `players_seasons_groups_teams` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `groups`;
CREATE TABLE `groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `images`;
CREATE TABLE `images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) NOT NULL,
  `name` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `album_id` (`album_id`),
  CONSTRAINT `images_ibfk_9` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `links`;
CREATE TABLE `links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `url` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `players`;
CREATE TABLE `players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_slovak_ci NOT NULL,
  `number` int(11) NOT NULL,
  `born` varchar(10) COLLATE utf8_slovak_ci DEFAULT NULL,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `number` (`number`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `players_seasons_groups_teams`;
CREATE TABLE `players_seasons_groups_teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `season_group_team_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `player_type_id` int(11) NOT NULL,
  `goals` int(11) NOT NULL DEFAULT '0',
  `assistances` int(11) NOT NULL DEFAULT '0',
  `is_transfer` tinyint(4) NOT NULL DEFAULT '0',
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `season_group_team_id` (`season_group_team_id`),
  KEY `player_id` (`player_id`),
  KEY `player_type_id` (`player_type_id`),
  CONSTRAINT `players_seasons_groups_teams_ibfk_6` FOREIGN KEY (`season_group_team_id`) REFERENCES `seasons_groups_teams` (`id`) ON DELETE NO ACTION,
  CONSTRAINT `players_seasons_groups_teams_ibfk_7` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE NO ACTION,
  CONSTRAINT `players_seasons_groups_teams_ibfk_8` FOREIGN KEY (`player_type_id`) REFERENCES `player_types` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;

INSERT INTO `players_seasons_groups_teams` (`id`, `season_group_team_id`, `player_id`, `player_type_id`, `goals`, `assistances`, `is_transfer`, `is_present`) VALUES
(2,	11,	2,	5,	0,	0,	0,	0),
(3,	11,	3,	7,	6,	0,	0,	1),
(4,	11,	4,	7,	1,	0,	0,	1),
(5,	11,	5,	1,	4,	0,	0,	1),
(6,	11,	6,	1,	7,	0,	0,	1),
(7,	11,	7,	1,	10,	0,	0,	1),
(8,	11,	8,	1,	3,	0,	0,	1),
(9,	11,	9,	1,	1,	0,	0,	1),
(10,	11,	10,	1,	4,	0,	0,	1),
(11,	11,	11,	1,	34,	0,	0,	1),
(12,	11,	12,	1,	0,	0,	0,	1),
(13,	11,	13,	1,	1,	0,	0,	1),
(14,	11,	14,	1,	0,	0,	0,	1),
(15,	11,	15,	1,	4,	0,	0,	1),
(16,	11,	16,	1,	0,	0,	0,	1),
(17,	11,	17,	1,	0,	0,	0,	1),
(18,	11,	18,	1,	0,	0,	0,	1),
(19,	11,	19,	1,	0,	0,	0,	1),
(20,	11,	20,	1,	1,	0,	0,	1),
(21,	11,	21,	1,	0,	0,	0,	1),
(22,	11,	22,	1,	0,	0,	0,	1),
(23,	11,	23,	1,	0,	0,	0,	1),
(24,	11,	24,	1,	0,	0,	0,	1),
(25,	11,	25,	1,	0,	0,	0,	1),
(26,	11,	26,	2,	0,	0,	0,	1),
(27,	11,	27,	2,	0,	0,	0,	1),
(28,	11,	28,	2,	1,	0,	0,	1),
(29,	11,	29,	2,	0,	0,	0,	1),
(30,	11,	30,	2,	0,	0,	0,	1),
(31,	7,	31,	5,	9,	0,	0,	1),
(32,	7,	32,	1,	0,	0,	0,	1),
(33,	7,	33,	1,	9,	0,	0,	1),
(34,	7,	34,	1,	1,	0,	0,	1),
(35,	7,	35,	1,	16,	0,	0,	1),
(36,	7,	36,	1,	9,	0,	0,	1),
(37,	7,	37,	1,	0,	0,	0,	1),
(38,	7,	38,	1,	2,	0,	0,	1),
(39,	7,	39,	1,	5,	0,	0,	1),
(40,	7,	40,	1,	1,	0,	0,	1),
(41,	7,	41,	1,	0,	0,	0,	1),
(42,	7,	42,	1,	0,	0,	0,	1),
(43,	7,	43,	1,	0,	0,	0,	1),
(44,	7,	44,	1,	2,	0,	0,	1),
(45,	7,	45,	1,	1,	0,	0,	1),
(46,	7,	46,	1,	6,	0,	0,	1),
(49,	7,	49,	1,	0,	0,	0,	1),
(50,	7,	50,	1,	0,	0,	0,	0),
(51,	7,	51,	1,	0,	0,	0,	1),
(52,	7,	52,	1,	0,	0,	0,	1),
(53,	7,	53,	1,	0,	0,	0,	1),
(54,	7,	54,	1,	0,	0,	0,	1),
(55,	7,	55,	2,	0,	0,	0,	1),
(56,	7,	56,	2,	0,	0,	0,	1),
(57,	8,	57,	5,	7,	0,	0,	1),
(58,	8,	58,	1,	0,	0,	0,	1),
(59,	8,	59,	1,	6,	0,	0,	1),
(60,	8,	60,	1,	2,	0,	0,	1),
(61,	8,	61,	1,	3,	0,	0,	1),
(62,	8,	62,	1,	1,	0,	0,	1),
(63,	8,	63,	1,	1,	0,	0,	1),
(64,	8,	64,	1,	0,	0,	0,	1),
(65,	8,	65,	1,	0,	0,	0,	1),
(66,	8,	66,	1,	2,	0,	0,	1),
(67,	8,	67,	1,	12,	0,	0,	1),
(68,	8,	68,	1,	7,	0,	0,	1),
(69,	8,	69,	1,	1,	0,	0,	1),
(70,	8,	70,	1,	6,	0,	0,	1),
(71,	8,	71,	1,	1,	0,	0,	1),
(72,	8,	72,	1,	3,	0,	0,	1),
(73,	8,	73,	1,	8,	0,	0,	1),
(74,	8,	74,	1,	5,	0,	0,	1),
(75,	8,	75,	1,	1,	0,	0,	1),
(76,	8,	76,	1,	0,	0,	0,	1),
(77,	8,	77,	1,	0,	0,	0,	1),
(80,	8,	49,	1,	0,	0,	0,	0),
(81,	8,	78,	2,	0,	0,	0,	1),
(82,	8,	79,	2,	0,	0,	0,	1),
(83,	10,	80,	5,	1,	0,	0,	1),
(84,	10,	81,	1,	3,	0,	0,	1),
(85,	10,	82,	1,	0,	0,	0,	1),
(86,	10,	83,	1,	4,	0,	0,	1),
(87,	10,	84,	1,	5,	0,	0,	1),
(88,	10,	85,	1,	0,	0,	0,	1),
(89,	10,	86,	1,	28,	0,	0,	1),
(90,	10,	87,	1,	13,	0,	0,	1),
(91,	10,	88,	1,	2,	0,	0,	1),
(92,	10,	89,	1,	0,	0,	0,	1),
(93,	10,	90,	1,	9,	0,	0,	1),
(94,	10,	91,	1,	14,	0,	0,	1),
(95,	10,	92,	1,	2,	0,	0,	1),
(96,	10,	93,	1,	0,	0,	0,	1),
(97,	10,	94,	1,	0,	0,	0,	1),
(98,	10,	95,	1,	0,	0,	0,	1),
(99,	10,	96,	1,	5,	0,	0,	1),
(100,	10,	97,	1,	0,	0,	0,	1),
(101,	10,	98,	1,	0,	0,	0,	1),
(102,	10,	99,	1,	0,	0,	0,	1),
(103,	10,	100,	1,	6,	0,	0,	1),
(104,	10,	101,	1,	11,	0,	0,	1),
(105,	10,	102,	1,	1,	0,	0,	1),
(106,	10,	103,	2,	0,	0,	0,	1),
(107,	10,	104,	2,	0,	0,	0,	1),
(108,	10,	105,	2,	0,	0,	0,	1),
(109,	9,	106,	7,	2,	0,	0,	1),
(110,	9,	107,	5,	8,	0,	0,	1),
(111,	9,	108,	7,	11,	0,	0,	1),
(112,	9,	109,	1,	7,	0,	0,	1),
(113,	9,	110,	1,	8,	0,	0,	1),
(114,	9,	111,	1,	7,	0,	0,	1),
(115,	9,	112,	1,	0,	0,	0,	1),
(116,	9,	113,	1,	0,	0,	0,	1),
(117,	9,	114,	1,	6,	0,	0,	1),
(118,	9,	115,	1,	0,	0,	0,	1),
(119,	9,	116,	1,	3,	0,	0,	1),
(120,	9,	117,	1,	0,	0,	0,	1),
(121,	9,	118,	1,	9,	0,	0,	1),
(122,	9,	119,	1,	4,	0,	0,	1),
(123,	9,	120,	1,	9,	0,	0,	1),
(124,	9,	121,	1,	2,	0,	0,	1),
(125,	9,	122,	1,	0,	0,	0,	1),
(126,	9,	123,	1,	1,	0,	0,	1),
(127,	9,	124,	1,	0,	0,	0,	1),
(128,	9,	125,	1,	4,	0,	0,	1),
(129,	9,	126,	1,	5,	0,	0,	1),
(130,	9,	127,	1,	1,	0,	0,	1),
(131,	9,	128,	1,	0,	0,	0,	1),
(132,	9,	129,	1,	2,	0,	0,	1),
(135,	4,	2,	5,	8,	0,	0,	1),
(136,	4,	131,	7,	13,	0,	0,	1),
(137,	4,	132,	7,	2,	0,	0,	1),
(138,	4,	133,	1,	1,	0,	0,	1),
(139,	4,	134,	1,	16,	0,	0,	1),
(140,	4,	135,	1,	5,	0,	0,	1),
(141,	4,	136,	1,	6,	0,	0,	1),
(142,	4,	137,	1,	4,	0,	0,	1),
(143,	4,	138,	1,	0,	0,	0,	1),
(144,	4,	139,	1,	0,	0,	0,	1),
(145,	4,	140,	1,	0,	0,	0,	1),
(146,	4,	141,	1,	10,	0,	0,	1),
(147,	4,	142,	1,	1,	0,	0,	1),
(148,	4,	143,	1,	0,	0,	0,	1),
(149,	4,	144,	1,	0,	0,	0,	1),
(150,	4,	145,	1,	3,	0,	0,	1),
(151,	4,	45,	1,	1,	0,	0,	1),
(152,	4,	146,	1,	29,	0,	0,	1),
(153,	4,	147,	1,	1,	0,	0,	1),
(154,	4,	148,	1,	0,	0,	0,	1),
(155,	4,	149,	1,	2,	0,	0,	1),
(158,	4,	55,	1,	0,	0,	0,	1),
(159,	4,	150,	2,	0,	0,	0,	1),
(160,	4,	151,	2,	0,	0,	0,	1),
(161,	3,	152,	5,	7,	0,	0,	1),
(162,	3,	153,	7,	15,	0,	0,	1),
(163,	3,	154,	7,	3,	0,	0,	1),
(164,	3,	155,	1,	2,	0,	0,	1),
(165,	3,	156,	1,	0,	0,	0,	1),
(166,	3,	157,	1,	1,	0,	0,	1),
(167,	3,	158,	1,	0,	0,	0,	1),
(168,	3,	159,	1,	7,	0,	0,	1),
(169,	3,	160,	1,	11,	0,	0,	1),
(170,	3,	161,	1,	8,	0,	0,	1),
(171,	3,	162,	1,	3,	0,	0,	1),
(172,	3,	163,	1,	0,	0,	0,	1),
(173,	3,	164,	1,	3,	0,	0,	1),
(174,	3,	165,	1,	0,	0,	0,	1),
(175,	3,	166,	1,	0,	0,	0,	1),
(176,	3,	167,	1,	1,	0,	0,	1),
(177,	3,	168,	1,	3,	0,	0,	1),
(178,	3,	169,	1,	0,	0,	0,	0),
(179,	3,	170,	1,	1,	0,	0,	0),
(180,	3,	171,	1,	0,	0,	0,	1),
(183,	3,	49,	1,	0,	0,	0,	0),
(184,	3,	172,	2,	1,	0,	0,	1),
(185,	3,	173,	2,	0,	0,	0,	1),
(186,	3,	174,	2,	0,	0,	0,	1),
(187,	5,	175,	7,	3,	0,	0,	1),
(188,	5,	176,	5,	0,	0,	0,	1),
(189,	5,	177,	7,	0,	0,	0,	1),
(190,	5,	178,	1,	3,	0,	0,	1),
(191,	5,	179,	1,	12,	0,	0,	1),
(192,	5,	180,	1,	0,	0,	0,	1),
(193,	5,	181,	1,	4,	0,	0,	1),
(194,	5,	182,	1,	7,	0,	0,	1),
(195,	5,	183,	1,	1,	0,	0,	1),
(196,	5,	184,	1,	21,	0,	0,	1),
(197,	5,	185,	1,	2,	0,	0,	1),
(198,	5,	186,	1,	0,	0,	0,	1),
(199,	5,	187,	1,	1,	0,	0,	1),
(200,	5,	188,	1,	0,	0,	0,	1),
(201,	5,	189,	1,	7,	0,	0,	1),
(202,	5,	190,	1,	0,	0,	0,	1),
(203,	5,	191,	1,	2,	0,	0,	1),
(206,	5,	49,	1,	0,	0,	0,	0),
(207,	5,	50,	1,	0,	0,	0,	0),
(208,	5,	51,	1,	0,	0,	0,	1),
(209,	5,	52,	1,	0,	0,	0,	1),
(210,	5,	53,	1,	0,	0,	0,	1),
(211,	5,	192,	2,	0,	0,	0,	1),
(212,	5,	193,	2,	0,	0,	0,	1),
(213,	1,	194,	7,	13,	0,	0,	1),
(214,	1,	108,	7,	9,	0,	0,	1),
(215,	1,	195,	1,	0,	0,	0,	1),
(216,	1,	196,	1,	0,	0,	0,	1),
(217,	1,	197,	1,	21,	0,	0,	1),
(218,	1,	127,	1,	0,	0,	0,	1),
(219,	1,	198,	1,	16,	0,	0,	1),
(220,	1,	199,	1,	1,	0,	0,	1),
(221,	1,	125,	5,	2,	0,	0,	1),
(222,	1,	200,	1,	0,	0,	0,	1),
(223,	1,	118,	1,	4,	0,	0,	1),
(224,	1,	201,	1,	14,	0,	0,	1),
(225,	1,	202,	1,	2,	0,	0,	1),
(226,	1,	203,	1,	0,	0,	0,	1),
(227,	1,	204,	1,	9,	0,	0,	1),
(228,	1,	205,	1,	2,	0,	0,	1),
(229,	1,	126,	1,	5,	0,	0,	1),
(230,	1,	129,	1,	0,	0,	0,	1),
(231,	1,	206,	1,	2,	0,	0,	1),
(232,	5,	47,	1,	3,	0,	0,	1),
(233,	5,	48,	1,	0,	0,	0,	1),
(234,	1,	49,	1,	0,	0,	0,	0),
(235,	1,	50,	1,	0,	0,	0,	0),
(236,	1,	207,	2,	0,	0,	0,	1),
(237,	1,	208,	2,	0,	0,	0,	1),
(238,	1,	209,	2,	0,	0,	0,	1),
(239,	6,	210,	5,	14,	0,	0,	1),
(240,	6,	211,	7,	13,	0,	0,	1),
(241,	6,	212,	1,	6,	0,	0,	1),
(242,	6,	213,	1,	4,	0,	0,	1),
(243,	6,	214,	1,	0,	0,	0,	1),
(244,	6,	215,	1,	3,	0,	0,	1),
(245,	6,	216,	1,	6,	0,	0,	1),
(246,	6,	217,	1,	4,	0,	0,	1),
(247,	6,	218,	1,	5,	0,	0,	1),
(248,	6,	219,	1,	5,	0,	0,	1),
(249,	6,	220,	7,	14,	0,	0,	1),
(250,	6,	221,	1,	2,	0,	0,	1),
(251,	6,	222,	1,	2,	0,	0,	1),
(252,	6,	223,	1,	0,	0,	0,	1),
(253,	6,	224,	1,	0,	0,	0,	1),
(254,	6,	225,	1,	0,	0,	0,	1),
(255,	6,	226,	1,	0,	0,	0,	1),
(258,	6,	49,	1,	0,	0,	0,	0),
(259,	6,	50,	1,	0,	0,	0,	0),
(260,	6,	51,	1,	0,	0,	0,	1),
(261,	6,	52,	1,	0,	0,	0,	1),
(262,	6,	227,	2,	0,	0,	0,	1),
(263,	6,	228,	2,	0,	0,	0,	1),
(264,	6,	229,	2,	0,	0,	0,	1),
(265,	2,	230,	5,	2,	0,	0,	1),
(266,	2,	231,	7,	0,	0,	0,	1),
(267,	2,	232,	7,	3,	0,	0,	1),
(268,	2,	233,	1,	6,	0,	0,	1),
(269,	2,	234,	1,	8,	0,	0,	1),
(270,	2,	235,	1,	10,	0,	0,	1),
(271,	2,	236,	1,	0,	0,	0,	1),
(272,	2,	237,	1,	8,	0,	0,	1),
(273,	2,	238,	1,	4,	0,	0,	1),
(274,	2,	239,	1,	1,	0,	0,	1),
(275,	2,	240,	1,	2,	0,	0,	1),
(276,	2,	241,	1,	0,	0,	0,	1),
(277,	2,	242,	1,	6,	0,	0,	1),
(278,	2,	243,	1,	4,	0,	0,	1),
(279,	2,	244,	1,	2,	0,	0,	1),
(280,	2,	245,	1,	1,	0,	0,	1),
(281,	2,	246,	1,	5,	0,	0,	1),
(282,	2,	247,	1,	10,	0,	0,	1),
(283,	2,	248,	1,	16,	0,	0,	1),
(284,	2,	249,	1,	0,	0,	0,	1),
(287,	2,	49,	1,	0,	0,	0,	0),
(288,	2,	50,	1,	0,	0,	0,	0),
(289,	2,	250,	2,	0,	0,	0,	1),
(290,	2,	251,	2,	0,	0,	0,	1),
(291,	9,	252,	2,	0,	0,	0,	1),
(292,	9,	253,	1,	7,	0,	0,	1),
(293,	3,	254,	1,	1,	0,	0,	1),
(294,	1,	255,	1,	0,	0,	0,	1),
(295,	5,	256,	1,	0,	0,	0,	0),
(296,	6,	257,	1,	0,	0,	0,	0),
(297,	3,	258,	1,	0,	0,	0,	0),
(298,	3,	259,	1,	4,	0,	0,	1),
(299,	11,	260,	1,	0,	0,	0,	1),
(300,	1,	261,	1,	0,	0,	0,	1),
(301,	3,	262,	1,	2,	0,	0,	1),
(302,	1,	263,	1,	0,	0,	0,	1),
(303,	11,	264,	1,	6,	0,	0,	1),
(304,	11,	265,	1,	1,	0,	0,	1),
(305,	11,	266,	1,	0,	0,	0,	1),
(306,	11,	267,	1,	0,	0,	0,	1),
(307,	4,	268,	1,	2,	0,	0,	1),
(308,	9,	269,	1,	0,	0,	0,	1),
(309,	9,	130,	2,	0,	0,	0,	1),
(310,	1,	270,	1,	0,	0,	0,	1),
(311,	1,	271,	1,	0,	0,	0,	1),
(312,	3,	272,	1,	0,	0,	0,	1),
(313,	3,	273,	1,	0,	0,	0,	1),
(314,	3,	274,	2,	0,	0,	0,	1),
(315,	11,	275,	1,	11,	0,	0,	1),
(316,	11,	276,	1,	2,	0,	0,	1),
(317,	8,	277,	1,	1,	0,	0,	1),
(318,	2,	278,	1,	4,	0,	1,	1),
(319,	6,	279,	1,	0,	0,	0,	1),
(320,	10,	280,	1,	1,	0,	0,	1),
(321,	7,	281,	1,	2,	0,	0,	1),
(322,	5,	282,	2,	0,	0,	0,	1),
(323,	1,	283,	1,	1,	0,	0,	1),
(324,	3,	284,	2,	0,	0,	0,	1),
(325,	1,	285,	1,	0,	0,	1,	1),
(326,	11,	286,	1,	0,	0,	0,	1),
(327,	11,	287,	1,	0,	0,	0,	1),
(328,	1,	288,	2,	0,	0,	0,	1),
(329,	14,	152,	7,	0,	0,	0,	1),
(330,	14,	153,	5,	0,	0,	0,	1),
(331,	14,	154,	7,	0,	0,	0,	1),
(332,	14,	155,	1,	0,	0,	0,	1),
(333,	14,	156,	1,	0,	0,	0,	1),
(334,	14,	289,	1,	0,	0,	0,	0),
(335,	14,	157,	1,	0,	0,	0,	1),
(336,	14,	289,	1,	0,	0,	0,	1),
(337,	14,	290,	1,	0,	0,	0,	1),
(338,	14,	291,	1,	0,	0,	0,	1),
(339,	14,	292,	1,	0,	0,	0,	1),
(340,	14,	293,	1,	0,	0,	0,	1),
(341,	14,	163,	1,	0,	0,	0,	1),
(342,	14,	164,	1,	0,	0,	0,	1),
(343,	14,	262,	1,	0,	0,	0,	1),
(344,	14,	294,	1,	0,	0,	0,	1),
(345,	14,	167,	1,	0,	0,	0,	1),
(346,	14,	295,	1,	0,	0,	0,	1),
(347,	14,	296,	1,	0,	0,	0,	1),
(348,	14,	297,	1,	0,	0,	0,	1),
(349,	14,	298,	1,	0,	0,	0,	1),
(350,	14,	299,	1,	0,	0,	0,	1),
(352,	14,	226,	1,	0,	0,	0,	0),
(353,	14,	300,	1,	0,	0,	0,	0),
(354,	14,	51,	1,	0,	0,	0,	0),
(355,	14,	52,	1,	0,	0,	0,	0),
(356,	14,	53,	1,	0,	0,	0,	0),
(357,	14,	301,	1,	0,	0,	0,	0),
(358,	14,	174,	2,	0,	0,	0,	1),
(359,	14,	302,	2,	0,	0,	0,	1),
(360,	14,	173,	2,	0,	0,	0,	1),
(361,	12,	176,	5,	0,	0,	0,	1),
(362,	12,	181,	7,	0,	0,	0,	1),
(363,	12,	303,	7,	0,	0,	0,	1),
(364,	12,	175,	1,	0,	0,	0,	1),
(365,	12,	182,	1,	0,	0,	0,	1),
(366,	12,	304,	1,	0,	0,	0,	1),
(367,	12,	305,	1,	0,	0,	0,	1),
(368,	12,	180,	1,	0,	0,	0,	1),
(369,	12,	178,	1,	0,	0,	0,	1),
(370,	12,	306,	1,	0,	0,	0,	1),
(371,	12,	189,	1,	0,	0,	0,	1),
(372,	12,	187,	1,	0,	0,	0,	1),
(373,	12,	307,	1,	0,	0,	0,	1),
(374,	12,	308,	1,	0,	0,	0,	1),
(375,	12,	309,	1,	0,	0,	0,	1),
(376,	12,	310,	1,	0,	0,	0,	1),
(377,	12,	192,	2,	0,	0,	0,	1),
(378,	12,	193,	1,	0,	0,	0,	1),
(379,	12,	311,	1,	0,	0,	0,	1),
(380,	12,	312,	1,	0,	0,	0,	1),
(381,	15,	313,	5,	0,	0,	0,	1),
(382,	15,	314,	1,	0,	0,	0,	1),
(383,	15,	132,	1,	0,	0,	0,	1),
(384,	15,	133,	1,	0,	0,	0,	1),
(385,	15,	315,	1,	0,	0,	0,	1),
(386,	15,	316,	1,	0,	0,	0,	1),
(387,	15,	136,	1,	0,	0,	0,	1),
(388,	15,	137,	1,	0,	0,	0,	1),
(389,	15,	138,	1,	0,	0,	0,	1),
(390,	15,	139,	1,	0,	0,	0,	1),
(391,	15,	140,	1,	0,	0,	0,	1),
(392,	15,	317,	1,	0,	0,	0,	1),
(393,	15,	142,	1,	0,	0,	0,	1),
(394,	15,	143,	1,	0,	0,	0,	1),
(395,	15,	144,	1,	0,	0,	0,	1),
(396,	15,	318,	1,	0,	0,	0,	1),
(397,	15,	319,	1,	0,	0,	0,	1),
(398,	15,	147,	1,	0,	0,	0,	1),
(399,	15,	149,	1,	0,	0,	0,	1),
(400,	15,	45,	1,	0,	0,	0,	1),
(401,	15,	268,	1,	0,	0,	0,	1),
(402,	15,	320,	1,	0,	0,	0,	1),
(403,	15,	226,	1,	0,	0,	0,	0),
(404,	15,	300,	1,	0,	0,	0,	0),
(405,	15,	51,	1,	0,	0,	0,	0),
(406,	15,	52,	1,	0,	0,	0,	0),
(407,	15,	53,	1,	0,	0,	0,	0),
(408,	15,	321,	2,	0,	0,	0,	1),
(409,	15,	55,	2,	0,	0,	0,	1),
(410,	15,	151,	2,	0,	0,	0,	1),
(411,	16,	322,	5,	0,	0,	0,	1),
(412,	16,	323,	7,	0,	0,	0,	1),
(413,	16,	324,	1,	0,	0,	0,	1),
(414,	16,	325,	1,	0,	0,	0,	1),
(415,	16,	326,	1,	0,	0,	0,	1),
(416,	16,	327,	1,	0,	0,	0,	1),
(417,	16,	328,	1,	0,	0,	0,	1),
(418,	16,	194,	1,	0,	0,	0,	1),
(419,	16,	126,	1,	0,	0,	0,	1),
(420,	16,	118,	1,	0,	0,	0,	1),
(421,	16,	329,	1,	0,	0,	0,	1),
(422,	16,	196,	1,	0,	0,	0,	1),
(423,	16,	330,	1,	0,	0,	0,	1),
(424,	16,	199,	1,	0,	0,	0,	1),
(425,	16,	129,	1,	0,	0,	0,	1),
(426,	16,	200,	1,	0,	0,	0,	1),
(427,	16,	205,	1,	0,	0,	0,	1),
(428,	16,	203,	1,	0,	0,	0,	1),
(429,	16,	331,	1,	0,	0,	0,	1),
(430,	16,	226,	1,	0,	0,	0,	0),
(431,	16,	300,	1,	0,	0,	0,	0),
(432,	16,	51,	1,	0,	0,	0,	0),
(433,	16,	52,	1,	0,	0,	0,	0),
(434,	16,	53,	1,	0,	0,	0,	0),
(435,	16,	301,	1,	0,	0,	0,	0),
(436,	16,	332,	1,	0,	0,	0,	0),
(437,	16,	333,	2,	0,	0,	0,	1),
(438,	16,	334,	2,	0,	0,	0,	1),
(439,	16,	335,	2,	0,	0,	0,	1),
(440,	16,	288,	2,	0,	0,	0,	1),
(441,	13,	230,	5,	4,	2,	0,	1),
(442,	13,	336,	7,	4,	0,	0,	1),
(443,	13,	232,	7,	0,	0,	0,	1),
(444,	13,	233,	1,	0,	0,	0,	1),
(445,	13,	337,	1,	0,	0,	0,	1),
(446,	13,	338,	1,	0,	0,	0,	1),
(447,	13,	236,	1,	0,	0,	0,	1),
(448,	13,	237,	1,	0,	0,	0,	1),
(449,	13,	238,	1,	0,	0,	0,	1),
(450,	13,	239,	1,	0,	0,	0,	1),
(451,	13,	240,	1,	0,	0,	0,	1),
(452,	13,	241,	1,	0,	0,	0,	1),
(453,	13,	339,	1,	0,	0,	0,	1),
(454,	13,	340,	1,	0,	0,	0,	1),
(455,	13,	341,	1,	0,	0,	0,	1),
(456,	13,	342,	1,	0,	0,	0,	1),
(457,	13,	343,	1,	0,	0,	0,	1),
(458,	13,	344,	1,	0,	0,	0,	1),
(459,	13,	345,	1,	0,	0,	0,	1),
(460,	13,	346,	1,	0,	0,	0,	1),
(461,	13,	226,	1,	0,	0,	0,	0),
(462,	13,	300,	1,	0,	0,	0,	0),
(463,	13,	51,	1,	0,	0,	0,	0),
(464,	13,	52,	1,	0,	0,	0,	0),
(465,	13,	53,	1,	0,	0,	0,	0),
(466,	13,	301,	1,	0,	0,	0,	0),
(467,	13,	332,	1,	0,	0,	0,	0),
(468,	13,	54,	1,	0,	0,	0,	0),
(469,	13,	250,	2,	0,	0,	0,	1),
(471,	17,	348,	5,	0,	0,	0,	1),
(472,	17,	211,	1,	0,	0,	0,	1),
(473,	17,	212,	7,	0,	0,	0,	1),
(474,	17,	213,	1,	0,	0,	0,	1),
(475,	17,	214,	1,	0,	0,	0,	1),
(476,	17,	215,	1,	0,	0,	0,	1),
(477,	17,	349,	1,	0,	0,	0,	1),
(478,	17,	350,	1,	0,	0,	0,	1),
(479,	17,	218,	1,	0,	0,	0,	1),
(480,	17,	351,	1,	0,	0,	0,	1),
(481,	17,	352,	7,	0,	0,	0,	1),
(482,	17,	353,	1,	0,	0,	0,	1),
(483,	17,	222,	1,	0,	0,	0,	1),
(484,	17,	223,	1,	0,	0,	0,	1),
(485,	17,	224,	1,	0,	0,	0,	1),
(486,	17,	354,	1,	0,	0,	0,	1),
(487,	17,	227,	2,	0,	0,	0,	1),
(488,	17,	355,	1,	0,	0,	0,	1),
(489,	17,	356,	1,	0,	0,	0,	1),
(490,	17,	357,	2,	0,	0,	0,	1),
(491,	17,	358,	1,	0,	0,	0,	1),
(492,	17,	226,	1,	0,	0,	0,	0),
(493,	17,	300,	1,	0,	0,	0,	0),
(494,	17,	51,	1,	0,	0,	0,	0),
(495,	17,	52,	1,	0,	0,	0,	0),
(496,	17,	53,	1,	0,	0,	0,	0),
(497,	17,	301,	1,	0,	0,	0,	0),
(498,	17,	332,	1,	0,	0,	0,	0),
(499,	17,	54,	1,	0,	0,	0,	0),
(500,	17,	359,	1,	0,	0,	0,	1),
(501,	18,	360,	1,	0,	0,	0,	1),
(502,	18,	361,	1,	0,	0,	0,	1),
(503,	18,	362,	1,	0,	0,	0,	1),
(504,	18,	363,	1,	0,	0,	0,	1),
(505,	18,	364,	1,	0,	0,	0,	1),
(506,	18,	365,	1,	0,	0,	0,	1),
(507,	18,	366,	1,	0,	0,	0,	1),
(508,	18,	367,	1,	0,	0,	0,	1),
(509,	18,	368,	1,	0,	0,	0,	1),
(510,	18,	369,	5,	0,	0,	0,	1),
(511,	18,	370,	1,	0,	0,	0,	1),
(512,	18,	371,	1,	0,	0,	0,	1),
(513,	18,	372,	1,	0,	0,	0,	1),
(514,	18,	373,	1,	0,	0,	0,	1),
(515,	18,	374,	1,	0,	0,	0,	1),
(516,	18,	375,	1,	0,	0,	0,	1),
(517,	18,	376,	1,	0,	0,	0,	1),
(518,	18,	377,	1,	0,	0,	0,	1),
(519,	18,	378,	2,	0,	0,	0,	1),
(520,	18,	226,	1,	0,	0,	0,	0),
(521,	18,	300,	1,	0,	0,	0,	0),
(522,	18,	51,	1,	0,	0,	0,	0),
(523,	18,	52,	1,	0,	0,	0,	0),
(525,	18,	53,	1,	0,	0,	0,	0),
(526,	18,	301,	1,	0,	0,	0,	0),
(527,	18,	332,	1,	0,	0,	0,	0),
(528,	18,	54,	1,	0,	0,	0,	0),
(529,	18,	359,	1,	0,	0,	0,	0),
(530,	18,	379,	1,	0,	0,	0,	0),
(531,	18,	380,	1,	0,	0,	0,	0),
(533,	24,	327,	5,	0,	0,	0,	1),
(534,	24,	255,	1,	0,	0,	0,	1),
(535,	24,	382,	1,	0,	0,	0,	1),
(536,	24,	383,	1,	0,	0,	0,	1),
(537,	24,	384,	1,	0,	0,	0,	1),
(538,	24,	385,	1,	0,	0,	0,	1),
(539,	24,	386,	1,	0,	0,	0,	1),
(540,	24,	387,	1,	0,	0,	0,	1),
(541,	24,	388,	1,	0,	0,	0,	1),
(542,	24,	389,	1,	0,	0,	0,	1),
(543,	24,	390,	1,	0,	0,	0,	1),
(544,	24,	391,	1,	0,	0,	0,	1),
(545,	24,	392,	1,	0,	0,	0,	1),
(546,	24,	393,	1,	0,	0,	0,	1),
(547,	24,	394,	1,	0,	0,	0,	1),
(548,	24,	395,	1,	0,	0,	0,	1),
(549,	24,	396,	1,	0,	0,	0,	1),
(550,	24,	125,	2,	0,	0,	0,	1),
(551,	24,	226,	1,	0,	0,	0,	0),
(552,	24,	300,	1,	0,	0,	0,	0),
(553,	24,	51,	1,	0,	0,	0,	0),
(554,	24,	52,	1,	0,	0,	0,	0),
(555,	24,	53,	1,	0,	0,	0,	0),
(556,	24,	301,	1,	0,	0,	0,	0),
(557,	24,	332,	1,	0,	0,	0,	0),
(558,	24,	332,	1,	0,	0,	0,	0),
(559,	24,	54,	1,	0,	0,	0,	0),
(560,	24,	359,	1,	0,	0,	0,	0),
(561,	24,	379,	1,	0,	0,	0,	0),
(562,	24,	380,	1,	0,	0,	0,	0),
(563,	20,	57,	5,	0,	0,	0,	1),
(564,	20,	58,	1,	0,	0,	0,	1),
(565,	20,	59,	1,	0,	0,	0,	1),
(566,	20,	60,	1,	0,	0,	0,	1),
(567,	20,	61,	1,	0,	0,	0,	1),
(568,	20,	62,	1,	0,	0,	0,	1),
(569,	20,	63,	1,	0,	0,	0,	1),
(570,	20,	397,	1,	0,	0,	0,	1),
(571,	20,	65,	1,	0,	0,	0,	1),
(572,	20,	66,	1,	0,	0,	0,	1),
(573,	20,	67,	1,	0,	0,	0,	1),
(574,	20,	68,	1,	0,	0,	0,	1),
(575,	20,	69,	1,	0,	0,	0,	1),
(576,	20,	70,	1,	0,	0,	0,	1),
(577,	20,	71,	1,	0,	0,	0,	1),
(578,	20,	73,	1,	0,	0,	0,	1),
(579,	20,	398,	1,	0,	0,	0,	1),
(580,	20,	74,	1,	0,	0,	0,	1),
(581,	20,	75,	1,	0,	0,	0,	1),
(582,	20,	76,	1,	0,	0,	0,	1),
(583,	20,	77,	1,	0,	0,	0,	1),
(584,	20,	399,	1,	0,	0,	0,	1),
(585,	20,	400,	1,	0,	0,	0,	1),
(586,	20,	199,	1,	0,	0,	0,	1),
(587,	20,	401,	1,	0,	0,	0,	1),
(588,	20,	226,	1,	0,	0,	0,	0),
(589,	20,	300,	1,	0,	0,	0,	0),
(590,	20,	51,	1,	0,	0,	0,	0),
(591,	20,	78,	2,	0,	0,	0,	1),
(592,	20,	79,	2,	0,	0,	0,	1),
(593,	23,	3,	7,	0,	0,	0,	1),
(594,	23,	136,	5,	0,	0,	0,	1),
(595,	23,	5,	7,	0,	0,	0,	1),
(596,	23,	8,	1,	0,	0,	0,	1),
(597,	23,	402,	1,	0,	0,	0,	1),
(599,	23,	265,	1,	0,	0,	0,	1),
(600,	23,	9,	1,	0,	0,	0,	1),
(601,	23,	7,	1,	0,	0,	0,	1),
(602,	23,	17,	1,	0,	0,	0,	1),
(603,	23,	403,	1,	0,	0,	0,	1),
(604,	23,	404,	1,	0,	0,	0,	1),
(605,	23,	405,	1,	0,	0,	0,	1),
(606,	23,	287,	1,	0,	0,	0,	1),
(607,	23,	406,	1,	0,	0,	0,	1),
(608,	23,	407,	1,	0,	0,	0,	1),
(609,	23,	408,	1,	0,	0,	0,	1),
(610,	23,	409,	1,	0,	0,	0,	1),
(611,	23,	12,	1,	0,	0,	0,	1),
(612,	23,	410,	1,	0,	0,	0,	1),
(613,	23,	411,	1,	0,	0,	0,	1),
(614,	23,	412,	1,	0,	0,	0,	1),
(615,	23,	413,	1,	0,	0,	0,	1),
(616,	23,	414,	1,	0,	0,	0,	1),
(617,	23,	415,	1,	0,	0,	0,	1),
(618,	23,	416,	1,	0,	0,	0,	1),
(619,	23,	417,	1,	0,	0,	0,	1),
(620,	23,	418,	1,	0,	0,	0,	1),
(621,	23,	26,	2,	0,	0,	0,	1),
(622,	23,	30,	2,	0,	0,	0,	1),
(623,	23,	28,	2,	0,	0,	0,	1),
(624,	22,	80,	5,	0,	0,	0,	1),
(625,	22,	81,	1,	0,	0,	0,	1),
(626,	22,	82,	1,	0,	0,	0,	1),
(627,	22,	83,	1,	0,	0,	0,	1),
(628,	22,	84,	1,	0,	0,	0,	1),
(629,	22,	85,	1,	0,	0,	0,	1),
(630,	22,	86,	1,	0,	0,	0,	1),
(631,	22,	100,	1,	0,	0,	0,	1),
(632,	22,	88,	1,	0,	0,	0,	1),
(633,	22,	89,	1,	0,	0,	0,	1),
(634,	22,	419,	1,	0,	0,	0,	1),
(635,	22,	91,	1,	0,	0,	0,	1),
(636,	22,	92,	1,	0,	0,	0,	1),
(637,	22,	93,	1,	0,	0,	0,	1),
(638,	22,	94,	1,	0,	0,	0,	1),
(639,	22,	95,	1,	0,	0,	0,	1),
(640,	22,	96,	1,	0,	0,	0,	1),
(641,	22,	97,	1,	0,	0,	0,	1),
(642,	22,	98,	1,	0,	0,	0,	1),
(643,	22,	101,	1,	0,	0,	0,	1),
(644,	22,	232,	1,	0,	0,	0,	1),
(645,	22,	420,	1,	0,	0,	0,	1),
(646,	22,	421,	1,	0,	0,	0,	1),
(647,	22,	422,	1,	0,	0,	0,	1),
(648,	22,	423,	1,	0,	0,	0,	1),
(649,	22,	424,	1,	0,	0,	0,	1),
(650,	22,	425,	1,	0,	0,	0,	1),
(651,	22,	103,	2,	0,	0,	0,	1),
(652,	22,	104,	2,	0,	0,	0,	1),
(653,	22,	426,	2,	0,	0,	0,	1),
(654,	21,	106,	7,	0,	0,	0,	1),
(655,	21,	107,	5,	0,	0,	0,	1),
(656,	21,	323,	7,	0,	0,	0,	1),
(657,	21,	109,	1,	0,	0,	0,	1),
(658,	21,	110,	1,	0,	0,	0,	1),
(659,	21,	111,	1,	0,	0,	0,	1),
(660,	21,	427,	1,	0,	0,	0,	1),
(661,	21,	428,	1,	0,	0,	0,	1),
(662,	21,	114,	1,	0,	0,	0,	1),
(663,	21,	115,	1,	0,	0,	0,	1),
(664,	21,	116,	1,	0,	0,	0,	1),
(665,	21,	117,	1,	0,	0,	0,	1),
(666,	21,	118,	1,	0,	0,	0,	1),
(667,	21,	119,	1,	0,	0,	0,	1),
(668,	21,	120,	1,	0,	0,	0,	1),
(669,	21,	126,	1,	0,	0,	0,	1),
(670,	21,	330,	1,	0,	0,	0,	1),
(671,	21,	129,	1,	0,	0,	0,	1),
(672,	21,	429,	1,	0,	0,	0,	1),
(673,	21,	430,	1,	0,	0,	0,	1),
(674,	21,	431,	1,	0,	0,	0,	1),
(675,	21,	431,	1,	0,	0,	0,	1),
(676,	21,	432,	1,	0,	0,	0,	1),
(677,	21,	433,	1,	0,	0,	0,	1),
(678,	21,	130,	2,	0,	0,	0,	1),
(679,	21,	434,	2,	0,	0,	0,	1),
(680,	19,	31,	5,	0,	0,	0,	1),
(681,	19,	32,	1,	0,	0,	0,	1),
(682,	19,	33,	1,	0,	0,	0,	1),
(683,	19,	34,	1,	0,	0,	0,	1),
(684,	19,	35,	1,	0,	0,	0,	1),
(685,	19,	36,	1,	0,	0,	0,	1),
(686,	19,	435,	1,	0,	0,	0,	1),
(687,	19,	38,	1,	0,	0,	0,	1),
(688,	19,	39,	1,	0,	0,	0,	1),
(689,	19,	40,	1,	0,	0,	0,	1),
(690,	19,	41,	1,	0,	0,	0,	1),
(691,	19,	42,	1,	0,	0,	0,	1),
(692,	19,	45,	1,	0,	0,	0,	1),
(693,	19,	46,	1,	0,	0,	0,	1),
(694,	19,	436,	1,	0,	0,	0,	1),
(695,	19,	437,	1,	0,	0,	0,	1),
(696,	19,	163,	1,	0,	0,	0,	1),
(697,	19,	438,	1,	0,	0,	0,	1),
(698,	19,	439,	1,	0,	0,	0,	1),
(699,	19,	440,	1,	0,	0,	0,	1),
(700,	19,	441,	1,	0,	0,	0,	1),
(701,	19,	442,	1,	0,	0,	0,	1),
(702,	19,	443,	1,	0,	0,	0,	1),
(703,	19,	444,	1,	0,	0,	0,	1),
(704,	19,	445,	1,	0,	0,	0,	1),
(705,	19,	446,	1,	0,	0,	0,	1),
(706,	19,	447,	1,	0,	0,	0,	1),
(707,	19,	302,	2,	0,	0,	0,	1),
(708,	19,	55,	2,	0,	0,	0,	1),
(709,	19,	56,	2,	0,	0,	0,	1),
(710,	20,	448,	1,	0,	0,	0,	1),
(711,	20,	449,	1,	0,	0,	0,	1),
(712,	20,	450,	1,	0,	0,	0,	1),
(713,	12,	451,	1,	0,	0,	0,	1),
(714,	12,	452,	1,	0,	0,	0,	1),
(715,	12,	453,	1,	0,	0,	0,	1),
(716,	12,	454,	1,	0,	0,	0,	1),
(717,	12,	455,	1,	0,	0,	0,	1),
(718,	12,	456,	1,	0,	0,	0,	1),
(719,	12,	457,	1,	0,	0,	0,	1),
(720,	12,	458,	1,	0,	0,	0,	1),
(721,	12,	459,	1,	0,	0,	0,	1),
(722,	12,	460,	1,	0,	0,	0,	1),
(723,	24,	461,	1,	0,	0,	0,	1),
(724,	24,	462,	1,	0,	0,	0,	1),
(725,	24,	463,	1,	0,	0,	0,	1),
(726,	24,	464,	1,	0,	0,	0,	1),
(727,	24,	465,	1,	0,	0,	0,	1),
(728,	24,	466,	1,	0,	0,	0,	1),
(729,	24,	467,	1,	0,	0,	0,	1),
(730,	24,	466,	1,	0,	0,	0,	0),
(731,	24,	468,	1,	0,	0,	0,	1),
(732,	24,	469,	1,	0,	0,	0,	1),
(733,	24,	470,	1,	0,	0,	0,	1),
(734,	24,	471,	1,	0,	0,	0,	1),
(735,	24,	472,	1,	0,	0,	0,	1),
(736,	18,	473,	1,	0,	0,	0,	1),
(737,	18,	474,	1,	0,	0,	0,	1),
(738,	18,	475,	1,	0,	0,	0,	1),
(739,	18,	476,	1,	0,	0,	0,	1),
(740,	18,	477,	1,	0,	0,	0,	1),
(741,	18,	478,	1,	0,	0,	0,	1),
(742,	18,	479,	1,	0,	0,	0,	1),
(743,	18,	480,	1,	0,	0,	0,	1),
(744,	18,	481,	1,	0,	0,	0,	1),
(745,	18,	482,	1,	0,	0,	0,	1),
(746,	18,	483,	1,	0,	0,	0,	1),
(747,	18,	484,	1,	0,	0,	0,	0),
(748,	17,	485,	1,	0,	0,	0,	1),
(749,	17,	486,	1,	0,	0,	0,	1),
(750,	17,	487,	1,	0,	0,	0,	1),
(751,	17,	488,	1,	0,	0,	0,	1),
(752,	17,	489,	1,	0,	0,	0,	1),
(753,	17,	490,	1,	0,	0,	0,	1),
(754,	17,	491,	1,	0,	0,	0,	1),
(755,	17,	492,	1,	0,	0,	0,	1),
(756,	13,	226,	1,	0,	0,	0,	0),
(757,	13,	300,	1,	0,	0,	0,	0),
(758,	13,	51,	1,	0,	0,	0,	0),
(759,	13,	52,	1,	0,	0,	0,	0),
(760,	13,	53,	1,	0,	0,	0,	0),
(761,	13,	301,	1,	0,	0,	0,	0),
(762,	13,	332,	1,	0,	0,	0,	0),
(763,	13,	54,	1,	0,	0,	0,	0),
(764,	13,	493,	1,	0,	0,	0,	0),
(765,	13,	226,	1,	0,	0,	0,	1),
(766,	16,	494,	1,	0,	0,	0,	1),
(767,	16,	495,	1,	0,	0,	0,	1),
(768,	16,	496,	1,	0,	0,	0,	1),
(769,	16,	497,	1,	0,	0,	0,	1),
(770,	16,	498,	1,	0,	0,	0,	1),
(771,	16,	499,	1,	0,	0,	0,	1),
(772,	16,	500,	1,	0,	0,	0,	1),
(773,	14,	501,	1,	0,	0,	0,	1),
(774,	14,	502,	1,	0,	0,	0,	1),
(775,	14,	503,	1,	0,	0,	0,	1),
(776,	14,	504,	1,	0,	0,	0,	1),
(777,	14,	505,	1,	0,	0,	0,	1),
(778,	14,	506,	1,	0,	0,	0,	1),
(779,	15,	507,	1,	0,	0,	0,	1),
(780,	15,	508,	1,	0,	0,	0,	1),
(781,	15,	509,	1,	0,	0,	0,	1),
(782,	15,	510,	1,	0,	0,	0,	1),
(783,	15,	511,	1,	0,	0,	0,	1),
(784,	13,	512,	1,	0,	0,	0,	1),
(785,	13,	513,	1,	0,	0,	0,	1),
(786,	13,	514,	1,	0,	0,	0,	1),
(787,	13,	515,	1,	0,	0,	0,	1),
(788,	13,	516,	1,	0,	0,	0,	1),
(789,	13,	517,	1,	0,	0,	0,	1),
(790,	13,	518,	1,	0,	0,	0,	1),
(791,	13,	519,	1,	0,	0,	0,	1);

DROP TABLE IF EXISTS `player_types`;
CREATE TABLE `player_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `abbr` tinytext COLLATE utf8_slovak_ci,
  `priority` tinyint(8) NOT NULL DEFAULT '100',
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `content` text COLLATE utf8_slovak_ci NOT NULL,
  `author` tinytext COLLATE utf8_slovak_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `thumbnail` tinytext COLLATE utf8_slovak_ci,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `post_images`;
CREATE TABLE `post_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `name` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `posts_id` (`post_id`),
  CONSTRAINT `post_images_ibfk_4` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `punishments`;
CREATE TABLE `punishments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_season_group_team_id` int(11) NOT NULL,
  `content` tinytext COLLATE utf8_slovak_ci,
  `round` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `condition` tinyint(4) NOT NULL DEFAULT '0',
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `player_team_id` (`player_season_group_team_id`),
  CONSTRAINT `punishments_ibfk_1` FOREIGN KEY (`player_season_group_team_id`) REFERENCES `players_seasons_groups_teams` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `rounds`;
CREATE TABLE `rounds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `season_id` int(11) DEFAULT NULL,
  `label` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `season_id` (`season_id`),
  CONSTRAINT `rounds_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `rules`;
CREATE TABLE `rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `season_id` int(11) DEFAULT NULL,
  `content` text COLLATE utf8_slovak_ci NOT NULL,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `season_id` (`season_id`),
  CONSTRAINT `rules_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `seasons`;
CREATE TABLE `seasons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `seasons_groups`;
CREATE TABLE `seasons_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `season_id` int(11) DEFAULT NULL,
  `group_id` int(11) NOT NULL,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `season_id` (`season_id`),
  KEY `group_id` (`group_id`),
  CONSTRAINT `seasons_groups_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`) ON DELETE NO ACTION,
  CONSTRAINT `seasons_groups_ibfk_2` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `seasons_groups_teams`;
CREATE TABLE `seasons_groups_teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `season_group_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `season_group_id` (`season_group_id`),
  KEY `team_id` (`team_id`),
  CONSTRAINT `seasons_groups_teams_ibfk_1` FOREIGN KEY (`season_group_id`) REFERENCES `seasons_groups` (`id`) ON DELETE NO ACTION,
  CONSTRAINT `seasons_groups_teams_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `sponsors`;
CREATE TABLE `sponsors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `url` tinytext COLLATE utf8_slovak_ci,
  `image` tinytext COLLATE utf8_slovak_ci,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `tables`;
CREATE TABLE `tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_type_id` int(11) NOT NULL,
  `season_group_id` int(11) NOT NULL,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  `is_visible` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `table_type_id` (`table_type_id`),
  KEY `season_group_id` (`season_group_id`),
  CONSTRAINT `tables_ibfk_3` FOREIGN KEY (`table_type_id`) REFERENCES `table_types` (`id`),
  CONSTRAINT `tables_ibfk_4` FOREIGN KEY (`season_group_id`) REFERENCES `seasons_groups` (`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `table_entries`;
CREATE TABLE `table_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `table_id` int(11) NOT NULL,
  `counter` int(11) NOT NULL DEFAULT '0',
  `win` int(11) NOT NULL DEFAULT '0',
  `tram` int(11) NOT NULL DEFAULT '0',
  `lost` int(11) NOT NULL DEFAULT '0',
  `score1` int(11) NOT NULL DEFAULT '0',
  `score2` int(11) NOT NULL DEFAULT '0',
  `points` int(11) NOT NULL DEFAULT '0',
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`),
  KEY `table_id` (`table_id`),
  CONSTRAINT `table_entries_ibfk_4` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE NO ACTION,
  CONSTRAINT `table_entries_ibfk_5` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `table_types`;
CREATE TABLE `table_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `teams`;
CREATE TABLE `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_slovak_ci NOT NULL,
  `photo` varchar(255) COLLATE utf8_slovak_ci DEFAULT NULL,
  `logo` varchar(255) COLLATE utf8_slovak_ci DEFAULT NULL,
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `username` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `password` tinytext COLLATE utf8_slovak_ci NOT NULL,
  `role` enum('admin') COLLATE utf8_slovak_ci NOT NULL DEFAULT 'admin',
  `is_present` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_slovak_ci;


-- 2020-09-18 14:15:54