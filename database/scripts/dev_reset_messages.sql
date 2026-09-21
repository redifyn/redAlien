/*
|--------------------------------------------------------------------------
| Reset all development messages
|--------------------------------------------------------------------------
|
| Keeps users, Aliens, Pods and memberships.
|
*/

SET FOREIGN_KEY_CHECKS = 0;

DELETE FROM messages;

ALTER TABLE messages AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;