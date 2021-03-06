
-- --------------------------------------------------------

--
-- Структура таблицы `proto_stats_moth`
--
-- Создание: Сен 16 2019 г., 22:18
--

DROP TABLE IF EXISTS `proto_stats_moth`;
CREATE TABLE `proto_stats_moth` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `host` bigint(20) UNSIGNED NOT NULL,
  `hit` bigint(20) UNSIGNED NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `proto_stats_moth`
--

INSERT INTO `proto_stats_moth` (`id`, `host`, `hit`, `date`) VALUES
(1, 540, 1471, '2011-12-05 11:05:00'),
(2, 643, 1752, '2011-12-31 20:13:42'),
(3, 992, 2923, '2012-01-31 20:10:09'),
(4, 670, 3026, '2012-02-29 20:18:19'),
(5, 205, 680, '2012-03-31 21:04:39'),
(6, 592, 1356, '2012-04-30 21:27:30'),
(7, 494, 1352, '2012-05-31 20:08:37'),
(8, 491, 1416, '2012-06-30 20:38:25'),
(9, 478, 1348, '2012-07-31 21:21:16'),
(10, 469, 1381, '2012-08-31 20:05:54'),
(11, 11, 47, '2012-09-30 20:20:08'),
(12, 438, 4138, '2013-01-05 22:50:30'),
(13, 532, 1973, '2013-01-31 20:48:44'),
(14, 470, 1736, '2013-02-28 22:49:06'),
(15, 434, 1502, '2013-03-31 22:01:09'),
(16, 390, 1272, '2013-04-30 22:50:14'),
(17, 426, 1516, '2013-05-31 20:02:00'),
(18, 391, 1690, '2013-06-30 20:26:28'),
(19, 404, 1323, '2013-07-31 20:04:21'),
(20, 303, 1561, '2013-08-31 22:44:37'),
(21, 401, 4325, '2013-09-30 20:18:28'),
(22, 339, 9643, '2013-10-31 20:23:22'),
(23, 346, 2811, '2013-12-01 06:20:13'),
(24, 649, 3828, '2013-12-31 21:20:30'),
(25, 1355, 7951, '2014-01-31 20:18:21'),
(26, 1272, 7452, '2014-02-28 20:18:56'),
(27, 522, 4402, '2014-03-31 20:16:26'),
(28, 482, 3249, '2014-04-30 20:01:02'),
(29, 430, 1814, '2014-05-31 20:09:48'),
(30, 439, 1866, '2014-06-30 21:36:48'),
(31, 326, 1905, '2014-07-31 20:50:11'),
(32, 330, 1563, '2014-08-31 20:56:58'),
(33, 314, 6495, '2014-09-30 22:04:52'),
(34, 382, 2049, '2014-10-31 22:00:22'),
(35, 359, 2066, '2014-11-30 22:27:26'),
(36, 1, 1, '2014-11-30 22:27:26'),
(37, 394, 9989, '2014-12-31 21:07:27'),
(38, 452, 6416, '2015-01-31 21:03:50'),
(39, 433, 5070, '2015-02-28 21:13:27');
