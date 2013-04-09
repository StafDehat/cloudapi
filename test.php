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


//var_dump($lbs->LoadBalancerList());

$lbid="120215";
$pool = $lbs->LoadBalancer($lbid);


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
$errorPage = $pool->ErrorPage();
$errorPage->content = $filecontents;
$errorPage->Create();



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
