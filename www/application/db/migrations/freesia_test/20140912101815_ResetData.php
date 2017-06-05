<?php

class ResetData extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute( "TRUNCATE TABLE `schema_migrations`;" );
    }//up()

    public function down()
    {
    }//down()
}
