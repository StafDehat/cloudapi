cloudapi
========

API Challenge by Support, for Support

 1339  git clone https://github.com/StafDehat/cloudapi
 1344  cd cloudapi/
 1357  git remote set-url origin ssh://git@github.com/StafDehat/cloudapi.git
 1348  git add *.php 
 1355  git status
 1370  git commit
 1371  git push

PHP-Opencloud libraries:
http://docs.rackspace.com/sdks/guide/content/php.html

API quick-reference:
https://github.com/rackspace/php-opencloud/blob/master/docs/quickref.md

Forums:
https://forums.rackspace.corp/categories/api-challenges


Ok, this sounds like fun, bring on the challenges!

Here are this week's Challenges:
·        Challenge 1: Write a script that builds three 512 MB Cloud Servers that follow a similar naming convention (ie., web1, web2, web3) and returns the IP and login credentials for each server. Use any image you want. Worth 1 point
·        Challenge 2: Write a script that clones a server (takes an image and deploys the image as a new server). Worth 2 Points
·        Challenge 3: Write a script that accepts a directory as an argument as well as a container name. The script should upload the contents of the specified directory to the container (or create it if it doesn't exist). The script should handle errors appropriately. (Check for invalid paths, etc.) Worth 2 Points
·        Challenge 4: Write a script that uses Cloud DNS to create a new A record when passed a FQDN and IP address as arguments. Worth 1 Point
·        Challenge 5: Write a script that creates a Cloud Database instance. This instance should contain at least one database, and the database should have at least one user that can connect to it. Worth 1 Point


Challenge 6: Write a script that creates a CDN-enabled container in Cloud Files. Worth 1 Point
Challenge 7: Write a script that will create 2 Cloud Servers and add them as nodes to a new Cloud Load Balancer. Worth 3 Points
Challenge 8: Write a script that will create a static webpage served out of Cloud Files. The script must create a new container, cdn enable it, enable it to serve an index file, create an index file object, upload the object to the container, and create a CNAME record pointing to the CDN URL of the container. Worth 3 Points
Challenge 9: Write an application that when passed the arguments FQDN, image, and flavor it creates a server of the specified image and flavor with the same name as the fqdn, and creates a DNS entry for the fqdn pointing to the server's public IP. Worth 2 Points
Challenge 10: Write an application that will:
- Create 2 servers, supplying a ssh key to be installed at /root/.ssh/authorized_keys.
- Create a load balancer
- Add the 2 new servers to the LB
- Set up LB monitor and custom error page. 
- Create a DNS record based on a FQDN for the LB VIP. 
- Write the error page html to a file in cloud files for backup.
Whew! That one is worth 8 points!
