<?php

class AddAckToPosition extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `Positions` 
			ADD COLUMN `ack` TINYINT(1) NULL DEFAULT 0 AFTER `playerId`;
		");
    }//up()

    public function down()
    {
    }//down()
}
