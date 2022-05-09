
-- --------------------------------------------------------

--
-- Структура таблицы `proto_stats_searcher_site`
--
-- Создание: Сен 16 2019 г., 22:18
-- Последнее обновление: Сен 16 2019 г., 22:18
--

DROP TABLE IF EXISTS `proto_stats_searcher_site`;
CREATE TABLE `proto_stats_searcher_site` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `url` varchar(258) NOT NULL,
  `pattern` varchar(64) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `proto_stats_searcher_site`
--

INSERT INTO `proto_stats_searcher_site` (`id`, `url`, `pattern`) VALUES
(1, 'yandex.ru', '/text=([^&]+)/i'),
(2, 'mail.ru', '/q=([^&]+)/i'),
(3, 'gogo.ru', '/q=([^&]+)/i'),
(4, 'rambler.ru', '/r=([^&]+)/i'),
(5, 'aport.ru', '/r=([^&]+)/i'),
(6, 'google.ru', '/[^a]q=([^&]+)/i'),
(7, 'msn.com', '/q=([^&]+)/i'),
(8, 'live.com', '/q=([^&]+)/i'),
(9, 'yahoo.com', '/p=([^&]+)/i'),
(10, 'nigma.ru', '/s=([^&]+)/i'),
(11, 'bing.com', '/q=([^&]+)/i'),
(12, 'kngine.com', '/q=([^&]+)/i'),
(13, 'go.mail.ru', '/q=([^&]+)/i'),
(14, 'yandex.ua', '/text=([^&]+)/i'),
(15, 'yandex.kz', '/text=([^&]+)/i'),
(16, 'yandex.by', '/text=([^&]+)/i'),
(17, 'google.com.ua', '/[^a]q=([^&]+)/i'),
(18, 'google.com', '/[^a]q=([^&]+)/i');
