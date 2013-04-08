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
$lbs = $RAX->LoadBalancerService("cloudLoadBalancers", "DFW", "publicURL");
$ostore = $RAX->ObjectStore();
$dns = $RAX->DNS();
// $monitor = $RAX->


function is_valid_ip4($address) {
  return (preg_match("/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/", $address));
}


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
$iplist = $lb->virtualIps;
$flag = false;
for ($x=0; $x<count($iplist); $x++) {
  if (is_valid_ip4($iplist[$x]->address)) {
    echo "Found valid IPv4 address on Load Balancer\n";
    $lbip = $iplist[$x]->address;
    $flag = true;
    break;
  }
}
if (! $flag) {
  echo "Error: Unable to find valid IPv4 address from Load Balancer\n";
  exit;
}
echo "IP: $lbip\n";
echo "\n";


// Create a zone file with A record pointing to the LB VIP
// Create DNS entry for FQDN
// Determine parent domain from input
$fqdn = "challenge10.cloud.rootmypc.net";
$fqdnArray = explode('.',$fqdn);
$numParts = count($fqdnArray);
$tld = $fqdnArray[$numParts-1];
$parentDomain = $fqdnArray[$numParts-2];
$subDomain = "";
for ($x=0; $x<$numParts-2; $x++) {
  $subDomain = $subDomain . $fqdnArray[$x] . ".";
}
$subDomain = preg_replace('/\.\s*$/', '', $subDomain); // Trim trailing '.'

// Get a list of domains, see if ours already exists
$domainlist = $dns->DomainList();
$exists = false;
while($zone = $domainlist->Next()) {
  if ($zone->name == "$parentDomain.$tld") {
    echo "Parent domain already exists.\n";
    $exists = true;
    break;
  }
}

// Create domain if it doesn't already exist
if ( ! $exists ) {
  echo "Parent domain does not exist.  Creating...\n";
  $zone = $dns->Domain();
  $zone->name = "$parentDomain.$tld";
  $zone->emailAddress = "admin@$parentDomain.$tld";
  $zone->Create();
  sleep(5); // TODO Actually test and verify domain created successfully
  echo "Parent domain created.\n";
  $zone = $dns->DomainList(array("name" => "$parentDomain.$tld"))->Next();
}

// Add our new record to the domain's zone file
$record = $zone->Record();
$record->name = $fqdn;
$record->type = "A";
$record->data = $lbip;
$record->Create();
$zone->Update();
echo "Added A record for \"$fqdn\" to zone file for \"$parentDomain.$tld\"\n";


// Make an error page
$filename = "sorry.html";
echo "Creating /tmp/$filename\n";
$filehandle = fopen("/tmp/$filename", 'w') or die ("Unable to write file\n");
$filecontents = "<html>
<head>
  <title>Challenge 10</title>
</head>
<body>
Error page for challenge 10
</body>
</html>";
fwrite($filehandle, $filecontents);
fclose($filehandle);


// Set the Load Balancer's error page HTML
$errorPage = $lb->ErrorPage();
$errorPage->content = $filecontents;
$errorPage->Create();


// Test if container exists in cloud files
$containerName = "AHoward-Challenge10"
$exists = false;
$containerlist = $ostore->ContainerList();
while($container = $containerlist->Next()) {
  if ($container->name == $containerName) {
    echo "Container \"$containerName\" already exists.\n";
    $exists = true;
  }
}
if (! $exists) {
  // Container doesn't exist - create it
  echo "Creating container \"$containerName\"\n";
  $container = $ostore->Container();
  $container->name = $containerName;
  $container->Create();
}


// Upload error page to Cloud Files for backup
echo "Uploading new $filename file to Cloud Files\n";
$file = $container->DataObject();
$file->Create(array('name'=>$filename), "/tmp/$filename");









// TODO Add a health monitor to the LB
// TODO Supply an SSH key to servers

?>
