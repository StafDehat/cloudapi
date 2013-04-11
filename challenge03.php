<?php
// Challenge 3: Write a script that accepts a directory as an argument as well as a container name. The script should upload the contents of the specified directory to the container (or create it if it doesn't exist). The script should handle errors appropriately. (Check for invalid paths, etc.) Worth 2 Points

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

//$compute = $RAX->Compute();
$ostore = $RAX->ObjectStore();

function usage($self) {
  echo "Usage: php $self local-dir cloud-files-container\n";
  exit;
}


// Ensure proper number of args
if (count($argv) != 3) {
  echo "Error: Incorrent number of arguments.\n";
  usage($argv[0]);
}
$dirName = $argv[1];
$containerName = $argv[2];
// Verify provided directory exists
if ( ! (file_exists($dirName) && is_dir($dirName)) ) {
  echo "Error: Directory \"$dirName\" does not exist.\n";
  usage($argv[0]);
}
if ( ! is_readable($dirName) ) {
  echo "Error: Directory \"$dirName\" is not readable.\n";
  usage($argv[0]);
}
// Ensure container name is valid
if (! ctype_alnum($containerName)) {
  echo "Error: Container name's gotta be alpha-numeric, bro.\n";
  usage($argv[0]);
}


// Test if container exists in cloud files
// If not, create it
$exists = false;
$containerlist = $ostore->ContainerList();
while($container = $containerlist->Next()) {
  if ($container->name == $containerName) {
    echo "Found a container named \"$containerName\".\n";
    $exists = true;
    break;
  }
}
if (! $exists) {
  echo "No container exists with name \"$containerName\" - creating it now.\n";
  $container = $ostore->Container();
  $container->name = $containerName;
  $container->Create();
}


// Iterate over contents of DIR, uploading each to cloud files
foreach (scandir($dirName) as $fileName) {
  if (is_dir("$dirName/$fileName")) continue;
  echo "Uploading \"$dirName/$fileName\" to container \"$containerName\".\n";
  $file = $container->DataObject();
  $file->Create(array('name'=>$fileName), "$dirName/$fileName");
}


?>
