<?php

class AddUserLoginTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute('CREATE TABLE `PlayerLogins` (
  `playerId` int(10) unsigned NOT NULL,
  `lastLogin` datetime NOT NULL,
  `created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`playerId`,`lastLogin`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1');
    }//up()

    public function down()
    {
    }//down()
}
