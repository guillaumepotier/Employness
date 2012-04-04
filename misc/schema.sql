-- phpMyAdmin SQL Dump
-- version 3.3.9.2
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Mar 20 Mars 2012 à 14:26
-- Version du serveur: 5.5.9
-- Version de PHP: 5.3.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de données: `employness_prod`
--

-- --------------------------------------------------------

--
-- Structure de la table `employness_days`
--

DROP TABLE IF EXISTS `employness_days`;
CREATE TABLE IF NOT EXISTS `employness_days` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `day` date NOT NULL,
  `karma` tinyint(2) NOT NULL DEFAULT '0',
  `participants` longtext COLLATE utf8_bin COMMENT 'DC2Type:array',
  PRIMARY KEY (`id`),
  UNIQUE KEY `day` (`day`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `employness_karma`
--

DROP TABLE IF EXISTS `employness_karma`;
CREATE TABLE IF NOT EXISTS `employness_karma` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(9) NOT NULL,
  `day_id` mediumint(9) NOT NULL,
  `karma` tinyint(2) NOT NULL DEFAULT '0',
  `anonymous` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_days` (`user_id`,`day_id`),
  KEY `day_id_fk` (`day_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `employness_users`
--

DROP TABLE IF EXISTS `employness_users`;
CREATE TABLE IF NOT EXISTS `employness_users` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `email` varchar(50) COLLATE utf8_bin NOT NULL,
  `password` varchar(50) COLLATE utf8_bin NOT NULL,
  `token` varchar(50) COLLATE utf8_bin DEFAULT NULL,
  `admin` tinyint(4) NOT NULL DEFAULT '0',
  `evaluated_days` mediumint(6) NOT NULL DEFAULT '0',
  `karma` mediumint(6) NOT NULL DEFAULT '0',
  `category_id` mediumint(9),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `employness_categories`
--

DROP TABLE IF EXISTS `employness_categories`;
CREATE TABLE IF NOT EXISTS `employness_categories` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin AUTO_INCREMENT=1 ;

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `employness_karma`
--
ALTER TABLE `employness_karma`
  ADD CONSTRAINT `day_id_fk` FOREIGN KEY (`day_id`) REFERENCES `employness_days` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_id_fk` FOREIGN KEY (`user_id`) REFERENCES `employness_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `employness_users`
    ADD CONSTRAINT `category_id_fk` FOREIGN KEY (`category_id`) REFERENCES `employness_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
  