#!/usr/bin/php
<?php
// Challenge 5: Write a script that creates a Cloud Database instance. This instance should contain at least one database, and the database should have at least one user that can connect to it. Worth 1 Point
//$RAXSDK_DEBUG = true;
require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');


$dbaas = $RAX->DbService('cloudDatabases','DFW','publicURL');


$instance = $dbaas->Instance();
$instance->name = 'AHoward-c05';
$instance->flavor = $dbaas->Flavor(1);
$instance->volume->size = 1;
$instance->Create();


// Wait loop for creation
$id = $instance->id;
do {
  echo "Instance still building.  Sleeping 30 seconds...\n";
  sleep(30);
  $instance = $dbaas->Instance($id);
} while ( $instance->status == "BUILD" );
if (! ($instance->status == "ACTIVE")) {
  echo "Unknown error occurred while building instance.\n";
  echo "Instance status: $instance->status\n";
  exit;
}
echo "Instance built with ID $instance->id.\n";


// Create a database
$db = $instance->Database();
$db->Create( array('name' => 'tmpdb') );


// Create a user
$user = $instance->User();
$user->name = 'tmpuser';    // assigns a name
$user->password = 'tmppass';    // assigns a name
$user->AddDatabase('tmpdb');
$user->Create();

?>
