<?php

class AlterEventNotification extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `EventNotifications` 
			ADD COLUMN `updated` DATETIME NULL AFTER `added`,
			ADD COLUMN `playerActionTaken` INT(1) NOT NULL DEFAULT 0 AFTER `playerId`;
			DROP TABLE IF EXISTS `EventNotificationsLog`;
			CREATE TABLE `EventNotificationsLog` (
			  `id` INT NOT NULL AUTO_INCREMENT,
			  `eventNotificationId` INT NOT NULL,
			  `type` VARCHAR(45) NOT NULL,
			  `data` VARCHAR(250) NOT NULL,
			  `pending` TINYINT(1) NOT NULL,
			  `playerActionTaken` INT(1) NOT NULL,
			  `updated` DATETIME NOT NULL,
			  PRIMARY KEY (`id`));
		");
    }//up()

    public function down()
    {
    	$this->execute("DROP TABLE IF EXISTS `EventNotificationsLog`;");
    }//down()
}
