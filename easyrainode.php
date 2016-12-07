<?php

	/*

	easyrainodephp

	====================

	LICENSE: Use it as you want!

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.

	====================

	*/

	// ****************************************
	// CONFIGURATION, EDIT WITH YOUR PARAMETERS
	// ****************************************

	DEFINE("RB_HOST","127.0.0.1"); // RaiBlocks node host
	DEFINE("RB_PORT","7076"); // RaiBlocks node port
	DEFINE("RB_URL",null); // I don't know what this is for, just leave null

	// ************************************************************
	// DO NOT EDIT BELOW, BUT DO IT IF YOU KNOW WHAT YOU ARE DOING!
	// ************************************************************
	
	DEFINE("RAI", "000000000000000000000000"); // A string to concatenate to build 1Rai value
	
	// Include easyraiblocksphp class
	
	include("easyraiblocks.php");
	
	$rb = new RaiBlocks(RB_HOST,RB_PORT,RB_URL);
	//$rb->setSSL('/full/path/to/mycertificate.cert'); // Uncomment this if you want to set up a secure SSL connection, you need tool like "stunnel" on the node server
	
	// Methods to call
	
	function rb_call_method($method,$params = null){
		
		global $rb;
		
		$args = array();
		
		echo $method."\n";
		
		if($params != null){
		
			echo "\n";
		
			foreach( $params as $key=>$param ){
				
				if(substr( $key, -2, 2 ) == "[]"){
					
					echo substr( $key, 0, -2 ).": ";
					$line = stream_get_line( STDIN, 1024, PHP_EOL );
					
					$args[$param] = array($line);
					
				}elseif(substr( $key, -2, 2 ) == "**"){
					
					echo substr( $key, 0, -2 ).": ";
					$line = stream_get_line( STDIN, 1024, PHP_EOL );
					
					$args[$param] = $line.RAI;
				
				}else{
				
					echo $key.": ";
					$line = stream_get_line( STDIN, 1024, PHP_EOL );
					$args[$param] = $line;
				
				}
				
			}
		
		}
		
		echo "\n";
		
		print_r( $rb->{$method}($args) );
		
	}
	
	// List of commands
	
	$commands = array(
	
		// Wallet
		"sep1" => array("Wallet","separator"),
		"wc" => array("Wallet create","wallet_create",null),
		"wd" => array("Wallet destroy","wallet_destroy",array("Wallet"=>"wallet")),
		"wco" => array("Wallet contains","wallet_contains",array("Wallet"=>"wallet","Account"=>"account")),
		"we" => array("Wallet export","wallet_export",array("Wallet"=>"wallet")),
		"wa" => array("Wallet add key","wallet_add",array("Wallet"=>"wallet","Key"=>"key")),
		"wch" => array("Wallet change password","password_change",array("Wallet"=>"wallet","Password"=>"password")),
		"wp" => array("Wallet password enter","password_enter",array("Wallet"=>"wallet","Password"=>"password")),
		"wv" => array("Wallet valid password","password_valid",array("Wallet"=>"wallet")),
		"wr" => array("Wallet representative","representative",array("Wallet"=>"wallet")),
		"wrs" => array("Wallet representative set","representative_set",array("Wallet"=>"wallet","Representative"=>"representative")),
		// Account
		"sep2" => array("Account","separator"),
		"ab" => array("Account balance","account_balance",array("Account"=>"account")),
		"ac" => array("Account create","account_create",array("Wallet"=>"wallet")),
		"al" => array("Account list","account_list",array("Wallet"=>"wallet")),
		"am" => array("Account move","account_move",array("Wallet destination"=>"wallet","Wallet source"=>"source","Account[]"=>"accounts")),
		"aw" => array("Account weight","account_weight",array("Account"=>"account")),
		"av" => array("Validate account number checksum","validate_account_number",array("Account"=>"account")),
		// Generic
		"sep3" => array("Generic","separator"),
		"as" => array("Available supply","available_supply",null),
		"bc" => array("Block count","block_count",null),
		"fc" => array("Frontier count","frontier_count",null),
		"rn" => array("Retrieve node version","version",null),
		"rp" => array("Retrieve online peers","peers",null),
		"ka" => array("Keep alive","keepalive",array("Address"=>"address","Port"=>"port")),
		"se" => array("Send","send",array("Wallet source"=>"wallet","Account source"=>"source","Account destination"=>"destination","Rai**"=>"amount")),
		"sn" => array("Stop node","stop",null),
		// Quit
		"sep4" => array("","separator"),
		"q" => array("Quit","rb_quit")
	
	);
	
	echo "\n";
	
	// Print commands menu
		
	foreach( $commands as $key => $command ){
		
		if( $command[1] == "separator" ){
			
			echo "\n\t".$command[0]."\n\n";
			
		}
		else{
			
			echo "\t".$key."\t".$command[0]."\n";
		
		}
		
	}
	
	while( true ){
	
		// Get input
		
		echo "\nCommand: ";
		$line = stream_get_line( STDIN, 1024, PHP_EOL );
		echo "";
		
		// Check input
		
		if( $line == "q" ){ // Exit
			
			echo "\n";
			
			exit;
			
		}elseif( array_key_exists($line,$commands) ){ // Right command
			
			echo "\n";
			
			rb_call_method($commands[$line][1],$commands[$line][2]);
			
		}else{ // Unknown command
			
			echo "Unknown command.\n";
			
		}
	
	}
?>
