/**
Configuration file for the project's database.
Creates the database, user, tables, and sample data for the project.
 */

CREATE DATABASE IF NOT EXISTS fantasticfour_p6;
CREATE USER IF NOT EXISTS 'fantasticfour'@'localhost' IDENTIFIED BY 'cs3744';
GRANT ALL PRIVILEGES ON fantasticfour_p6.* to 'fantasticfour'@'localhost';

USE fantasticfour_p6;

DROP TABLE IF EXISTS Person;
DROP TABLE IF EXISTS UnitNote;
DROP TABLE IF EXISTS TimelineEntry;
DROP TABLE IF EXISTS Message;
DROP TABLE IF EXISTS Comment;
DROP TABLE IF EXISTS Unit;
DROP TABLE IF EXISTS Following;
DROP TABLE IF EXISTS UserToken;
DROP TABLE IF EXISTS User;


CREATE TABLE User
(
  userId INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(128) UNIQUE NOT NULL,
  pword_hash VARCHAR(255) NOT NULL,
  email TEXT NOT NULL,
  type INT(11) NOT NULL DEFAULT '3',
  firstname VARCHAR(250) NOT NULL ,
  lastname VARCHAR(250) NOT NULL ,
  privacy VARCHAR(7) NOT NULL
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


CREATE TABLE Following
(
  id INT PRIMARY KEY AUTO_INCREMENT,
  userFrom INT NOT NULL ,
  userTo INT NOT NULL ,
  CONSTRAINT Following_User_userFrom_userId_fk FOREIGN KEY (userFrom) REFERENCES User(userId) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT Following_User_userTo_userId_fk FOREIGN KEY (userTo) REFERENCES User(userId) ON DELETE CASCADE ON UPDATE CASCADE
);
ALTER TABLE Following COMMENT = 'Represents a one-way user following relation.';


CREATE TABLE Unit
(
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  unitParent INT,
  CONSTRAINT Unit_unitParent_fk FOREIGN KEY (unitParent) REFERENCES Unit(id) ON DELETE CASCADE ON UPDATE CASCADE
);
ALTER TABLE Unit COMMENT = 'Represents a Unit in the batallion. unitParent refers to the parent Unit, null implies that it is a top-level unit.';


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


CREATE TABLE UnitNote
(
  id INT PRIMARY KEY AUTO_INCREMENT,
  unitID INT NOT NULL ,
  title VARCHAR(256) NOT NULL,
  content TEXT NOT NULL ,
  imageURL TEXT ,

  CONSTRAINT UnitNote_Unit_unitID_fk FOREIGN KEY (unitID) REFERENCES Unit(id)
    ON DELETE CASCADE ON UPDATE CASCADE
);
ALTER TABLE UnitNote COMMENT = 'A note, containing a title, paragraph of text, and an optional image, left on a Unit page.';


CREATE TABLE Comment
(
  id INT PRIMARY KEY AUTO_INCREMENT,
  user INT NOT NULL ,
  unit INT NOT NULL ,
  timestamp DATETIME NOT NULL ,
  text TEXT NOT NULL ,
  CONSTRAINT Comment_User_userID_fk FOREIGN KEY (user) REFERENCES User(userId) ON DELETE CASCADE ON UPDATE CASCADE ,
  CONSTRAINT Comment_Unit_unitID_fk FOREIGN KEY (unit) REFERENCES Unit(id) ON DELETE CASCADE ON UPDATE CASCADE
);
ALTER TABLE Comment COMMENT = 'Represents a single comment left by a User on a Unit page.';


CREATE TABLE Message
(
  id INT PRIMARY KEY AUTO_INCREMENT,
  follow INT NOT NULL ,
  timestamp DATETIME NOT NULL ,
  text TEXT NOT NULL ,
  CONSTRAINT Message_Following_id_fk FOREIGN KEY (follow) REFERENCES Following(id) ON DELETE CASCADE ON UPDATE CASCADE
);
ALTER TABLE Message COMMENT = 'Represents a single message sent through a Following relation.';


CREATE TABLE TimelineEntry
(
  id INT PRIMARY KEY AUTO_INCREMENT,
  eventName VARCHAR(64) NOT NULL,
  type VARCHAR(32) NOT NULL ,
  date DATE NOT NULL ,
  description TEXT NOT NULL ,
  locationName TEXT NOT NULL ,
  latitude FLOAT NOT NULL ,
  longitude FLOAT NOT NULL
);
ALTER TABLE TimelineEntry COMMENT = 'An event that occurred along the campaign. Can be a battle, an operation, a diary entry, etc.';



/** Add Sample Data **/

/** Admin Username: adminuser; Password: mypassword **/
/** Editor Username: leveltwo; Password: mypassword **/
/** Commentor Username: levelone; Password: mypassword **/
INSERT INTO fantasticfour_p6.User (`userId`, `username`, `pword_hash`, `email`, `type`, `firstname`, `lastname`, `privacy`) VALUES
  (1, 'adminuser', '$2y$10$Hj1xEDJR.vr3KOPxi/iXC.et92qXsgyyb3hmM/NaAv2acz/SVxBwK', 'sample@example.com', 3, 'Admin', 'User', 'PRIVATE'),
  (2, 'levelone', '$2y$10$Hj1xEDJR.vr3KOPxi/iXC.et92qXsgyyb3hmM/NaAv2acz/SVxBwK', 'leslie6@vt.edu', 1, 'Comment', 'User', 'PRIVATE'),
  (3, 'leveltwo', '$2y$10$Hj1xEDJR.vr3KOPxi/iXC.et92qXsgyyb3hmM/NaAv2acz/SVxBwK', 'leslie6@vt.edu', 2, 'Editor', 'User', 'PRIVATE'),
  (4, 'levelthree', '$2y$10$Hj1xEDJR.vr3KOPxi/iXC.et92qXsgyyb3hmM/NaAv2acz/SVxBwK', 'leslie6@vt.edu', 3, '', '', 'PRIVATE'),
  (5, 'admin300', '$2y$10$Hj1xEDJR.vr3KOPxi/iXC.et92qXsgyyb3hmM/NaAv2acz/SVxBwK', 'random@gmail.com', 1, '', '', 'PRIVATE'),
  (8, 'admin1000', '$2y$10$Hj1xEDJR.vr3KOPxi/iXC.et92qXsgyyb3hmM/NaAv2acz/SVxBwK', 'joeschmoe@gmail.com', 1, 'joe', 'schmoe', 'PRIVATE'),
  (9, 'admin2000', '$2y$10$Hj1xEDJR.vr3KOPxi/iXC.et92qXsgyyb3hmM/NaAv2acz/SVxBwK', 'sample@yahoo.com', 2, 'hey', 'hi', 'PUBLIC'),
  (10, 'admin3000', '$2y$10$Hj1xEDJR.vr3KOPxi/iXC.et92qXsgyyb3hmM/NaAv2acz/SVxBwK', 'sample@email.com', 1, 'Mary', 'Doe', 'PRIVATE'),
  (11, 'admin4000', '$2y$10$Hj1xEDJR.vr3KOPxi/iXC.et92qXsgyyb3hmM/NaAv2acz/SVxBwK', 'sample@email.com', 11, 'John', 'Jacobs', 'PUBLIC'),
  (13, 'admin7000', '$2y$10$Hj1xEDJR.vr3KOPxi/iXC.et92qXsgyyb3hmM/NaAv2acz/SVxBwK', 'sample@gmail.com', 1, 'Joe', 'Jacobs', 'PRIVATE');

INSERT INTO fantasticfour_p6.Unit (id, name) VALUES (1, 'Headquarters Company');
INSERT INTO fantasticfour_p6.Unit (id, name, unitParent) VALUES (11, 'Assault Gun Platoon', 1);
INSERT INTO fantasticfour_p6.Unit (id, name, unitParent) VALUES (12, 'Reconaissance Platoon', 1);
INSERT INTO fantasticfour_p6.Unit (id, name) VALUES (3, 'Company A');
INSERT INTO fantasticfour_p6.Unit (id, name, unitParent) VALUES (31, 'Company A - 1st Platoon', 3);
INSERT INTO fantasticfour_p6.Unit (id, name, unitParent) VALUES (32, 'Company A - 2nd Platoon', 3);
INSERT INTO fantasticfour_p6.Unit (id, name, unitParent) VALUES (33, 'Company A - 3rd Platoon', 3);
INSERT INTO fantasticfour_p6.Unit (id, name) VALUES (4, 'Medical Detachment');

INSERT INTO fantasticfour_p6.Person (id, unitID, rank, firstname, lastname) VALUES (1, 11, 'Pfc', 'Lawrence', 'Clark');
INSERT INTO fantasticfour_p6.Person (id, unitID, rank, firstname, lastname) VALUES (2, 11, 'Pvt', 'Chester', 'Harej');
INSERT INTO fantasticfour_p6.Person (id, unitID, rank, firstname, lastname) VALUES (3, 11, 'Pfc', 'Vito', 'Mikalauski');
INSERT INTO fantasticfour_p6.Person (id, unitID, rank, firstname, lastname) VALUES (4, 12, 'Cpl', 'Harvey', 'Keller');
INSERT INTO fantasticfour_p6.Person (id, unitID, rank, firstname, lastname) VALUES (5, 12, 'Pvt', 'Jessie', 'Staggs');

INSERT INTO Following (id, userFrom, userTo) VALUES
  (1, 1, 2), (2, 2, 1), (3, 1, 9), (4, 11, 1);

INSERT INTO Comment(id, user, unit, timestamp, text) VALUES
  (1, 1, 11, NOW(), 'Sample Administrator Comment!'),
  (2, 2, 11, NOW(), 'Sample Commenter Comment!'),
  (3, 3, 12, NOW(), 'Sample Editor Comment!'),
  (4, 13, 12, NOW(), '<marquee>Comments support HTML!</marquee>'),
  (5, 1, 31, NOW(), 'Sample Administrator Comment!'),
  (6, 2, 31, NOW(), 'Sample Commenter Comment!'),
  (7, 3, 31, NOW(), 'Sample Editor Comment!');

INSERT INTO Message(id, follow, timestamp, text) VALUES
  (1, 1, NOW(), 'Hello, Commenter User'),
  (2, 2, NOW(), 'Hello, Admin User'),
  (3, 4, NOW(), 'Hello, Admin User'),
  (4, 4, NOW(), '<h1>Messages Support HTML</h1>');