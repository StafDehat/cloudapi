<?php

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

// Some hard-coded crap
$cent63id = 'c195ef3b-9195-4474-b6f7-16e5bd86acd0';

$compute = $RAX->Compute();

$serverlist = $compute->ServerList();
while($server = $serverlist->Next())
    print($server->name."\n");

?>
