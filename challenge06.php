<?php
// Challenge 6: Write a script that creates a CDN-enabled container in Cloud Files. Worth 1 Point

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

$ostore = $RAX->ObjectStore();

function usage($self) {
  echo "Usage: php $self cloud-files-container\n";
  exit;
}


// Ensure proper number of args
if (count($argv) != 2) {
  echo "Error: Incorrent number of arguments.\n";
  usage($argv[0]);
}
$containerName = $argv[1];
// Ensure container name is valid
if (! ctype_alnum($containerName)) {
  echo "Error: Container name's gotta be alpha-numeric, bro.\n";
  usage($argv[0]);
}


// Test if container exists in cloud files
$exists = false;
$containerList = $ostore->ContainerList();
while($container = $containerList->Next()) {
  if ($container->name == $containerName) {
    echo "Error: Container \"$containerName\" already exists.\n";
    usage($argv[0]);
  }
}

// Create container
echo "Creating container \"$containerName\"\n";
$container = $ostore->Container();
$container->name = $containerName;
$container->Create();

// Verify container got created.
$containerList = $ostore->ContainerList(array("name"=>$containerName));
if (count($containerList) < 1) {
  echo "Unknown error occurred while creating container.\n";
  exit;
}
echo "Container created successfully.\n";

// Enable CDN
echo "Enabling CDN on container.\n";
$container->EnableCDN();

?>
