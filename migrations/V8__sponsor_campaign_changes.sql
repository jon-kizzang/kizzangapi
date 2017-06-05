Alter table kizzang.Sponsor_Campaigns add modalAssetUrl varchar(500) after artAssetUrl;
Alter table kizzang.Sponsor_Campaigns add numGames int unsigned NOT NULL DEFAULT 0 after day;

Create table kizzang.Sponsor_Campaign_Redemptions (
sponsorCampaignId int unsigned NOT NULL,
playerId int unsigned NOT NULL,
created timestamp NULL DEFAULT CURRENT_TIMESTAMP,
updated timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
primary key (sponsorCampaignId, playerId))
engine innodb;

ALTER TABLE `kizzang`.`Sponsor_Campaigns` CHANGE COLUMN `id` `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT ;
ALTER TABLE `kizzang`.`SportParlayCards` CHANGE COLUMN `overUnderScore` `overUnderScore` DECIMAL(10,1) NULL DEFAULT NULL ;