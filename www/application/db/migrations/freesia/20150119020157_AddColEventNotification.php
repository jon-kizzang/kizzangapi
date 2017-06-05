<?php

class AddColEventNotification extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `EventNotifications` 
			ADD COLUMN `expireDate` DATETIME NULL AFTER `updated`;");
    }//up()

    public function down()
    {
    }//down()
}
