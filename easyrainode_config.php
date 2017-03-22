<?php

	// ****************************************
	// CONFIGURATION, EDIT WITH YOUR PARAMETERS
	// ****************************************
	
	// Node connection parameters
	
	DEFINE("RB_HOST","127.0.0.1"); // RaiBlocks node host
	DEFINE("RB_PORT","7076"); // RaiBlocks node port
	DEFINE("RB_URL",null); // I don't know what this is for, just leave null
	
	// Other parameters
	
	DEFINE("ERN_WALLET",""); // Default wallet, type "WALLET" to use this value when CLI ask to you
	
	// Connection to node
	
	$rb = new RaiBlocks(RB_HOST,RB_PORT,RB_URL); // Connect to node
	//$rb->setSSL('/full/path/to/mycertificate.cert'); // Uncomment this if you want to set up a secure SSL connection, you need tool like "stunnel" on the node server
	$rb_ext = $rb; // Enable extensions usage

?>
