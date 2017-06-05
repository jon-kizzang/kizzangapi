ALTER TABLE `Tickets` ADD INDEX ( `playerId`, `isIssued` ) ;

ALTER TABLE `Tickets` DROP INDEX `id_idx`;


