<?php

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

$compute = $RAX->Compute();


/**
$flavors = $compute->FlavorList(FALSE);
while($flavor = $flavors->Next())
    printf("Flavor %s has %d GB of RAM and %dGB of disk\n",
        $flavor->id, $flavor->name, $flavor->disk);

echo "\n";

$imlist = $compute->ImageList();
while( $image = $imlist->Next() )
    printf("Image: %s id=%s\n", $image->name, $image->id);
**/


//$servers = $compute->ServerList(TRUE, array('flavor'=>'512'));
$servers = $compute->ServerList();
while($server = $servers->Next()) {
  echo "Server Name: ". $server->name ."\n";
  echo "ID:          ". $server->id ."\n";
  echo "Flavor:      ". $server->flavor->id ."\n";
}


//$flavor = $compute->Flavor(2);
//print_r($flavor);


//$server = $compute->Server();
//$server->name = 'AHoward-Test';
//$server->flavor = $compute->Flavor(1);
//$server->image = $compute->Image('c195ef3b-9195-4474-b6f7-16e5bd86acd0');
//$server->Create();


?>
