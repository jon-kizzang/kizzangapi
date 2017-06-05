<?php

class InitDatabase extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$file = dirname(__FILE__) . '/kizzang-stagging.sql';
      
      if( file_exists( $file ) ) {

          $contents = file_get_contents( $file );
          $queries = explode(";\n", $contents );

          foreach( $queries as $query ) {

              if ( ! empty( $query ) ) {

                  $this->execute( $query );
              }
          }
      }
    }//up()

    public function down()
    {
    }//down()
}
