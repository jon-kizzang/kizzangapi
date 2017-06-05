<?php

class AddWinnerToGamePlayerCard extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `FinalPlayerCards` 
				ADD COLUMN `winner` TINYINT(1) NOT NULL DEFAULT 0 AFTER `rank`;
			ALTER TABLE `BGPlayerCards` 
				ADD COLUMN `winner` TINYINT(1) NOT NULL DEFAULT 0 AFTER `bgCategoryId`;
			ALTER TABLE `SportPlayerCards` 
				ADD COLUMN `winner` TINYINT(1) NOT NULL DEFAULT 0 AFTER `parlayCardId`;
			ALTER TABLE `SportOUPlayerCards` 
				ADD COLUMN `winner` TINYINT(1) NOT NULL DEFAULT 0 AFTER `parlayCardId`;
		");
    }//up()

    public function down()
    {
    }//down()
}
