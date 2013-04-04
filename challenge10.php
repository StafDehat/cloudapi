#!/usr/bin/php
<?php
/** Challenge 10: Write an application that will:
- Create 2 servers, supplying a ssh key to be installed at /root/.ssh/authorized_keys.
- Create a load balancer
- Add the 2 new servers to the LB
- Set up LB monitor and custom error page. 
- Create a DNS record based on a FQDN for the LB VIP. 
- Write the error page html to a file in cloud files for backup.
Whew! That one is worth 8 points!
**/

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

$compute = $RAX->Compute();
$lbs = $RAX->LoadBalancerService();
$ostore = $RAX->ObjectStore();
$dns = $RAX->DNS();
// $monitor = $RAX->


// Some hard-coded crap
$cent63id = 'c195ef3b-9195-4474-b6f7-16e5bd86acd0';


// Initialize some LB stuff
$pool = $lbs->LoadBalancer();
$nodes = array();


// Initiate the creation of all servers - store their initial details in an array
$servers = array();
for ($x=0; $x<2; $x++) {
  $server = $compute->Server();
  $server->name = 'AHoward-Challenge10-' . $x;
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
echo "Creating Load Balancer \"AHoward-Challenge10\"\n";
$pool->name = "AHoward-Challenge10";
$pool->port = "80";
$pool->protocol = "HTTP";
$pool->algorithm = "LEAST_CONNECTIONS";
$pool->nodes = $nodes;
$pool->AddVirtualIp();
$pool->Create();


// Get the load balancer's IP


// Create a zone file with A record pointing to the LB VIP


// Make an error page


// Upload error page to Cloud Files


// Tell the LB to failover to Cloud Files error page







// TODO Add a monitor to the LB
// TODO Supply an SSH key to servers

?>
