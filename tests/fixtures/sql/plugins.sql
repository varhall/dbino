SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `visitors`;
CREATE TABLE `visitors` (
  `id` char(36) NOT NULL,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO `visitors` (`id`, `name`) VALUES
('02a497ca-62a9-424e-a08e-07b1504d8317', 'Winkler'),
('03b27216-41a7-48da-86b7-0838b21e794d', 'Hermann'),
('1056d9a0-7979-4ae7-ad87-a5cf8dee52b3', 'Kohl'),
('19cc7f48-cc66-409a-85ec-18b0ade4c50a', 'Fruhauf'),
('1eb4c793-0ca1-4380-96a8-1b16c602516b', 'Seelemann'),
('205cc131-34eb-4de6-b763-37b0074aedb8', 'Neubauer'),
('2213b355-4512-485f-aff4-f29f3fe8db59', 'Pilz'),
('25639b84-b3fc-4e4c-92c4-8180c8933b62', 'Berger');


DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `parent` int(11) NULL,
    `lpos` int(11) NOT NULL,
    `rpos` int(11) NOT NULL,
    `name` varchar(50) NOT NULL,
    PRIMARY KEY (`id`)
);

INSERT INTO `categories` (`id`, `parent`, `lpos`, `rpos`, `name`) VALUES
(1, NULL, 1, 14, 'Computers'),
(2, 1, 2, 3, 'RAM'),
(3, 1, 4, 11, 'Drives'),
(4, 3, 9, 10, 'HDD'),
(5, 3, 7, 8, 'SSD'),
(6, 1, 12, 13, 'Processors'),
(7, NULL, 15, 16, 'Tablets'),
(8, NULL, 17, 22, 'Phones'),
(9, 8, 18, 19, 'Android'),
(10, 8, 20, 21, 'iOS'),
(11, 3, 5, 6, 'M.2');


DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(20) NOT NULL,
    `availability` varchar(20) NOT NULL,
    `published` TINYINT NOT NULL,
    `info` text NOT NULL,
    PRIMARY KEY (`id`)
);

INSERT INTO `products` (`id`, `name`, `availability`, `published`, `info`) VALUES
(1,	'iPhone 12', 'stocked', 1, '{"condition":"new","identifier":"IP12","warranty":24}'),
(2,	'Macbook Air', 'stocked', 1, '{"condition":"new","identifier":"MA2021","warranty":24}'),
(3,	'Samsung Galaxy S20', 'stocked', 0, '{"condition":"used","identifier":"SG20","warranty":24}'),
(4,	'Samsung Galaxy S20 FE', 'unknown', 0, '[]'),
(5,	'Samsung Galaxy S20 SE', 'unknown', 0, '{"condition": "used"}');
