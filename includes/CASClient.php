<?php
//
//  Central Authentication Service (CAS) light-weight client
//
//  This class implements a CAS 1.0 client, as described here:
//  http://www.jasig.org/cas/protocol
//
//  PHP must be built with support for the CURL library.
//
//  JAH v1.2 2007-03-28 -- feedback to jhalderm[at]cs.princeton.edu
//  SCK v1.3 2010-03-09 -- feedback to scott[at]cs.princeton.edu
//
//
class CASClient 
{
	// URL of trusted CAS provider
	// var $cas_url = 'https://signon.princeton.edu/cas/';
	var $cas_url = 'https://fed.princeton.edu/cas/';

	// Authenticates the remote user, and returns the user's NetID.
	//
	// The first time this function is called, it redirects the
	// browser to a login page to complete the authentication process.
	// The login page then redirects the browser back to the current
	// page.  When this function is called again, it confirms that the
	// login was successful and returns the NetID.  This function will
	// not return unless the user is successfully authenticated.
	function Authenticate()
	{
		// If the request contains a login ticket, try to validate it
		if (isset($_GET['ticket'])) {	
			$netid = $this->Validate($_GET['ticket']);
			if ($netid)
				return $netid; // successful login
			echo "<br />Got 'ticket' but wasn't valid"; // DEBUG
                        exit;
		}

		// No valid ticket; redirect the browser to the login page to get one
		$login_url = $this->cas_url . 'login'
			. '?service=' . urlencode($this->ServiceURL());
		//header('HTTP/1.1 302 Found');
		header('Location: ' . $login_url);
		header('Status-line: HTTP/1.1 307 Temporary Redirect');
		header('');
		exit;
	}

	// Validates a login ticket by contacting the CAS server. If
	// valid, returns the user's NetID; otherwise, returns false.
	function Validate($ticket)
	{
		$validate_url = $this->cas_url . 'validate'
			. '?service=' . urlencode($this->ServiceURL())
			. '&ticket='  . urlencode($_GET['ticket']);
		$r = $this->SecureGetURL($validate_url);
                // echo "<br />sizeof(r)=", sizeof($r), "\n"; // DEBUG
                // echo "<br />r[0] = '" . $r[0] . "'\n"; // DEBUG
                // echo "<br />r[1] = '" . $r[1] . "'\n"; // DEBUG
		if (2 == sizeof($r) && 'yes' == trim($r[0]))
			return trim($r[1]); // ticket was valid, return NetID
		return false;
	}

	// Returns the URL of the current page after stripping out the ticket=
	// parameter added by the CAS server.
	function ServiceURL()
	{
		$url = 'http' . (($_SERVER['HTTPS'] == 'on') ? 's' : '') .
			'://' . $_SERVER['HTTP_HOST'];
		if (!($_SERVER['HTTPS'] == 'on' && $_SERVER['SERVER_PORT'] == '443') &&
			!($_SERVER['HTTPS'] != 'on' && $_SERVER['SERVER_PORT'] == '80'))
			$url .= ':' . $_SERVER['SERVER_PORT'];
		$url .= $_SERVER['REQUEST_URI'];
		$url = preg_replace('/ticket=[^&]*&?/', '', $url);
		return preg_replace('/\?&?$|&$/', '', $url);
	}

	// Retrieves content from a specified URL and returns an array
	// where each element corresponds to a line of the content.
	// Unlike PHP's built in 'file' function, it verifies the server's
	// SSL certificate when retrieving an HTTPS URL.
	function SecureGetURL($url)
	{
		function_exists('curl_init')
			or die('CASClient requires PHP to be built with CURL support.');
		$h = curl_init($url)
			or die('CASClient cannot initialize CURL.');

		// verify that the server's certificate corresponds to its hostname
		curl_setopt($h, CURLOPT_SSL_VERIFYHOST, 2);
		// verify that the certificate was issued by a trusted authority
		//curl_setopt($h, CURLOPT_CAINFO, "cacert.pem");
		//curl_setopt($h, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($h, CURLOPT_SSL_VERIFYPEER, false);
		// return the content as a string
		curl_setopt($h, CURLOPT_RETURNTRANSFER, true);

		$c = curl_exec($h);
		$cerr = curl_error($h); // DEBUG
		curl_close($h);
		if (false !== $c)
			return explode("\n", trim($c));
                echo "<br />curl_error = '" . $cerr . "'\n"; // DEBUG
                echo "<br />SecureGetURL returning false\n"; // DEBUG
		return false;
	}
}

// Sample usage:
//
//   require 'CASClient.php';
//   $C = new CASClient();
//   $netid = $C->Authenticate();
//   echo "Howdy, $netid.";
