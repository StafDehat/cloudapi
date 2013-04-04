<?php
// Challenge 6: Write a script that creates a CDN-enabled container in Cloud Files. Worth 1 Point

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

//$compute = $RAX->Compute();
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
$containerlist = $ostore->ContainerList();
while($container = $containerlist->Next()) {
  if ($container->name == $containerName) {
    echo "Error: Container \"$containerName\" already exists.\n";
    usage($argv[0]);
  }
}

$container = $ostore->Container();
$container->name = $containerName;
$container->Create();
$container->EnableCDN();

?>
