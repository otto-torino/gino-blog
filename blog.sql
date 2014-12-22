
-- --------------------------------------------------------
-- PERMISSIONS --

INSERT INTO `auth_permission` (`class`, `code`, `label`, `description`, `admin`) VALUES
('blog', 'can_admin', 'Amministrazione post', 'gestione completa dei post', 1),
('blog', 'can_publish', 'Pubblicazione post', 'pubblicazione dei contenuti', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `blog_entry`
--

CREATE TABLE `blog_entry` (
  `id` int(11) NOT NULL,
  `instance` int(11) NOT NULL,
  `author` int(11) NOT NULL,
  `creation_date` datetime NOT NULL,
  `last_edit_date` datetime NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `image` varchar(200) DEFAULT NULL,
  `text` text NOT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `enable_comments` int(1) NOT NULL,
  `published` int(1) NOT NULL,
  `num_read` int(11) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Indexes for table `blog_entry`
--
ALTER TABLE `blog_entry`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `slug` (`slug`);

--
-- AUTO_INCREMENT for table `blog_entry`
--
ALTER TABLE `blog_entry`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Struttura della tabella `blog_opt`
--

CREATE TABLE `blog_opt` (
  `id` int(11) NOT NULL,
  `instance` int(11) NOT NULL,
  `last_number` smallint(2) NOT NULL,
  `list_ifp` smallint(3) NOT NULL,
  `showcase_number` smallint(3) NOT NULL,
  `showcase_auto_start` tinyint(1) NOT NULL,
  `showcase_auto_interval` int(8) NOT NULL,
  `newsletter_entries_number` smallint(2) NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

--
-- Indexes for table `blog_opt`
--
ALTER TABLE `blog_opt`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `blog_opt`
--
ALTER TABLE `blog_opt`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
