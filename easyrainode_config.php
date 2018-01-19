<?php

	// ****************************************
	// CONFIGURATION, EDIT WITH YOUR PARAMETERS
	// ****************************************
	
	// Node connection parameters
	
	DEFINE("RB_HOST","139.162.231.197"); // RaiBlocks node host
	DEFINE("RB_PORT","7076"); // RaiBlocks node port
	DEFINE("RB_URL",null); // I don't know what this is for, just leave null
	
	// Other parameters
	
	$dwallets = array( // Default wallets, type the wallet tag to use the wallet ID when CLI ask to you
	
		"fullnode" => "18FF0CCC0976A958D130A356899DD49AC2DC94B24351F7B761A6A5BA243EC731"
	
	);
		
	// Connection to node
	
	$rb = new RaiBlocks(RB_HOST,RB_PORT,RB_URL); // Connect to node
	//$rb->setSSL('/full/path/to/mycertificate.cert'); // Uncomment this if you want to set up a secure SSL connection, you need tool like "stunnel" on the node server
	$rb_ext = $rb; // Enable extensions usage

?>
