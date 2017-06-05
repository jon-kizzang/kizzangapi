<?php

class SponsorCampaignChanges extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("
			Create table `Sponsor_Campaign_Redemptions` (
				`sponsorCampaignId` int unsigned NOT NULL,
				`playerId` int unsigned NOT NULL,
				`created` timestamp NULL DEFAULT NULL,
				`updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				primary key (`sponsorCampaignId`, `playerId`))
				engine innodb;

    		ALTER TABLE `Sponsor_Campaigns` CHANGE COLUMN `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ;
			ALTER TABLE `SportParlayCards` CHANGE COLUMN `overUnderScore` `overUnderScore` DECIMAL(10,1) NULL DEFAULT NULL ;
		");

    }//up()

    public function down()
    {
    }//down()
}
