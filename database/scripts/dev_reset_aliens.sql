/*
|--------------------------------------------------------------------------
| Reset development Aliens
|--------------------------------------------------------------------------
|
| Keeps registered users.
| Removes messages, Pods, memberships and Aliens.
|
*/

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM messages;
DELETE FROM channels;
DELETE FROM team_members;
DELETE FROM teams;

ALTER TABLE messages AUTO_INCREMENT = 1;
ALTER TABLE channels AUTO_INCREMENT = 1;
ALTER TABLE team_members AUTO_INCREMENT = 1;
ALTER TABLE teams AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;