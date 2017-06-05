<?php

class AddoverUnderScoreToSportParlayCards extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `SportParlayCards` 
				ADD COLUMN `overUnderScore` INT NULL AFTER `team2Name`;
			");
    }//up()

    public function down()
    {
    }//down()
}
