<?php

class AddWinners extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("DROP TABLE IF EXISTS `Winners`;
    		CREATE TABLE `Winners` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `player_id` int(11) unsigned DEFAULT NULL,
			  `foreign_id` int(11) NOT NULL,
			  `game_type` enum('Slots','Scratchers','Sweepstakes','Parlay','BG','FT') NOT NULL,
			  `serial_number` char(10) NOT NULL,
			  `prize_name` varchar(100) DEFAULT NULL,
			  `amount` decimal(10,2) NOT NULL,
			  `processed` tinyint(3) unsigned NOT NULL DEFAULT '0',
			  `order_num` varchar(100) DEFAULT NULL,
			  `created` timestamp NULL,
			  `updated` timestamp NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;
		");
    }//up()

    public function down()
    {
    }//down()
}
