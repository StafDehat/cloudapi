<?php

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

$compute=$RAX->Compute();

$imgName = "AHoward-c02";
$imgList = $compute->ImageList(TRUE, array('name'=>$imgName));
$image = $imgList->Next();
echo "Status: $image->status\n";


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
