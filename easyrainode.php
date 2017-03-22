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

	// ************************************************************
	// DO NOT EDIT BELOW, BUT DO IT IF YOU KNOW WHAT YOU ARE DOING!
	// ************************************************************
	
	// Includes
	
	include("easyraiblocks.php"); // Include RPC class
	include("easyraiext.php"); // Include extension class
	include("easyrainode_config.php"); // Include configuration file
	
	// Methods to call
	
	function americanu($number,$decimal){
	
		return number_format($number,$decimal,".",",");
	
	}
	
	function rb_call_method($method,$params = null){
		
		global $rb;
		
		$args = array();
		
		echo $method."\n";
		
		if($params != null){
		
			echo "\n";
		
			foreach( $params as $key=>$param ){
				
				if(substr( $key, -2, 2 ) == "[]"){ // If asked for array, ask only for first element.
					
					echo substr( $key, 0, -2 ).": ";
					$line = stream_get_line( STDIN, 1024, PHP_EOL );
					
					$args[$param] = array($line);
					
				}elseif(substr( $key, -2, 2 ) == "**"){ // If it's an amount, elaborate it as rai.
					
					echo substr( $key, 0, -2 ).": ";
					$line = stream_get_line( STDIN, 1024, PHP_EOL );
					
					$args[$param] = $line.RAI;
				
				}else{
				
					echo $key.": ";
					$line = stream_get_line( STDIN, 1024, PHP_EOL );
					
					if($param == "wallet" && $line == "WALLET"){ $line = ERN_WALLET; }
					
					$args[$param] = $line;
				
				}
				
			}
		
		}
		
		echo "\n";
		
		$result = $rb->{$method}($args);
		
		if( isset($result["balance"]) ){ $result["balance_rai"] = americanu($result["balance"]/RAIN,24); }
		if( isset($result["pending"]) ){ $result["panding_rai"] = americanu($result["balance"]/RAIN,24); }
		if( isset($result["weight"]) ){ $result["weight_rai"] = americanu($result["weight"]/RAIN,24); }
		if( isset($result["count"]) ){ $result["count_readable"] = americanu($result["count"],0); }
		
		print_r($result);
		
		// BACKUP LATEST OUTPUT
		
		$fp = fopen( "latest-output.json", "a" );
		fwrite($fp, "\n\n".json_encode($result));
		fclose($fp);
		
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
		"ar" => array("Account representative","account_representative",array("Account"=>"account")),
		"ars" => array("Account representative set","account_representative_set",array("Wallet" => "wallet", "Account"=>"account", "Representative"=>"representative")),
		"ac" => array("Account create","account_create",array("Wallet"=>"wallet")),
		"al" => array("Account list","account_list",array("Wallet"=>"wallet")),
		"am" => array("Account move","account_move",array("Wallet destination"=>"wallet","Wallet source"=>"source","Account[]"=>"accounts")),
		"aw" => array("Account weight","account_weight",array("Account"=>"account")),
		"av" => array("Validate account number checksum","validate_account_number",array("Account"=>"account")),
		// Generic
		"sep3" => array("Generic","separator"),
		"as" => array("Available supply","available_supply",null),
		"bc" => array("Block count","block_count",null),
		"bo" => array("Bootstrap","bootstrap",array("IP"=>"address","Port"=>"port")),
		"fc" => array("Frontier count","frontier_count",null),
		"rn" => array("Retrieve node version","version",null),
		"rp" => array("Retrieve online peers","peers",null),
		"ka" => array("Keep alive","keepalive",array("IP"=>"address","Port"=>"port")),
		"spe" => array("Search pending","search_pending",array("Wallet"=>"wallet")),
		"pe" => array("Pending","pending",array("Account"=>"account","Count"=>"count")),
		"wg" => array("Work generate","work_generate",array("Hash"=>"hash")),
		"wca" => array("Work cancel","work_cancel",array("Hash"=>"hash")),
		"se" => array("Send","send",array("Wallet source"=>"wallet","Account source"=>"source","Account destination"=>"destination","Rai**"=>"amount")),
		"sn" => array("Stop node","stop",null),
		// Extension
		"sep4" => array("Extensions","separator"),
		"e_bw" => array("Wallet balance","raiblocks_balance_wallet",array("Wallet"=>"wallet")),
		"e_cw" => array("Clear wallet","raiblocks_clear_wallet",array("Wallet"=>"wallet","Destination"=>"destination")),
		"e_sw" => array("Send from wallet","raiblocks_send_wallet",array("Wallet"=>"wallet","Destination"=>"destination","Rai"=>"amount")),
		"e_ra" => array("Set representative for all","raiblocks_representative_all",array("Wallet"=>"wallet","Representative"=>"representative","Further"=>"furhter")),
		"e_na" => array("Create n accounts","raiblocks_n_accounts",array("Wallet"=>"wallet","N accounts"=>"n")),
		// Quit
		"sep5" => array("","separator"),
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
	
	// Installed version
	$cversion = $rb->version();
	$cversion = $cversion["node_vendor"];
	$cversion = str_replace("RaiBlocks ","",$cversion);
	
	// Latest version
	$url = "https://raiblockscommunity.net/get/latest.php";
	$json = file_get_contents($url);
	$json_data = json_decode($json, true);
	$lversion = $json_data["node"];
	
	if( $lversion == $cversion ){ echo "\n\tRunning latest version: ".$cversion; }
	else{ echo "\n\tNEW VERSION AVAILABLE: ".$lversion; }
	
	echo "\n";
	
	while( true ){
	
		// Get input
		
		echo "\nCommand: ";
		$line = stream_get_line( STDIN, 1024, PHP_EOL );
		echo "";
		
		// Check input
		
		if( $line == "q" ){ // Exit
			
			echo "\n";
			
			exit;
			
		}elseif( substr( $line, 0, 2 ) == "e_" && array_key_exists($line,$commands) ){ // Extended functions
			
			echo "\n";
			
			$result = array(); $args = array();
			
			$params = $commands[$line][2]; $args = array();
			
			if($params != null){
			
				echo "\n";
			
				foreach( $params as $key=>$param ){
					
					echo $key.": ";
					$line2 = stream_get_line( STDIN, 1024, PHP_EOL );
					
					if($param == "wallet" && $line2 == "WALLET"){ $line2 = ERN_WALLET; }
					
					$args[] = $line2;
					
				}
			
			}
			
			if( $line == "e_bw" ){
			
				$result = raiblocks_balance_wallet( $args[0] );
			
			}elseif( $line == "e_cw" ){
			
				$result = raiblocks_clear_wallet( $args[0], $args[1] );
			
			}elseif( $line == "e_sw" ){
			
				$result = raiblocks_send_wallet( $args[0], $args[1], $args[2] );
			
			}elseif( $line == "e_ra" ){
			
				$result = raiblocks_representative_all( $args[0], $args[1], $args[2] );
			
			}elseif( $line == "e_na" ){
			
				$result = raiblocks_n_accounts( $args[0], $args[1] );
			
			}else{
				
				// Do nothing.
			
			}
			
			print_r( $result );
			
			// BACKUP LATEST OUTPUT
			
			$fp = fopen( "latest-output.json", "a" );
			fwrite($fp, "\n\n".json_encode($result));
			fclose($fp);
			
		}elseif( array_key_exists($line,$commands) ){ // Normal RPC
			
			echo "\n";
			
			rb_call_method($commands[$line][1],$commands[$line][2]);
			
		}else{ // Unknown command
			
			echo "Unknown command.\n";
			
		}
	
	}
?>
