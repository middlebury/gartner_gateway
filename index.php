<?php
/**
 * Gartner login gateway.
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once('phpcas/source/CAS.php');

phpCAS::client(CAS_VERSION_2_0,'login.middlebury.edu', 443,'/cas', true);

// The ipsCA certificate may be installed on your machine already 
// (it is in the bundle at /etc/pki/tls/certs/ca-bundle.crt on Red Hat Enterprise Linux 5). 
// If so, you should be able to use that bundle file:
phpCAS::setCasServerCACert('/etc/pki/tls/certs/ca-bundle.crt');

// force CAS authentication
phpCAS::forceAuthentication();

print "Authenticated.";