-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 16 juin 2025 à 14:07
-- Version du serveur : 8.0.27
-- Version de PHP : 7.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `tripadvisor`
--

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

DROP TABLE IF EXISTS `avis`;
CREATE TABLE IF NOT EXISTS `avis` (
  `id_avis` int NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `commentaire` varchar(50) NOT NULL,
  `id_restaurant` int NOT NULL,
  `id_client` int NOT NULL,
  PRIMARY KEY (`id_avis`),
  KEY `avis_restaurants_FK` (`id_restaurant`),
  KEY `avis_clients0_FK` (`id_client`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`id_avis`, `date`, `commentaire`, `id_restaurant`, `id_client`) VALUES
(1, '2025-02-10', 'Excellente cuisine !', 1, 1),
(2, '2025-02-12', 'Service impeccable.', 1, 2),
(3, '2025-02-15', 'Pizza parfaite !', 2, 3),
(4, '2025-02-18', 'Ambiance agréable.', 2, 4),
(5, '2025-02-20', 'Sushis très frais.', 3, 5),
(6, '2025-02-22', 'Le personnel est adorable.', 3, 1),
(7, '2025-02-25', 'Tacos super bons.', 4, 2),
(8, '2025-02-27', 'Un délice épicé !', 5, 3),
(9, '2025-02-28', 'Bonne expérience.', 6, 4),
(10, '2025-03-01', 'Viande de qualité.', 7, 5),
(11, '2025-03-02', 'Service trop lent.', 1, 3),
(12, '2025-03-03', 'Pâtes sans saveur.', 2, 5),
(13, '2025-03-04', 'Sushis trop chers.', 3, 2),
(14, '2025-03-05', 'Attente interminable.', 4, 1),
(15, '2025-03-06', 'Trop épicé, immangeable.', 5, 4),
(16, '2025-03-07', 'Tables trop serrées.', 6, 1),
(17, '2025-03-08', 'Viande trop cuite.', 7, 3),
(18, '2025-03-09', 'Burger froid et sec.', 8, 6),
(19, '2025-03-10', 'Trop cher pour la qualité.', 9, 7);

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `id_client` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `mail` varchar(50) NOT NULL,
  PRIMARY KEY (`id_client`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `clients`
--

INSERT INTO `clients` (`id_client`, `nom`, `prenom`, `mail`) VALUES
(1, 'Bernard', 'Claire', 'claire@mail.com'),
(2, 'Robert', 'Luc', 'luc@mail.com'),
(3, 'Garcia', 'Elena', 'elena@mail.com'),
(4, 'Fischer', 'Hans', 'hans@mail.com'),
(5, 'Nguyen', 'Linh', 'linh@mail.com'),
(6, 'Smith', 'John', 'john@mail.com'),
(7, 'Kim', 'Soo', 'soo@mail.com');

-- --------------------------------------------------------

--
-- Structure de la table `emplois`
--

DROP TABLE IF EXISTS `emplois`;
CREATE TABLE IF NOT EXISTS `emplois` (
  `id_restaurant` int NOT NULL,
  `id_employe` int NOT NULL,
  PRIMARY KEY (`id_restaurant`,`id_employe`),
  KEY `emplois_employes0_FK` (`id_employe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `emplois`
--

INSERT INTO `emplois` (`id_restaurant`, `id_employe`) VALUES
(1, 1),
(7, 1),
(1, 2),
(2, 2),
(6, 2),
(1, 3),
(3, 3),
(7, 3),
(2, 4),
(3, 5),
(9, 5),
(4, 6),
(9, 6),
(5, 7),
(6, 8),
(8, 9),
(8, 10);

-- --------------------------------------------------------

--
-- Structure de la table `employes`
--

DROP TABLE IF EXISTS `employes`;
CREATE TABLE IF NOT EXISTS `employes` (
  `id_employe` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `id_poste` int NOT NULL,
  PRIMARY KEY (`id_employe`),
  KEY `employes_postes_FK` (`id_poste`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `employes`
--

INSERT INTO `employes` (`id_employe`, `nom`, `prenom`, `id_poste`) VALUES
(1, 'Dupont', 'Jean', 1),
(2, 'Martin', 'Sophie', 2),
(3, 'Leroy', 'Paul', 3),
(4, 'Rossi', 'Marco', 1),
(5, 'Tanaka', 'Yuki', 1),
(6, 'Lopez', 'Carlos', 2),
(7, 'Sharma', 'Anika', 3),
(8, 'Durand', 'Alice', 4),
(9, 'Johnson', 'Mike', 5),
(10, 'Wong', 'Lisa', 6);

-- --------------------------------------------------------

--
-- Structure de la table `origines`
--

DROP TABLE IF EXISTS `origines`;
CREATE TABLE IF NOT EXISTS `origines` (
  `id_origine` int NOT NULL AUTO_INCREMENT,
  `pays` varchar(50) NOT NULL,
  PRIMARY KEY (`id_origine`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `origines`
--

INSERT INTO `origines` (`id_origine`, `pays`) VALUES
(1, 'France'),
(2, 'Italie'),
(3, 'Japon'),
(4, 'Mexique'),
(5, 'Inde'),
(6, 'États-Unis'),
(7, 'Thaïlande');

-- --------------------------------------------------------

--
-- Structure de la table `postes`
--

DROP TABLE IF EXISTS `postes`;
CREATE TABLE IF NOT EXISTS `postes` (
  `id_poste` int NOT NULL AUTO_INCREMENT,
  `intitule` varchar(50) NOT NULL,
  PRIMARY KEY (`id_poste`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `postes`
--

INSERT INTO `postes` (`id_poste`, `intitule`) VALUES
(1, 'Chef cuisinier'),
(2, 'Serveur'),
(3, 'Barman'),
(4, 'Manager'),
(5, 'Plongeur'),
(6, 'Pâtissier');

-- --------------------------------------------------------

--
-- Structure de la table `restaurants`
--

DROP TABLE IF EXISTS `restaurants`;
CREATE TABLE IF NOT EXISTS `restaurants` (
  `id_restaurant` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `id_origine` int NOT NULL,
  PRIMARY KEY (`id_restaurant`),
  KEY `restaurants_origines_FK` (`id_origine`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `restaurants`
--

INSERT INTO `restaurants` (`id_restaurant`, `nom`, `id_origine`) VALUES
(1, 'Le Gourmet', 1),
(2, 'Pasta Bella', 2),
(3, 'Sakura Sushi', 3),
(4, 'El Tacon', 4),
(5, 'Spicy Masala', 5),
(6, 'Le Bistrot Lyonnais', 1),
(7, 'Steak House', 6),
(8, 'Green Eat', 4),
(9, 'Bangkok Wok', 7);

-- --------------------------------------------------------

--
-- Structure de la table `types`
--

DROP TABLE IF EXISTS `types`;
CREATE TABLE IF NOT EXISTS `types` (
  `id_type` int NOT NULL AUTO_INCREMENT,
  `intitule` varchar(50) NOT NULL,
  PRIMARY KEY (`id_type`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `types`
--

INSERT INTO `types` (`id_type`, `intitule`) VALUES
(1, 'Gastronomique'),
(2, 'Pizzeria'),
(3, 'Sushi'),
(4, 'Tacos'),
(5, 'Curry'),
(6, 'Bistrot'),
(7, 'Grillades'),
(8, 'Fast Food'),
(9, 'Vegan');

-- --------------------------------------------------------

--
-- Structure de la table `types_restaurants`
--

DROP TABLE IF EXISTS `types_restaurants`;
CREATE TABLE IF NOT EXISTS `types_restaurants` (
  `id_type` int NOT NULL,
  `id_restaurant` int NOT NULL,
  PRIMARY KEY (`id_type`,`id_restaurant`),
  KEY `possede_restaurants0_FK` (`id_restaurant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `types_restaurants`
--

INSERT INTO `types_restaurants` (`id_type`, `id_restaurant`) VALUES
(1, 1),
(6, 1),
(2, 2),
(6, 2),
(3, 3),
(4, 4),
(6, 4),
(5, 5),
(6, 6),
(7, 7),
(8, 8),
(9, 9);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `avis_clients0_FK` FOREIGN KEY (`id_client`) REFERENCES `clients` (`id_client`),
  ADD CONSTRAINT `avis_restaurants_FK` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurants` (`id_restaurant`);

--
-- Contraintes pour la table `emplois`
--
ALTER TABLE `emplois`
  ADD CONSTRAINT `emplois_employes0_FK` FOREIGN KEY (`id_employe`) REFERENCES `employes` (`id_employe`),
  ADD CONSTRAINT `emplois_restaurants_FK` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurants` (`id_restaurant`);

--
-- Contraintes pour la table `employes`
--
ALTER TABLE `employes`
  ADD CONSTRAINT `employes_postes_FK` FOREIGN KEY (`id_poste`) REFERENCES `postes` (`id_poste`);

--
-- Contraintes pour la table `restaurants`
--
ALTER TABLE `restaurants`
  ADD CONSTRAINT `restaurants_origines_FK` FOREIGN KEY (`id_origine`) REFERENCES `origines` (`id_origine`);

--
-- Contraintes pour la table `types_restaurants`
--
ALTER TABLE `types_restaurants`
  ADD CONSTRAINT `possede_restaurants0_FK` FOREIGN KEY (`id_restaurant`) REFERENCES `restaurants` (`id_restaurant`),
  ADD CONSTRAINT `possede_types_FK` FOREIGN KEY (`id_type`) REFERENCES `types` (`id_type`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
