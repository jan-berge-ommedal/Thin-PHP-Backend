DROP TABLE IF EXISTS DATA;
CREATE TABLE DATA(
  id VARCHAR(40) NOT NULL PRIMARY KEY,
  dataType VARCHAR(80) NOT NULL,
  contentType VARCHAR(40) NOT NULL,
  data MEDIUMBLOB NOT NULL,  -- max 16 MB
  name VARCHAR (255),
  INDEX dataType (dataType)
);

DROP TABLE IF EXISTS SESSION;
CREATE TABLE SESSION(
  SESSION_ID VARCHAR(255) NOT NULL PRIMARY KEY,
  CREATED DATETIME,
  INDEX CREATED (CREATED)
);