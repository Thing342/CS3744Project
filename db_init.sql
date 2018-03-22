/**
Configuration file for the project's database.
Creates the database, user, tables, and sample data for the project.
 */

CREATE DATABASE IF NOT EXISTS fantasticfour_p4;
CREATE USER 'fantasticfour'@'localhost' IDENTIFIED BY 'cs3744';
GRANT ALL PRIVILEGES ON fantasticfour_p4.* to 'fantasticfour'@'localhost';

USE fantasticfour_p4;

DROP TABLE IF EXISTS Person;
DROP TABLE IF EXISTS UnitEvent;
DROP TABLE IF EXISTS Unit;
DROP TABLE IF EXISTS UserToken;
DROP TABLE IF EXISTS User;


CREATE TABLE User
(
  userId INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(128) UNIQUE NOT NULL,
  pword_hash VARCHAR(255) NOT NULL,
  email TEXT NOT NULL
);
ALTER TABLE User COMMENT = 'A user account.';


CREATE TABLE UserToken
(
  tokenId INT PRIMARY KEY AUTO_INCREMENT,
  user INT NOT NULL ,
  created DATETIME NOT NULL,
  expires DATETIME NOT NULL,
  CONSTRAINT UserToken_User_userId_fk FOREIGN KEY (user) REFERENCES User(userId) ON DELETE CASCADE ON UPDATE CASCADE
);
ALTER TABLE UserToken COMMENT = 'Represents an authenticated user session.';

CREATE TABLE Unit
(
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL
);

CREATE TABLE Person
(
  id INT PRIMARY KEY AUTO_INCREMENT,
  unitID INT NOT NULL ,
  rank VARCHAR(64) NOT NULL ,
  firstname VARCHAR(128) NOT NULL ,
  lastname VARCHAR(128) NOT NULL,
  CONSTRAINT Person_Person_unitId_fk FOREIGN KEY (unitID) REFERENCES Unit(id) ON DELETE CASCADE ON UPDATE CASCADE
);
ALTER TABLE Person COMMENT = 'A Person. Added and edited by Users. Belongs to a Unit.';

CREATE TABLE UnitEvent
(
  id INT PRIMARY KEY AUTO_INCREMENT,
  unitID INT NOT NULL ,
  type INT NOT NULL ,
  date DATE NOT NULL ,
  location VARCHAR(160) NOT NULL ,
  event TEXT NOT NULL ,
  CONSTRAINT UnitEvent_Unit_unitID_fk FOREIGN KEY (unitID) REFERENCES Unit(id)
    ON DELETE CASCADE ON UPDATE CASCADE
);
ALTER TABLE UnitEvent COMMENT = 'An event that occurred to some Unit along the campaign. Can be a battle, an operation, a diary entry, etc.';
