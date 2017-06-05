<?php

class AddParlayCarIdToBGQuestion extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute('ALTER TABLE `BGQuestions` 
			ADD COLUMN `parlayCardId` INT NULL AFTER `rule`;
			');
    }//up()

    public function down()
    {
    }//down()
}
