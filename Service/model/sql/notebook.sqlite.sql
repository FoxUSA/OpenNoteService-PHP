CREATE TABLE IF NOT EXISTS folder (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  parrentFolderID INTEGER DEFAULT NULL,
  "name" varchar(200) NOT NULL,
  userID INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS note (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  folderID INTEGER NOT NULL,
  originNoteID INTEGER DEFAULT NULL,
  title varchar(200) NOT NULL,
  note longtext,
  dateCreated timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  userID INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS token (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  userID INTEGER NOT NULL,
  ip varchar(256) NOT NULL,
  token varchar(256) NOT NULL,
  issued timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  expires timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
);

CREATE UNIQUE INDEX token_index ON token (token);

CREATE TABLE IF NOT EXISTS uploads (
  id varchar(128) PRIMARY KEY,
  originalName varchar(255) NOT NULL,
  diskName varchar(255) NOT NULL,
  userID INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  userName varchar(255) NOT NULL,
  "password" varchar(255) NOT NULL
);
