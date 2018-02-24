-- Create token table
	CREATE TABLE IF NOT EXISTS token (
	  id INTEGER PRIMARY KEY AUTOINCREMENT,
	  userID INTEGER NOT NULL,
	  ip varchar(256) NOT NULL,
	  token varchar(256) NOT NULL,
	  issued timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  expires timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
	);
	

	
/* You will have to figure out how to change and drop these columns. SQLITE doesnt support the ALTER statement fully
Change upload id to varchar
	ALTER TABLE uploads CHANGE id VARCHAR( 128 ) NOT NULL ;
	
Remove old columns
	ALTER TABLE users DROP lastLoginIP; -- This data can now be found in the token table
	ALTER TABLE users DROP lastLoginTime;
*/	
	
-- Escape html
	UPDATE note
		SET note = REPLACE(REPLACE(REPLACE(REPLACE(note, ">", "&gt;"), "<", "&lt;"), '"', "&quot;"), "'", "&#39;");