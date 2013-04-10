<?php

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');


function usage($self) {
  exit;
}


$authkeys = "";
$homedir = $_SERVER['HOME'];

if (count($argv) == 2 ) {
$localkey = $argv[1];
  // Verify provided directory exists
  if ( file_exists($localkey) &&
       is_readable($localkey) ) {
    echo "Found public SSH key at \"$localkey\" - Gonna upload it.\n";
    $authkeys = $authkeys ."\n". file_get_contents($localkey);
  }
}
if ( file_exists( "$homedir/.ssh/id_rsa.pub" ) &&
     is_readable( "$homedir/.ssh/id_rsa.pub" ) ) {
  echo "Found public SSH key at \"$homedir/.ssh/id_rsa.pub\" - Gonna upload it.\n";
  $authkeys = $authkeys ."\n". file_get_contents("$homedir/.ssh/id_rsa.pub");
}
if ( file_exists( "$homedir/.ssh/id_dsa.pub" ) &&
     is_readable( "$homedir/.ssh/id_dsa.pub" )) {
  echo "Found public SSH key at \"$homedir/.ssh/id_dsa.pub\" - Gonna upload it.\n";
  $authkeys = $authkeys ."\n". file_get_contents("$homedir/.ssh/id_dsa.pub");
}

echo "Auth Keys:\n$authkeys\n";

/**
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
