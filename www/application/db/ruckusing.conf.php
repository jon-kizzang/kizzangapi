<?php
//$ruckusBase=dirname(__FILE__) . '/../../../../lib/ruckus';
$env = 'ENV_DEV';

$system_path = '../../vendor/rogeriopradoj/codeigniter/system';
if (realpath($system_path) !== FALSE) {
    $system_path = realpath($system_path).'/';
}

// ensure there's a trailing slash
$system_path = rtrim($system_path, '/').'/';

// Path to the system folder
define('BASEPATH', str_replace("\\", "/", $system_path));

// prod vs dev check
if (file_exists(dirname(__FILE__) . '/../../vendor/ruckusing/ruckusing-migrations')) {
    $ruckusBase = dirname(__FILE__) . '/../../vendor/ruckusing/ruckusing-migrations';
}

$pathToEnv="../config/database.php";

require_once($pathToEnv);

if ( strpos($env, '_TEST') !== FALSE ) {
    $envVar = $db['test'];
} else {
    $envVar = $db['default'];
}

$scratchCardEnvVar = $db['scratchcardsdb'];

if( file_exists($pathToEnv) && is_readable($pathToEnv) && $envVar && $scratchCardEnvVar ) {
    if (isset($envVar) && isset($scratchCardEnvVar) ) {

        $host = $envVar['hostname'];
        $dbname = $envVar['database'];

        //----------------------------
        // DATABASE CONFIGURATION
        //----------------------------

        /*

        Valid types (adapters) are Postgres & MySQL:

        'type' must be one of: 'pgsql' or 'mysql'


        'development' database used by default. If you wish to use another, make use of
        the 'env' variable during runtime.
        */
        return array(
                'db' => array(
                      'development' => array(
                                'type'      => 'mysql',
                                'host'      => $host,
                                'port'      => 3306,
                                'database'  => $dbname,
                                'user'      => $envVar['username'],
                                'password'  => $envVar['password'],
                                'charset' => 'utf8',
                                //'directory' => 'custom_name',
                                // 'socket' => '/var/run/mysqld/mysqld.sock'
                            ),
                       /*
					   'scratchcards' => array(
                                'type'      => 'mysql',
                                'host'      => $scratchCardEnvVar['hostname'],
                                'port'      => 3306,
                                'database'  => $scratchCardEnvVar['database'],
                                'user'      => $scratchCardEnvVar['username'],
                                'password'  => $scratchCardEnvVar['password'],
                                'charset' => 'utf8',
					   ),
					   */
                       /* 'mysql'  => array(
                                'type'  => 'mysql',
                                'host'  => 'localhost',
                                'port'  => 3306,
                                'database'  => '',
                                'user'  => '',
                                'password'  => '',
                                'charset'=>'utf8',
                                //'directory' => 'custom_name',
                                //'socket' => '/var/run/mysqld/mysqld.sock'
                        )*/

                ),

                'migrations_dir' => array('default' => RUCKUSING_WORKING_BASE . '/migrations'),

                'db_dir' => RUCKUSING_WORKING_BASE . '/db',

                'log_dir' => RUCKUSING_WORKING_BASE  . '/logs',

                'ruckusing_base' => $ruckusBase

                );
    }
    else
    {
        exit('Error: "db" database definition not found in database.php\n');
    }
}
else
{
    exit('Error: "database.php" not found\n');
}
