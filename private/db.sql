CREATE TABLE Upload (
  id           INTEGER PRIMARY KEY AUTOINCREMENT,
  name         VARCHAR(45),
  email        VARCHAR(512),
  photoPath    VARCHAR(1024),
  thumbPath    VARCHAR(1024),
  templatePath VARCHAR(1024),
  notes        VARCHAR(4096),
  whenTaken    DATETIME            DEFAULT CURRENT_TIMESTAMP
);