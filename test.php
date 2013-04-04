<?php

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

$compute = $RAX->Compute();
$dns = $RAX->DNS();

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

?>
