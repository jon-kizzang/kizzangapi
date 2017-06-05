<?php

class AddAbbrToSportTeams extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `SportTeams` ADD COLUMN `abbr` CHAR(3) NULL;");
    }//up()

    public function down()
    {
    }//down()
}
