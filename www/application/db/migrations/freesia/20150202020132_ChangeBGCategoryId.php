<?php

class ChangeBGCategoryId extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `BGPlayerCards` 
			CHANGE COLUMN `bgCategoryId` `parlayCardId` INT(2) NOT NULL DEFAULT '1' ;
		");
    }//up()

    public function down()
    {
    }//down()
}
