#!/usr/bin/php
<?php
// Challenge 9: Write an application that when passed the arguments FQDN, image,
//  and flavor it creates a server of the specified image and flavor with the
//  same name as the fqdn, and creates a DNS entry for the fqdn pointing to the
//  server's public IP.
// Worth 2 Points

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

$compute = $RAX->Compute();
$dns = $RAX->DNS();


function usage($self) {
  echo "Usage: php $self FQDN Image-Name Flavor-ID\n";
  exit;
}
function is_valid_domain_name($domain_name) {
  return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
       && preg_match("/^.{1,253}$/", $domain_name) //overall length check
       && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name)   ); //length of each label
}


// Ensure proper number of args
if (count($argv) != 4) {
  echo "Error: Incorrent number of arguments.\n";
  usage($argv[0]);
}
$fqdn = $argv[1];
$imgName = $argv[2];
$flavorID = $argv[3];
// Validate domain
if ( ! is_valid_domain_name($fqdn) ) {
  echo "Error: Invalid domain name.\n";
  echo "  Valid domain characters are letters, numbers, hypens, and/or underscores.\n";
  usage($argv[0]);
}
// Validate image name
if ( ! is_alnum($imgName) ) {
  echo "Error: Image name must be alpha-numeric.\n";
  usage($argv[0]);
}
// Validate flavour ID
if ( ! is_num($flavorID) ) {
  echo "Error: Flavor ID must be numeric.\n";
  usage($argv[0]);
}


// Verify $imgName is actually a valid image


// Verify flavorID is a valid flavour ID








// Create DNS entry for FQDN
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
  sleep(5); // TODO Actually test and verify domain created successfully
  echo "Parent domain created.\n";
  $zone = $dns->DomainList(array("name" => "$parentDomain.$tld"))->Next();
}

// Add our new record to the domain's zone file
$record = $zone->Record();
$record->name = $fqdn;
$record->type = "CNAME";
$record->data = $filesfqdn;
$record->Create();
$zone->Update();
echo "Added A record for \"$fqdn\" to zone file for \"$parentDomain.$tld\"\n";



?>
