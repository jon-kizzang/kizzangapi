<?php

class AddNewUserFlowPlayers extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `Players` 
    		ADD COLUMN `newUserFlow` TINYINT(1) NOT NULL DEFAULT 1;");
    }//up()

    public function down()
    {
    }//down()
}
