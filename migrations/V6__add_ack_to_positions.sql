ALTER TABLE `Positions` 
   ADD COLUMN `ack` TINYINT(1) NULL DEFAULT 0 
   AFTER `playerId`;