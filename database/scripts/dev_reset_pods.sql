/*
|--------------------------------------------------------------------------
| Reset development Pods
|--------------------------------------------------------------------------
|
| Removes all messages and channels.
| Keeps teams, users and team memberships.
|
*/

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM messages;
DELETE FROM channels;

ALTER TABLE messages AUTO_INCREMENT = 1;
ALTER TABLE channels AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;