<?php
//Challenge 1: Write a script that builds three 512 MB Cloud Servers that follow a similar naming convention (ie., web1, web2, web3) and returns the IP and login credentials for each server. Use any image you want. Worth 1 point


require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

// Some hard-coded crap
$cent63id = 'c195ef3b-9195-4474-b6f7-16e5bd86acd0';

$compute = $RAX->Compute();


$servers = array();

// Initiate the creation of all servers - store their initial details in an array
for ($x=0; $x<3; $x++) {
  $server = $compute->Server();
  $server->name = 'AHoward-Test' . $x;
  $server->flavor = $compute->Flavor(2); //512MB
  $server->image = $compute->Image($cent63id);
  $server->Create();

  $servers[] = $server;

  echo "Creating server " . $server->name . " with ID ". $server->id ."\n";
}


// Wait for build completion, pull updated details and print info
for ($x=0; $x<count($servers); $x++) {
  $server = $servers[$x];
  $id = $server->id;
  $rootpass = $server->adminPass;

  do {
    echo "Server not yet active.  Sleeping 30s...\n";
    sleep(30);
    $server = $compute->Server($id);
  } while ( ! ($server->status == 'ACTIVE') );

  echo "\n";
  echo $server->name . " details:\n";
  echo "Server ID: ". $id ."\n";
  echo "IP:        " . $server->ip(4) . "\n";
  echo "Username:  root\n";
  echo "Password:  " . $rootpass . "\n";
  echo "\n";
}

?>
