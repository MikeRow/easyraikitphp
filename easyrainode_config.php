<?php

	// ****************************************
	// CONFIGURATION, EDIT WITH YOUR PARAMETERS
	// ****************************************
	
	// Node connection parameters
	
	DEFINE("RB_HOST","139.162.231.197"); // RaiBlocks node host
	DEFINE("RB_PORT","7076"); // RaiBlocks node port
	DEFINE("RB_URL",null); // I don't know what this is for, just leave null
	
        
        error_reporting(E_ERROR | E_WARNING | E_PARSE);
        
	// Other parameters
	
	$dwallets = array( // Default wallets, type the wallet tag to use the wallet ID when CLI ask to you
	
		"fullnode" => "1711635F5292E79A74767FE4B48491CAD3454F8DFB4A543327BCA34029983F8E"
	
	);
        
        $daccounts = array( // Default wallets, type the wallet tag to use the wallet ID when CLI ask to you
	
		"fullnode" => "xrb_1ztxehegbtkx5swheeh5domysiea9miwie3ap96o8dmcf7j14qmw4zcffemz"
	
	);
        
        $dkey = "fullnode";
        
        
		
	// Connection to node
	
	$rb = new RaiBlocks(RB_HOST,RB_PORT,RB_URL); // Connect to node
	//$rb->setSSL('/full/path/to/mycertificate.cert'); // Uncomment this if you want to set up a secure SSL connection, you need tool like "stunnel" on the node server
	$rb_ext = $rb; // Enable extensions usage

?>
