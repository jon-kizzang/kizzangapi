<?php

class AddFromPostions extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `Positions` 
			ADD COLUMN `fromPosition` INT NULL AFTER `playerId`;");
    }//up()

    public function down()
    {
    }//down()
}
