<?php
	$memcache = new Memcache;
$memcache->connect("localhost",11211); # You might need to set "localhost" to "127.0.0.1"



echo "Server's version: " . $memcache->getVersion() . "\n";



$tmp_object = new stdClass;

$tmp_object->str_attr = "test";

$tmp_object->int_attr = 123;



$memcache->set("key",$tmp_object,false,10);

echo "Store data in the cache (data will expire in 10 seconds)\n";



echo "Data from the cache:\n";

var_dump($memcache->get("key"));