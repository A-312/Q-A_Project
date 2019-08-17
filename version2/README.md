```sql
CREATE DATABASE IF NOT EXISTS `sdd` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `sdd`;

-- --------------------------------------------------------

--
-- Structure de la table `membre`
--

CREATE TABLE `membre` (
  `idmembre` int(10) UNSIGNED NOT NULL,
  `pseudo` varchar(20) CHARACTER SET utf8 NOT NULL,
  `reputation` int(10) UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
  `idmessage` int(10) UNSIGNED NOT NULL,
  `idquestion` int(10) UNSIGNED NOT NULL,
  `texte` text CHARACTER SET utf8 NOT NULL,
  `type` enum('question','reponse') CHARACTER SET utf8 NOT NULL,
  `idmembre` int(10) UNSIGNED NOT NULL,
  `dateenvois` int(10) UNSIGNED NOT NULL,
  `dateedition` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `msgcomment`
--

CREATE TABLE `msgcomment` (
  `idcommentaire` int(11) NOT NULL,
  `idmessage` int(10) UNSIGNED NOT NULL,
  `texte` text CHARACTER SET utf8 NOT NULL,
  `idmembre` int(10) UNSIGNED NOT NULL,
  `dateenvois` int(10) UNSIGNED NOT NULL,
  `dateedition` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `msgvote`
--

CREATE TABLE `msgvote` (
  `idmessage` int(10) UNSIGNED NOT NULL,
  `idmembre` int(10) UNSIGNED NOT NULL,
  `valeur` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `question`
--

CREATE TABLE `question` (
  `idquestion` int(10) UNSIGNED NOT NULL,
  `question` text CHARACTER SET utf8 NOT NULL,
  `vue` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `categorie` enum('question','news','projet','refwiki','redaction') CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `questsuivi`
--

CREATE TABLE `questsuivi` (
  `idquestion` int(10) UNSIGNED NOT NULL,
  `idmembre` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `questtag`
--

CREATE TABLE `questtag` (
  `idquestion` int(10) UNSIGNED NOT NULL,
  `idtag` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `session`
--

CREATE TABLE `session` (
  `sess_id` varchar(255) CHARACTER SET utf8 NOT NULL,
  `sess_data` text CHARACTER SET utf8 NOT NULL,
  `sess_time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `tag`
--

CREATE TABLE `tag` (
  `idtag` int(10) UNSIGNED NOT NULL,
  `tag` varchar(30) CHARACTER SET utf8 NOT NULL,
  `description` text CHARACTER SET utf8 NOT NULL,
  `texte` text CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `membre`
--
ALTER TABLE `membre`
  ADD PRIMARY KEY (`idmembre`),
  ADD UNIQUE KEY `pseudo` (`pseudo`),
  ADD UNIQUE KEY `pseudo_2` (`pseudo`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`idmessage`),
  ADD KEY `idquestion` (`idquestion`),
  ADD KEY `idmembre` (`idmembre`);

--
-- Index pour la table `msgcomment`
--
ALTER TABLE `msgcomment`
  ADD PRIMARY KEY (`idcommentaire`),
  ADD KEY `idmessage` (`idmessage`),
  ADD KEY `idmembre` (`idmembre`);

--
-- Index pour la table `msgvote`
--
ALTER TABLE `msgvote`
  ADD KEY `idmessage` (`idmessage`),
  ADD KEY `idmembre` (`idmembre`);

--
-- Index pour la table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`idquestion`),
  ADD KEY `categorie` (`categorie`);

--
-- Index pour la table `questsuivi`
--
ALTER TABLE `questsuivi`
  ADD KEY `idquestion` (`idquestion`),
  ADD KEY `idmembre` (`idmembre`);

--
-- Index pour la table `questtag`
--
ALTER TABLE `questtag`
  ADD UNIQUE KEY `idquestion+idtag` (`idquestion`,`idtag`),
  ADD KEY `idquestion` (`idquestion`),
  ADD KEY `idtag` (`idtag`);

--
-- Index pour la table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`sess_id`);

--
-- Index pour la table `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`idtag`),
  ADD UNIQUE KEY `tag` (`tag`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `membre`
--
ALTER TABLE `membre`
  MODIFY `idmembre` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
  MODIFY `idmessage` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT pour la table `msgcomment`
--
ALTER TABLE `msgcomment`
  MODIFY `idcommentaire` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT pour la table `question`
--
ALTER TABLE `question`
  MODIFY `idquestion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT pour la table `tag`
--
ALTER TABLE `tag`
  MODIFY `idtag` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`idquestion`) REFERENCES `question` (`idquestion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`idmembre`) REFERENCES `membre` (`idmembre`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `message_ibfkm_1` FOREIGN KEY (`idquestion`) REFERENCES `question` (`idquestion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `message_ibfkm_2` FOREIGN KEY (`idmembre`) REFERENCES `membre` (`idmembre`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `msgcomment`
--
ALTER TABLE `msgcomment`
  ADD CONSTRAINT `msgcomment_ibfk_2` FOREIGN KEY (`idmembre`) REFERENCES `membre` (`idmembre`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `msgcomment_ibfk_3` FOREIGN KEY (`idmessage`) REFERENCES `message` (`idmessage`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `msgcomment_ibfkm_2` FOREIGN KEY (`idmembre`) REFERENCES `membre` (`idmembre`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `msgcomment_ibfkm_3` FOREIGN KEY (`idmessage`) REFERENCES `message` (`idmessage`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `msgvote`
--
ALTER TABLE `msgvote`
  ADD CONSTRAINT `msgvote_ibfk_1` FOREIGN KEY (`idmessage`) REFERENCES `message` (`idmessage`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `msgvote_ibfk_2` FOREIGN KEY (`idmembre`) REFERENCES `membre` (`idmembre`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `msgvote_ibfkm_1` FOREIGN KEY (`idmessage`) REFERENCES `message` (`idmessage`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `msgvote_ibfkm_2` FOREIGN KEY (`idmembre`) REFERENCES `membre` (`idmembre`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `questsuivi`
--
ALTER TABLE `questsuivi`
  ADD CONSTRAINT `questsuivi_ibfk_1` FOREIGN KEY (`idquestion`) REFERENCES `question` (`idquestion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `questsuivi_ibfk_2` FOREIGN KEY (`idmembre`) REFERENCES `membre` (`idmembre`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `questsuivi_ibfkm_1` FOREIGN KEY (`idquestion`) REFERENCES `question` (`idquestion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `questsuivi_ibfkm_2` FOREIGN KEY (`idmembre`) REFERENCES `membre` (`idmembre`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `questtag`
--
ALTER TABLE `questtag`
  ADD CONSTRAINT `questtag_ibfk_1` FOREIGN KEY (`idquestion`) REFERENCES `question` (`idquestion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `questtag_ibfk_2` FOREIGN KEY (`idtag`) REFERENCES `tag` (`idtag`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `questtag_ibfkm_1` FOREIGN KEY (`idquestion`) REFERENCES `question` (`idquestion`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `questtag_ibfkm_2` FOREIGN KEY (`idtag`) REFERENCES `tag` (`idtag`) ON DELETE CASCADE ON UPDATE CASCADE;
```