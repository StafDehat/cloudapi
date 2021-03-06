<?php
// Challenge 2: Write a script that clones a server (takes an image and deploys the image as a new server). Worth 2 Points

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

$compute = $RAX->Compute();


$imgName = "AHoward-c02";

// Get a list of servers, grab the smallest
$serverlist = $compute->ServerList();
if ( count($serverlist) < 1 ) {
  echo "Error: Tried to find smallest server on account, but found no servers at all.\n";
  exit;
}
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
} while ($image->status == 'SAVING');

if (!($image->status == 'ACTIVE')) {
  echo "Unknown error encountered while saving image.\n";
  echo "Image status: $image->status\n";
  exit;
}

echo "Image creation complete.\n";
echo "\n";
echo "Image details:\n";
echo "Name:     ". $image->name ."\n";
echo "ID:       ". $image->id ."\n";
echo "Min Disk: ". $image->minDisk ."\n";
echo "min RAM:  ". $image->minRam ."\n";
echo "Created:  ". $image->created ."\n";
echo "\n";


// Spin up a new cloud server from that image
$newServer = $compute->Server();
$newServer->name = $sourceServer->name ."-clone";
$newServer->flavor = $compute->Flavor($sourceServer->flavor->id); //512MB
$newServer->image = $compute->Image($image->id);
$newServer->Create();


// Wait for the server to finish building
$id = $newServer->id;
$rootpass = $newServer->adminPass;
do {
  echo "New server not yet active.  Sleeping 30s...\n";
  sleep(30);
  $newServer = $compute->Server($id);
} while ($newServer->status == 'BUILD');

if (!($newServer->status == 'ACTIVE')) {
  echo "Unknown error encountered while building server.\n";
  echo "Server status: $newServer->status\n";
  exit;
}

echo "Server build complete\n";
echo "\n";
echo $newServer->name . " details:\n";
echo "Server ID: ". $id ."\n";
echo "IP:        " . $newServer->ip(4) . "\n";
echo "Username:  root\n";
echo "Password:  " . $rootpass . "\n";
echo "\n";

?>
