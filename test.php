<?php

function is_valid_ip4($address) {
  return (preg_match("/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/", $address));
}

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

$compute = $RAX->Compute();
$lbs = $RAX->LoadBalancerService("cloudLoadBalancers", "DFW", "publicURL");
$ostore = $RAX->ObjectStore();
$dns = $RAX->DNS();
//$RAX->LoadBalancerService("HealthMonitor", "DFW", "publicURL");

$lbid="118973";
$lb = $lbs->LoadBalancer($lbid);

$healthMon = new \OpenCloud\LoadBalancerService\HealthMonitor(
  $lb,
  array("type"=>"CONNECT",
        "delay"=>"10",
        "timeout"=>"5",
        "attemptsBeforeDeactivation"=>"2") );
$lb->healthMonitor = $healthMon;
print_r($healthMon);
//$healthMon->Create();


//curl -i -H "X-Auth-Token: " -H "Content-Type: application/xml" -H "Accept: application/xml" https://dfw.loadbalancers.api.rackspacecloud.com/v1.0/DDI/loadbalancers
//<healthMonitor type="HTTP" delay="10" timeout="10" attemptsBeforeDeactivation="3" path="/" statusRegex="200"/>


/**
$imagelist = $compute->ImageList();
$imagelist->Sort('name');   // sort by name
while($image = $imagelist->Next())
    printf("Image: %s\n", $image->name);


$flavorlist = $compute->FlavorList();
$flavorlist->Sort();    // The default sort key is 'id'
while($flavor = $flavorlist->Next()) {
    printf("Flavor: %s RAM=%d\n", $flavor->name, $flavor->ram);
  echo "Flavor ID: $flavor->id\n";
}

echo "\n";

if ($flavor = $compute->Flavor(9)) {
  echo "Flavor $flavor->name has ID $flavor->id and $flavor->ram MB RAM\n";
} else {
  echo "Flavor ID not found \n";
}
**/
?>
