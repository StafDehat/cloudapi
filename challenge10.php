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
  $server->name = 'AHoward-c10-' . $x;
  $server->flavor = $compute->Flavor(2); //512MB
  $server->image = $compute->Image($cent63id);
  $server->AddFile("/root/.ssh/authorized_keys", "ssh-dss AAAAB3NzaC1kc3MAAACBAIV176V+xkqeC9l0zNX/DKPj7MVFNgqlwU7eI2/K/dsy0bQxSC7rpnFz61bJUm0NkU/iBUv0db26wbeYUJujjU9b/aknyM7fPX3KAG5S8NYMAtsGDqnzipb5A3zwai1xm4+UEGfUWHzQad8wa2V9YzDYl0M483uvj9+5oCzOy4BJAAAAFQC9MdKTr6aHuUdF5vxp1vFf6mZoiwAAAIApC153lpx006JViJb37LNsVN1fv1iKxSkfOUi1WjSJ5hvRvPLqD/5K7MDGAWVcVN48NUJzArlYBYcTr8ZqbbuVZDKLS/7tbftecVk/smEWnF1Zp8wdeT5vnSRhFkvIqBBZQWL6iie9omUiLWSa2GBQ6HLYgrNyenoD9A7vDlVLggAAAIBzA7s7oaSlku3iC3CJJtagMcHCexSndO8mUNzREJtTYcvt2TwdfHfJ7J+VqdzN12UxRmsBS/UoIKT0GFhBDlHjzQsZSicOlWQ4+vxMoHH/HfuEpCUqYnvmJ6LKOpcxuunL0pWAE06J8s6KV1AfyDDbcW5gj06Y1YWSzX+UrTew/Q== andr4596@cbast1.dfw1.corp.rackspace.com");
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
echo "Creating Load Balancer \"AHoward-c10\"\n";
$pool->name = "AHoward-c10";
$pool->port = "80";
$pool->protocol = "HTTP";
$pool->algorithm = "LEAST_CONNECTIONS";
$pool->nodes = $nodes;
$pool->AddVirtualIp();
$pool->healthMonitor = array(
  "type"=>"CONNECT",
  "delay"=>"10",
  "timeout"=>"5",
  "attemptsBeforeDeactivation"=>"2");
$pool->Create();
$poolid = $pool->id;


// Get the load balancer's IP
$iplist = $pool->virtualIps;
$flag = false;
for ($x=0; $x<count($iplist); $x++) {
  if (is_valid_ip4($iplist[$x]->address)) {
    echo "Found valid IPv4 address on Load Balancer\n";
    $poolip = $iplist[$x]->address;
    $flag = true;
    break;
  }
}
if (! $flag) {
  echo "Error: Unable to find valid IPv4 address from Load Balancer\n";
  exit;
}
echo "Load Balancer IP: $poolip\n";
echo "Load Balancer ID: $poolid\n";
echo "\n";


// Create a zone file with A record pointing to the LB VIP
// Create DNS entry for FQDN
// Determine parent domain from input
$fqdn = "c10.cloud.rootmypc.net";
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
$record->data = $poolip;
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


// Verify $pool is created, wait if necessary
while ($lbs->LoadBalancer($poolid)->status == "BUILD") {
  echo "Waiting for load balancer to finish building...\n";
  sleep(5);
}
if (! ($lbs->LoadBalancer($poolid)->status == "ACTIVE")) {
  echo "Error: Unknown problem encountered during build.\n";
  exit;
}
echo "Load Balancer build complete.\n";


// Set the Load Balancer's error page HTML
echo "Setting custom error page for Load Balancer.\n";
$errorPage = $pool->ErrorPage();
$errorPage->content = $filecontents;
$errorPage->Create();


// Test if container exists in cloud files
$containerName = "AHoward-c10";
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


// TODO Take SSH key as argument

?>
