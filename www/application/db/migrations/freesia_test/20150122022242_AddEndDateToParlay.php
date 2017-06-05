<?php

class AddEndDateToParlay extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `SportParlayCards` 
				ADD COLUMN `endDate` DATETIME NULL AFTER `dateTime`;
			ALTER TABLE `FinalMatches` 
				ADD COLUMN `endDate` DATETIME NULL AFTER `dateTime`;
		");
    }//up()

    public function down()
    {
    }//down()
}
