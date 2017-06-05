<?php

class AlterFacebookPlayerInvites extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `FacebookPlayerInvites` 
			CHANGE COLUMN `friendFacebookId` `friendFacebookId` VARCHAR(512) NOT NULL ;
		");
    }//up()

    public function down()
    {
    }//down()
}
