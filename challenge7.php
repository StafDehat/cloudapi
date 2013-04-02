<?php
//Challenge 7: Write a script that will create 2 Cloud Servers and add them as nodes to a new Cloud Load Balancer. Worth 3 Points

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

// Some hard-coded crap
$cent63id = 'c195ef3b-9195-4474-b6f7-16e5bd86acd0';

$compute = $RAX->Compute();


// Initialize some LB stuff
$lbs = $RAX->LoadBalancerService('cloudLoadBalancers', 'DFW', 'publicURL');
$pool = $lbs->LoadBalancer();
$nodes = array();


// Initiate the creation of all servers - store their initial details in an array
$servers = array();
for ($x=0; $x<2; $x++) {
  $server = $compute->Server();
  $server->name = 'AHoward-LBTest' . $x;
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

  // Create a load-balance Node from this new server
  $node = $pool->Node();
  $node->address = $server->ip(4);
  $node->port = "80";
  $node->condition = "ENABLED";
  $nodes[] = $node;
}


// Define the load balancer and create it
$pool->name = "AHoward-LBTest";
$pool->port = "80";
$pool->protocol = "HTTP";
$pool->algorithm = "LEAST_CONNECTIONS";
$pool->nodes = $nodes;
$pool->AddVirtualIp();
$pool->Create();




?>
