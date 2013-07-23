<?php
	/**
	 * domainchecker.php
	 * A simple domain Availability Checker for domain.com
	 *
	 * This script can be ran from command line via
	 *  `php domainchecker.php` or via a web server/browser
	 *
	 * @author Brandon Skrtich <bskrtich@gmail.com>
	 */
	
	/* 
	 * A Chainable, REST Friendly, PHP HTTP Client. A sane alternative to cURL.
	 * http://phphttpclient.com
	*/
	require('httpful-0.2.4.phar');


	/* Base Abstract Class for Service Checking */
	abstract class HTTPServiceLookUp {
	    // Force Extending class to define this method
	    abstract public function SendCheckRequest($Name, $TLD);
	}

	/* Classes for Checking Different Services */
	class DomainAvailability_NameComGet extends HTTPServiceLookUp {
		public function SendCheckRequest($Name, $TLD) {
			$response = \Httpful\Request::get('http://api.dev.name.com/api/domain/check/'.$Name.'/'.$TLD)->send();
			if ($response->code === 200) { // Check HTTP Responce Code for Success (200)
				$data = json_decode($response->body);
			
				if ($data->result->code === 100) { // Check for Success from Service (100)
					$domain = $Name.'.'.$TLD;
				
					return $data->domains->$domain->avail;
				}
			}
			return False;
		}
	}

	class DomainAvailability_NameComPost extends HTTPServiceLookUp {
		public function SendCheckRequest($Name, $TLD) {
			$data = json_encode(array('keyword' => $Name, 'tlds' => array($TLD)));
			$response = \Httpful\Request::put('http://api.dev.name.com/api/domain/check', $data)->send();
			if ($response->code === 200) { // Check HTTP Responce Code for Success (200)
				$data = json_decode($response->body);
			
				if ($data->result->code === 100) { // Check for Success from Service (100)
					$domain = $Name.'.'.$TLD;
				
					return $data->domains->$domain->avail;
				}
			}
		
			return False;
		}
	}

	/* Main Class for checking Domain Availability */
	// 
	// If I was scaling to 100's of TLD's, I would want
	// to use a database with a list of TLD and assocated
	// services. This would allow configuation of each
	// service and quick lookup for the large dataset.
	// 
	// This script uses an array for portability
	class CheckDomainAvailability {
		private $TLDService;
		private $Name;
		private $TLD;
		private $TLDClassList = array( // Add class names and TDL here
			'DomainAvailability_NameComGet' => array('com', 'net'),
			'DomainAvailability_NameComPost' => array('org', 'info'),
		);
	
		function __construct($TLD = '') {
			if ($TLD !== '') $this->SetTLD($TLD);
		}
		
		// Load Different Checking Services by TLD
		function SetTLD($TLD) {
			$this->TLD = $TLD;
		
			$this->TLDService = NULL;
		
			foreach ($this->TLDClassList as $ClassName => $TLDList) {
				if (in_array($TLD, $TLDList)) {
					$this->TLDService = New $ClassName();
				}
			}
		
			if ($this->TLDService == NULL) {
				throw new Exception('Service for TLD Not Available');
			}
		}
		
		// Check for a Domain
		public function CheckDomain($Name, $TLD = '') {
			$this->Name = $Name;
			if ($TLD !== '') $this->SetTLD($TLD);
		
			return $this->TLDService->SendCheckRequest($this->Name, $this->TLD);
		}
	}
	
	// Defaults
	$domain = NULL;
	
	// Meat of the code
	if (PHP_SAPI == 'cli') {
		echo "\n  ___                 _         ___ _           _           ";
		echo "\n |   \ ___ _ __  __ _(_)_ _    / __| |_  ___ __| |_____ _ _ ";
		echo "\n | |) / _ \ '  \/ _` | | ' \  | (__| ' \/ -_) _| / / -_) '_|";
		echo "\n |___/\___/_|_|_\__,_|_|_||_|  \___|_||_\___\__|_\_\___|_|  ";	
		echo "\n\n";
		
		echo "\nEnter a Domain to check and press [Enter]: ";
		
		$_REQUEST['domain'] = trim(fgets(STDIN));
	}
	
	// Clean All User Input
	if (isset($_REQUEST['domain'])) {
		// Strip all but alpanumeric and dash
		$domain = preg_replace("/[^a-z0-9-.]+/i", "", $_REQUEST['domain']);
		
		// Remove all but TLD and SLD
		$domain = explode('.', $domain, substr_count($domain, '.'));
		$domain = end($domain);
	}
	
	// Do Stuff if needed data is avaiable
	if ($domain !== NULL) {
		// Proccess $domain in to the Name and TLD
		list($LookUpName, $LookUpTLD) = explode('.', $domain);
		
		if (strlen($LookUpTLD) > 0 && strlen($LookUpName) > 0) {
		
			// Check for Domain
			try {
				$DomainChecker = New CheckDomainAvailability($LookUpTLD);
				if ($DomainChecker->CheckDomain($LookUpName)) {
					$output = "\nThe Domain ".$LookUpName.".".$LookUpTLD." is Available";
				} else {
					$output = "\nThe Domain ".$LookUpName.".".$LookUpTLD." is Not Available";
				}
			} catch (exception $e) {
				$output = "\nWe are currently unable to check for the domain '".$domain."'";
			}
		} else {
			$output = "\nThe Domain you entered '".$domain."' is not a valid domain.";
		}
	}
	
	if (PHP_SAPI == 'cli') {
		echo $output;
		
		echo "\n";
		exit("\nEnd Script\n");
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title>Domain Checker</title>
	</head>
	<body style="text-align: center;">
<pre>
  ___                 _         ___ _           _           
 |   \ ___ _ __  __ _(_)_ _    / __| |_  ___ __| |_____ _ _ 
 | |) / _ \ '  \/ _` | | ' \  | (__| ' \/ -_) _| / / -_) '_|
 |___/\___/_|_|_\__,_|_|_||_|  \___|_||_\___\__|_\_\___|_|  
</pre>
		<?php if (isset($output)) echo '<h2>'.$output.'</h2>'; ?>
		<form style="text-align: center;" id="form1" name="form1" method="post" action="">
			<label for="domain">Domain:</label>
			<input type="text" name="domain" id="domain" />
			<input type="submit"id="submit" value="Check" />
		</form>
	</body>
</html>