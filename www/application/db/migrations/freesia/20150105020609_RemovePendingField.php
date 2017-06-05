<?php

class RemovePendingField extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `EventNotificationsLog` DROP COLUMN `pending`;");
    }//up()

    public function down()
    {
    }//down()
}
