SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `authors`;
CREATE TABLE `authors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `web` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime,
  `deleted_at` datetime,
  PRIMARY KEY (`id`)
);

INSERT INTO `authors` (`id`, `name`, `surname`, `web`, `email`, `password`, `created_at`) VALUES
(1, 'John', 'Smith', 'http://www.smith.com/', 'john@smith.com', '123456', '2017-10-20 13:35:02'),
(2, 'Martin', 'Graham', 'http://www.martin-graham.com/', 'books@martin-graham.com', '123456', '2017-10-20 13:35:02'),
(3, 'Karoline', 'Lebenkosten', 'http://www.lebenkosten.de', 'lk@lebenkosten.de', '123456', '2017-10-20 13:35:02'),
(4, 'Hans', 'Tot', 'http://www.tot.de', 'hans@tot.de', '123456', '2017-10-20 13:35:02');

UPDATE authors SET deleted_at = NOW() WHERE id = 4;

DROP TABLE IF EXISTS `books`;
CREATE TABLE `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `author_id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `written` date DEFAULT NULL,
  `available` tinyint(1) unsigned NOT NULL,
  `price` decimal(16,4) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime,
  `deleted_at` datetime,
  PRIMARY KEY (`id`)
  -- CONSTRAINT `book_author` FOREIGN KEY (`author_id`) REFERENCES `author` (`id`)
);

INSERT INTO `books` (`id`, `author_id`, `title`, `written`, `available`, `price`, `created_at`) VALUES
(1, 1, 'PHP Tips & Tricks', '2010-01-01', 0, 50, NOW()),
(2, 1, 'MySQL Queries', '2007-01-01', 1, 80, NOW()),
(3, 3, 'Einfach JavaScript', '2004-01-01', 1, 30, NOW()),
(4, 2, 'Web programming', '2005-01-01', 1, 10, NOW()),
(5, 2, 'Oracle', '2004-01-01', 1, 50, NOW());

INSERT INTO `books` (`id`, `author_id`, `title`, `written`, `available`, `price`, `created_at`, `updated_at`, `deleted_at`) VALUES
(6, 1, 'Death Code', '2012-05-12', 0, 30, NOW(), NULL, NOW());

-- UPDATE books SET deleted_at = NOW() WHERE id = 5;

DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `tags` (`id`, `name`) VALUES
(1,	'JavaScript'),
(2,	'MySQL'),
(3,	'XML'),
(4,	'PHP');

DROP TABLE IF EXISTS `book_tags`;
CREATE TABLE `book_tags` (
  `book_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`book_id`,`tag_id`)
);

INSERT INTO `book_tags` (`book_id`, `tag_id`) VALUES
(1, 2),
(1, 3),
(1, 4),
(2, 2),
(3, 1),
(4, 1),
(4, 2),
(4, 3),
(4, 4),
(6, 1),
(6, 4);

DROP TABLE IF EXISTS `no_primary_table`;
CREATE TABLE `no_primary_table` (
  `foo` varchar(255) NOT NULL,
  `barr` varchar(255) NOT NULL,
  `wee` varchar(255) NOT NULL
);
