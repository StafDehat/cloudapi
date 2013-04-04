#!/usr/bin/php
<?php
/** Challenge 10: Write an application that will:
- Create 2 servers, supplying a ssh key to be installed at /root/.ssh/authorized_keys.
- Create a load balancer
- Add the 2 new servers to the LB
- Set up LB monitor and custom error page. 
- Create a DNS record based on a FQDN for the LB VIP. 
- Write the error page html to a file in cloud files for backup.
Whew! That one is worth 8 points!
**/

require_once('opencloud/lib/rackspace.php');
require_once('./auth.php');

//$compute = $RAX->Compute();
$ostore = $RAX->ObjectStore();

