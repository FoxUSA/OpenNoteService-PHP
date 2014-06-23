-- Change upload id to varchar
	ALTER TABLE `uploads` CHANGE `id` `id` VARCHAR( 128 ) NOT NULL ;

-- Create token table
	CREATE TABLE IF NOT EXISTS `token` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `userID` int(11) NOT NULL,
	  `ip` varchar(256) NOT NULL,
	  `token` varchar(256) NOT NULL,
	  `issued` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `expires` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `token` (`token`),
	  KEY `userID` (`userID`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=176 ;

	ALTER TABLE `token`
		ADD CONSTRAINT `token_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
		
-- Remove old columns
	ALTER TABLE `users` DROP `lastLoginIP`; -- This data can now be found in the token table
	ALTER TABLE `users` DROP `lastLoginTime`;
	
	
--Escape html
	UPDATE note
		SET note = REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(note, ">", "&gt;"), "<", "&lt;"), "&", "&amp;"), '"', "&quot;"), "'", "&apos;");

	
	
/*

-- Original Direct access
UPDATE note
	SET note = REPLACE(note,"http://domain/OpenNote/upload/", "https://domain/OpenNote/Service/upload/");

UPDATE note
	SET note = REPLACE(note,"https://domain/OpenNote/upload/", "https://domain/OpenNote/Service/upload/");

-- Through upload script
UPDATE note
	SET note = REPLACE(note,"http://domain/OpenNote/upload/Download.php?uploadID=", "https://domain/OpenNote/Service/file/");

UPDATE note
	SET note = REPLACE(note,"https://domain/OpenNote/upload/Download.php?uploadID=", "https://domain/OpenNote/Service/file/");


*/