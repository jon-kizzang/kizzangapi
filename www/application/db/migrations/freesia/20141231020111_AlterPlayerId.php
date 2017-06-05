<?php

class AlterPlayerId extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `SportOUPlayerCards` CHANGE COLUMN `playerId` `playerId` INT(11) NOT NULL ;
    		ALTER TABLE `SportPlayerCards` CHANGE COLUMN `playerId` `playerId` INT(11) NOT NULL ;
    		ALTER TABLE `MasterEmail` CHANGE COLUMN `playerId` `playerId` INT(11) UNSIGNED NOT NULL ;
    		ALTER TABLE `Tickets` CHANGE COLUMN `playerId` `playerId` INT(11) UNSIGNED NOT NULL ;
			ALTER TABLE `Positions` CHANGE COLUMN `playerId` `playerId` INT(11) UNSIGNED NOT NULL ;
			ALTER TABLE `History` CHANGE COLUMN `playerId` `playerId` INT(11) UNSIGNED NOT NULL ;
			ALTER TABLE `Sponsor_Management_Users` CHANGE COLUMN `playerID` `playerID` INT(11) NULL DEFAULT NULL ;
			ALTER TABLE `TrackWedges` CHANGE COLUMN `playerId` `playerId` INT(11) UNSIGNED NOT NULL ;
			ALTER TABLE `FacebookPlayerInvites` CHANGE COLUMN `playerId` `playerId` INT(11) UNSIGNED NOT NULL ;
			ALTER TABLE `GameCount` CHANGE COLUMN `playerId` `playerId` INT(11) UNSIGNED NOT NULL ;
			ALTER TABLE `PlayPeriod` CHANGE COLUMN `playerId` `playerId` INT(11) UNSIGNED NOT NULL ;
			ALTER TABLE `BGPlayerCards` CHANGE COLUMN `playerId` `playerId` INT(11) NOT NULL ;");
    }//up()

    public function down()
    {
    }//down()
}
