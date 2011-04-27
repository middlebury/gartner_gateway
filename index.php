<?php
/**
 * Gartner login gateway.
 *
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

// config
$institutionGroup = "CN=institution,OU=General,OU=Groups,DC=middlebury,DC=edu";
$shortName = "shortname";
$secretWord = "secretword";


require_once('phpcas/source/CAS.php');

phpCAS::client(CAS_VERSION_2_0,'login.middlebury.edu', 443,'/cas', true);

// The ipsCA certificate may be installed on your machine already 
// (it is in the bundle at /etc/pki/tls/certs/ca-bundle.crt on Red Hat Enterprise Linux 5). 
// If so, you should be able to use that bundle file:
phpCAS::setCasServerCACert('/etc/pki/tls/certs/ca-bundle.crt');

// force CAS authentication
phpCAS::forceAuthentication();


// Verify that we have the needed attributes.
$errors = array();
if (!phpCAS::hasAttribute('FirstName') || !strlen(phpCAS::getAttribute('FirstName')))
	$errors[] = "Your account has no FirstName.";
else
	$firstname = phpCAS::getAttribute('FirstName');
	
if (!phpCAS::hasAttribute('LastName') || !strlen(phpCAS::getAttribute('LastName')))
	$errors[] = "Your account has no LastName.";
else
	$lastname = phpCAS::getAttribute('LastName');
	
if (!phpCAS::hasAttribute('EMail') || !strlen(phpCAS::getAttribute('EMail')))
	$errors[] = "Your account has no EMail.";
else
	$email = phpCAS::getAttribute('EMail');

// Verify Group membership to exclude guests.
if (!phpCAS::hasAttribute('MemberOf'))
	$errors[] = "Error fetching group membership. No groups found.";

$memberOf = phpCAS::getAttribute('MemberOf');
if (is_string($memberOf)) {
	if ($memberOf != $institutionGroup)
		$errors[] = "You aren't a member of the allowed group: institution";
} else if (is_array($memberOf)) {
	if (!in_array($institutionGroup, $memberOf))
		$errors[] = "You aren't a member of the allowed group: institution";
} else {
	$errors[] = "Error fetching group membership. No groups found.";
}

	
if (count($errors)) {
	print <<<END
<html>
<head>
	<title>Gartner Login - Error</title>
</head>
<body>
	<h1>Gartner Login</h1>
	<h2>Authentication Error</h2>
	<ul>
END;
	foreach ($errors as $error) {
		print "<li>".$error."</li>";
	}
	print <<<END
	</ul>
</body>
</html>
END;
	exit;
}

/*********************************************************
 * Build the Gartner URL
 *********************************************************/
// Steps 1 & 2
$query = 'fn='.$firstname
		.appendParameter('ln', $lastname)
		.appendParameter('em', $email)
		.appendParameter('dt', time())
		.appendParameter('comp', $shortName);
 
// Step 4
$credential = $query.'&'.$secretWord;

// Step 5
$md5 = md5($credential);

// Step 6.
$query = $query.appendParameter('md5', $md5);

// Step 7.
$base64Query = base64_encode($query);

// Step 8.
$gartnerUrl = 'https://www.gartner.com/enterprise_access/common/eacportal.jsp?msg='.$base64Query;

// Step 9.
$gartnerUrl .= '&comp='.$shortName;

// print($gartnerUrl);

header('Location: '.$gartnerUrl);


function appendParameter($param, $value) {
  if ($value) {
	 return '&'.$param.'='.$value;
  } else {
	 return '';
  }
}