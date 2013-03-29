<?php

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

$compute = $RAX->Compute();


$imgName = "AHoward-c2";

// Get a list of servers, grab the smallest
$serverlist = $compute->ServerList();
$sourceServer = $serverlist->Next();
while($x = $serverlist->Next()) {
  if ( $x->flavor->id < $sourceServer->flavor->id )
    $sourceServer = $x;
}
echo "Smallest server:\n";
echo "Name:   ". $sourceServer->name ."\n";
echo "Flavor: ". $sourceServer->flavor->id ."\n";
echo "ID:     ". $sourceServer->id ."\n";
echo "\n";

// Take an image of that server
echo "Taking an image of source server.\n";
$sourceServer->CreateImage($imgName);
// Wait for the image to complete
do {
  $imgList = $compute->ImageList(TRUE, array('name'=>$imgName));
  if (count($imgList) <= 0) {
    echo "Image creation must have failed - can't find one with desired name.\n";
    exit;
  } else {
    $image = $imgList->Next();
    echo "Waiting for image creation to complete...\n";
    sleep(30);
  }
} while (!($image->status == 'ACTIVE'));
echo "Image creation complete.\n";
echo "\n";
echo "Image details:\n";
echo "Name: ". $image->name ."\n";
echo "ID: ". $image->id ."\n";
echo "Min Disk: ". $image->minDisk ."\n";
echo "min RAM: ". $image->minRam ."\n";
echo "Created: ". $image->created ."\n";
echo "\n";


// Spin up a new cloud server from that image
$newServer = $compute->Server();
$newServer->name = $sourceServer->name ."-clone";
$newServer->flavor = $compute->Flavor($sourceServer->flavor->id); //512MB
$newServer->image = $compute->Image($image->id);
$newServer->Create();


// Optionally, wait for the server to finish building
$id = $newServer->id;
$rootpass = $newServer->adminPass;
do {
  echo "New server not yet active.  Sleeping 30s...\n";
  sleep(30);
  $newServer = $compute->Server($id);
} while ( ! ($newServer->status == 'ACTIVE') );
echo "Server build complete\n";
echo "\n";
echo $newServer->name . " details:\n";
echo "Server ID: ". $id ."\n";
echo "IP:        " . $newServer->ip(4) . "\n";
echo "Username:  root\n";
echo "Password:  " . $rootpass . "\n";
echo "\n";

?>
