<?php
$config['memcache_enable'] = TRUE;
$config['memcache_timeout'] = 0;
$server = getenv('MEMCACHED_HOST') ? getenv('MEMCACHED_HOST') : '127.0.0.1';
$config['memcache_servers'] = array(
	array($server, 11211)
);
