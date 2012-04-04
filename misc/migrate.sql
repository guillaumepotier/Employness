-- sql Migrate
-- Add categories

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
-- Add column `employness_categories`
--

ALTER TABLE `employness_users`
  ADD COLUMN `category_id` mediumint(9),
  ADD CONSTRAINT `category_id_fk` FOREIGN KEY (`category_id`) REFERENCES `employness_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
