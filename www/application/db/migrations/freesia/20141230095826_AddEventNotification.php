<?php

class AddEventNotification extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("DROP TABLE IF EXISTS `EventNotifications`;
    		CREATE TABLE `EventNotifications` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `playerId` int(11) NOT NULL,
			  `type` varchar(45) NOT NULL,
			  `data` varchar(250) NOT NULL,
			  `pending` tinyint(1) NOT NULL,
			  `added` datetime NOT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=latin1;");
    }//up()

    public function down()
    {
    	$this->execute("DROP TABLE IF EXISTS `EventNotifications`;");
    }//down()
}
