-- contents.sql (SQLite compatible)
INSERT OR IGNORE INTO user (username,password) values ('foo','password');
INSERT OR IGNORE INTO user (username,password) values ('bar','password');

INSERT OR IGNORE INTO role (id,name) values (1,'admin');
INSERT OR IGNORE INTO role (id,name) values (2,'member');
