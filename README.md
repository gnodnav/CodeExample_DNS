CodeExample_DNS
===============

This is a PHP/OOP code example requested by a potential employer.


===============

This script can be run in a web server to a web browser or directly via php command line.

===============

This example was created to meet these requirement:


Please create a script that takes one domain as an argument (with a TLD of com, net, org, or info) and prints whether or not the domain is available.  This is returned from the API in the "avail" attribute.

To simulate different protocols, assume .com and .net use a GET request to the following URL:

http://api.dev.name.com/api/domain/check/<keyword>/<tld>
Example: http://api.dev.name.com/api/domain/check/examplesearch/com

Whereas .org and .info use a POST against this URL:

http://api.dev.name.com/api/domain/check
POST data: {"keyword":"<keyword>","tlds":["<tld>"]}
Example: curl -d '{"keyword":"examplesearch","tlds":["org"]}' -X POST http://api.dev.name.com/api/domain/check

Taking into consideration there would in the future be hundreds of TLDs as well as different protocols, we're most interested to see use of OOP and inheritance, reusability of code, and overall coding style.