/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

# Dump of table USER_TAGS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `USER_TAGS`;

CREATE TABLE `USER_TAGS` (
                             `userID` int(11) unsigned NOT NULL,
                             `tag` varchar(32) DEFAULT NULL,
                             KEY `userID` (`userID`),
                             KEY `tag` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `USER_TAGS` WRITE;
/*!40000 ALTER TABLE `USER_TAGS` DISABLE KEYS */;

INSERT INTO `USER_TAGS` (`userID`, `tag`)
VALUES
(1,'president'),
(1,'good'),
(1,'human'),
(2,'president'),
(2,'alien'),
(2,'a man'),
(3,'president'),
(3,'not a boy'),
(3,'strong');

/*!40000 ALTER TABLE `USER_TAGS` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table USERS
# ------------------------------------------------------------

DROP TABLE IF EXISTS `USERS`;

CREATE TABLE `USERS` (
                         `userID` int(11) unsigned NOT NULL AUTO_INCREMENT,
                         `name` varchar(32) DEFAULT NULL,
                         PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `USERS` WRITE;
/*!40000 ALTER TABLE `USERS` DISABLE KEYS */;

INSERT INTO `USERS` (`userID`, `name`)
VALUES
(1,'George Washington'),
(2,'John Adams'),
(3,'Thomas Jefferson'),
(4,'James Madison'),
(5,'James Monroe'),
(6,'John Quincy Adams'),
(7,'Andrew Jackson'),
(8,'Martin Van Buren'),
(9,'William Henry Harrison'),
(10,'John Tyler'),
(11,'James K. Polk'),
(12,'Zachary Taylor'),
(13,'Millard Fillmore'),
(14,'Franklin Pierce'),
(15,'James Buchanan'),
(16,'Abraham Lincoln'),
(17,'Andrew Johnson'),
(18,'Ulysses S. Grant'),
(19,'Rutherford B. Hayes'),
(20,'James A. Garfield'),
(21,'Chester A. Arthur'),
(22,'Grover Cleveland'),
(23,'Benjamin Harrison'),
(24,'Grover Cleveland (2nd term)'),
(25,'William McKinley'),
(26,'Theodore Roosevelt'),
(27,'William Howard Taft'),
(28,'Woodrow Wilson'),
(29,'Warren G. Harding'),
(30,'Calvin Coolidge'),
(31,'Herbert Hoover'),
(32,'Franklin D. Roosevelt'),
(33,'Harry S. Truman'),
(34,'Dwight D. Eisenhower'),
(35,'John F. Kennedy'),
(36,'Lyndon B. Johnson'),
(37,'Richard Nixon'),
(38,'Gerald Ford'),
(39,'Jimmy Carter'),
(40,'Ronald Reagan'),
(41,'George H. W. Bush'),
(42,'Bill Clinton'),
(43,'George W. Bush'),
(44,'Barack Obama'),
(45,'Donald Trump');

/*!40000 ALTER TABLE `USERS` ENABLE KEYS */;
UNLOCK TABLES;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SELECT u.userId,
       u.name,
       GROUP_CONCAT(t.tag ORDER BY t.tag DESC) tags
FROM USERS u
         JOIN USER_TAGS t USING (userId)
GROUP BY u.userId
UNION
SELECT r.userId = 1 AS ID_ASSERT, r.name = 'George Washington' AS NAME_ASSERT, r.tags = 'president,human,good' AS TAG_ASSERT
FROM (SELECT u.userId,
             u.name,
             GROUP_CONCAT(t.tag ORDER BY t.tag DESC) tags
      FROM USERS u
               JOIN USER_TAGS t USING (userId)
      WHERE u.userId = 1
      GROUP BY u.userId) r

