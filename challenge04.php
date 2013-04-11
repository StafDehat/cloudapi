<?php
// Challenge 4: Write a script that uses Cloud DNS to create a new A record when passed a FQDN and IP address as arguments. Worth 1 Point

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');


$dns = $RAX->DNS();


function usage($self) {
  echo "Usage: php $self FQDN IP-Address\n";
  exit;
}
function is_valid_domain_name($domain_name) {
  return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
       && preg_match("/^.{1,253}$/", $domain_name) //overall length check
       && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   ); //length of each label
}
function is_valid_ip($address) {
  return (preg_match("/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/", $address));
}

// Ensure proper number of args
if (count($argv) != 3) {
  echo "Error: Incorrent number of arguments.\n";
  usage($argv[0]);
}
$fqdn = $argv[1];
$ipaddr = $argv[2];
// Validate domain
if ( ! is_valid_domain_name($fqdn) ) {
  echo "Error: Invalid domain name.\n";
  echo "  Valid domain characters are letters, numbers, hypens, and/or underscores.\n";
  usage($argv[0]);
}
// Validate IP address
if ( ! is_valid_ip($ipaddr) ) {
  echo "Error: Invalid IP address.\n";
  usage($argv[0]);
}
// Determine parent domain from input
$fqdn = preg_replace('/\.\s*$/', '', $fqdn); // Trim trailing '.'
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
  sleep(5);
  echo "Parent domain created.\n";
  $zone = $dns->DomainList(array("name" => "$parentDomain.$tld"));
}
if (count($zone) < 1) {
  echo "Unknown error occurred while attempting to create domain.\n";
  exit;
}
$zone = $zone->Next();

// Add our new record to the domain's zone file
$record = $zone->Record();
$record->name = "$fqdn";
$record->type = "A";
$record->data = $ipaddr;
$record->Create();
$zone->Update();
echo "Added A record for \"$fqdn\" to zone file for \"$parentDomain.$tld\"\n";

?>
