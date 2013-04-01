#!/usr/bin/php
<?php

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');


//$dbaas = $RAX->DbService(NULL, 'DFW');
$dbaas = $RAX->DbService('cloudDatabases','DFW','publicURL');


/**
$instance = $dbaas->Instance();
$instance->name = 'AHoward';
$instance->flavor = $dbaas->Flavor(1);
$instance->volume->size = 1;
$instance->Create();


echo "ID: ". $instance->id ."\n";

// Wait loop for creation
$id = $instance->id;
while ( ! ($instance->status == "ACTIVE") ) {
  echo "Instance still building.  Sleeping 30 seconds...\n";
  sleep(30);
  $instance = $dbaas->Instance($id);
}
echo "Instance built.\n";
**/

$instance = $dbaas->Instance("e7f49874-74f7-45c6-b4f0-3f63fbd9e94d");

// Create a database
$db = $instance->Database();
echo "Test1\n";
$db->Create( array('name' => 'tmpdb') );
echo "Test2\n";

// Create a user
$user = $instance->User();
$user->name = 'tmpuser';    // assigns a name
$user->password = 'tmppass';    // assigns a name
$user->AddDatabase('tmpdb');
$user->Create();

?>
