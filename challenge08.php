<?php
// Challenge 8: Write a script that will create a static webpage served out of Cloud Files.
// The script must create a new container, cdn enable it, enable it to serve an index file,
//  create an index file object, upload the object to the container, and create a CNAME
//  record pointing to the CDN URL of the container.
// Worth 3 Points

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

//$compute = $RAX->Compute();
$ostore = $RAX->ObjectStore();
$dns = $RAX->DNS();

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
    print_r($container);
    usage($argv[0]);
  }
}
// Container doesn't exist - create it
echo "Creating container \"$containerName\"\n";
$container = $ostore->Container();
$container->name = $containerName;
$container->Create();
$container->EnableCDN();


// Make an index file in /tmp
echo "Creating /tmp/index.html\n";
$filename = "index.html";
$filehandle = fopen("/tmp/$filename", 'w') or die ("Unable to write file\n");
$filecontents = "<html>
<head>
  <title>Challenge 8</title>
</head>
<body>
Test page for challenge 8
</body>
</html>";
fwrite($filehandle, $filecontents);
fclose($filehandle);

// Upload index file to new Cloud Files container
echo "Uploading new index file to Cloud Files\n";
$file = $container->DataObject();
$file->Create(array('name'=>$filename), "/tmp/$filename");

// Declare this new index file as the DirectoryIndex for the container
echo "Setting metadata to make index file a static site.\n";
$container->metadata = array('X-Container-Meta-Web-Index'=>$filename);
$container->CreateStaticSite("index.html");


// Add a CNAME record
$fqdn = "files.cloud.rootmypc.net";
$subDomain = "files.cloud";
$parentDomain = "rootmypc";
$tld = "net";
$filesfqdn = substr($container->PublicURL(), 7); //Trim "http://" off the front

// Get a list of domains, see if ours already exists
$domainlist = $dns->DomainList();
$exists = false;
while($zone = $domainlist->Next()) {
  if ($zone->name == "$parentDomain.$tld") {
    echo "Parent domain already exists.\n";
    $exists = true;
    break;
  }
}
// Create domain if it doesn't already exist
if ( ! $exists ) {
  echo "Parent domain does not exist.  Creating...\n";
  $zone = $dns->Domain();
  $zone->name = "$parentDomain.$tld";
  $zone->emailAddress = "admin@$parentDomain.$tld";
  $zone->Create();
  sleep(5); // TODO Actually verify the domain created successfully
  echo "Parent domain created.\n";
  $zone = $dns->DomainList(array("name" => "$parentDomain.$tld"))->Next();
}

// Add our new record to the domain's zone file
$record = $zone->Record();
$record->name = $fqdn;
$record->type = "CNAME";
$record->data = $filesfqdn;
$record->Create();
$zone->Update();
echo "Added CNAME record for \"$fqdn\" to zone file for \"$parentDomain.$tld\"\n";


?>
