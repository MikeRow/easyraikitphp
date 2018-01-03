<?php

	/*

	easyraiextphp

	Allows you to perform some advanced operation not available with RPC
	
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

	// *******************
	// USAGE OF THE SCRIPT
	// *******************

	/*
	
	To use this extension add after include_once('PATH/easyraiblocks.php'); -> include_once('PATH/easyraiext.php');
	
	Then add --> $rb_ext = $rb; // Put here your variable name used to call RPC, Example: $rb = new RaiBlocks('host','port');
	
	*/
	
	// ************************************************************
	// DO NOT EDIT BELOW, BUT DO IT IF YOU KNOW WHAT YOU ARE DOING!
	// ************************************************************
	
	// Call this function to get all balances of every accounts in a wallet
	// Parameters:
	// $walletID -> the ID of the wallet you want to check
	
	function raiblocks_balance_wallet($walletID){
	
		global $rb_ext;
		$accounts_balances = array( "accounts" => array(), "sum_balance_rai" => 0, "sum_pending_rai" => 0, "n_accounts" => 0 );
		
		$return = $rb_ext->account_list( array( "wallet" => $walletID ) ); // Get all accounts of a wallet
		
		// Fetch every account
				
		//echo "\nCount".count($return["accounts"])."\n";
				
		if (isset($return["accounts"][0])) {
		
			foreach($return["accounts"] as $account){
			
				$return2 = $rb_ext->account_balance( array( "account" => $account ) ); // Get balance of account
				
				$accounts_balances["accounts"][$account] = array( // Build the return array
				
					"balance_rai" => floor( $return2["balance"]/RAIN ),
					"pending_rai" => floor( $return2["pending"]/RAIN )
				
				);
				
				$accounts_balances["sum_balance_rai"] += $accounts_balances["accounts"][$account]["balance_rai"];
				$accounts_balances["sum_pending_rai"] += $accounts_balances["accounts"][$account]["pending_rai"];
				$accounts_balances["n_accounts"]++;
			
			}
		
		}
		
		return $accounts_balances;
	
	}
	
	// Call this function to get summary balances of all wallets declared in easyrainode_config.php
	// Parameters: none
	
	function raiblocks_summary_wallets() {
	
		global $dwallets;
		$wallets_balances = array( "wallets" => array(), "sum_balance_rai" => 0, "sum_pending_rai" => 0 );
		
		foreach ($dwallets as $tag=>$id){
		
			$return = raiblocks_balance_wallet($id);
			$wallets_balances["wallets"][$id] = array( "tag" => $tag, "balance_rai" => $return["sum_balance_rai"], "pending_rai" => $return["sum_pending_rai"] );
			
			$wallets_balances["sum_balance_rai"] += $return["sum_balance_rai"];
			$wallets_balances["sum_pending_rai"] += $return["sum_pending_rai"];
			
		}
		
		return $wallets_balances;
		
	}
	
	// Call this function to clear a wallet sending all funds to an account
	// Parameters:
	// $walletID -> the ID of the wallet you want to clear
	// $destination -> the account that receive all funds
	
	function raiblocks_clear_wallet( $walletID, $destination ){
	
		global $rb_ext;
		$payment_hashes = array( "accounts" => array(), "sum_balance_rai" => 0, "sum_paid_rai" => 0 );
		
		$return = raiblocks_balance_wallet($walletID);
		
		$payment_hashes["sum_balance_rai"] = $return["sum_balance_rai"];
		
		foreach( $return["accounts"] as $account => $balance ){
			
			if( $balance["balance_rai"] > 0 ){
			
				$args = array(
				
					"wallet" => $walletID,
					"source" => $account,
					"destination" => $destination,
					"amount" => $balance["balance_rai"].RAI
				
				);
				
				$return2 = $rb_ext->send( $args );
				
				if( $return2["block"] != "0000000000000000000000000000000000000000000000000000000000000000" ){ // If payment performed correctly
				
					$payment_hashes["accounts"][$account] = array(
					
						"hash" => $return2["block"],
						"amount_rai" => $balance["balance_rai"]
					
					);
					
					$payment_hashes["sum_paid_rai"] += $balance["balance_rai"];
				
				}else{ // If error happened
				
					$payment_hashes["accounts"][$account] = array(
					
						"hash" => "error",
						"amount_rai" => $balance["balance_rai"]
					
					);
				
				}
			
			}
			
		}
		
		return $payment_hashes;
	
	}
	
	// Call this function to send funds from a wallet to an account without sending from a particular account
	// Parameters:
	// $walletID -> the ID of the wallet you want to use as soruce of payment
	// $destination -> the account to send funds
	// $amount -> the funds you want to send (rai)
	
	function raiblocks_send_wallet( $walletID, $destination, $amount ){
	
		global $rb_ext;
		$payment_hashes = array( "accounts" => array(), "status" => "ok", "sum_paid_rai" => 0 ); $selected_accounts = array(); $sum = 0; $diff_amount = $amount;
		
		$return = raiblocks_balance_wallet($walletID);
		
		// Select funds from accounts
		
		foreach($return["accounts"] as $account => $balance){
		
			if( $balance["balance_rai"] > 0 ){
			
				$selected_accounts[$account] = $balance["balance_rai"];
				$sum += $balance["balance_rai"];
			
			}else{
			
				continue;
			
			}
			
			if($sum >= $amount) break; // Amount reached?
		
		}
		
		// Sum not reached?
		
		if( $sum < $amount ){
		
			$payment_hashes["sum_paid_rai"] = 0;
			$payment_hashes["status"] = "not enough funds";
			return $payment_hashes;
		
		}
		
		// Sum reached?
		
		foreach($selected_accounts as $selected_account => $balance){
			
			if( $diff_amount - $balance < 0 ){
			
				$balance = $diff_amount;
			
			}else{
			
				// Nothing.
			
			}
			
			$args = array(
			
				"wallet" => $walletID,
				"source" => $selected_account,
				"destination" => $destination,
				"amount" => $balance.RAI
			
			);
			
			$return2 = $rb_ext->send( $args );
			
			if( $return2["block"] != "0000000000000000000000000000000000000000000000000000000000000000" ){ // If payment performed correctly
			
				$payment_hashes["accounts"][$selected_account] = array(
				
					"hash" => $return2["block"],
					"amount_rai" => $balance
				
				);
				
				$payment_hashes["sum_paid_rai"] += $balance;
				
				$diff_amount -= $balance;
			
			}else{ // If error happened
			
				$payment_hashes["accounts"][$selected_account] = array(
				
					"hash" => "error",
					"amount_rai" => $balance
				
				);
				
				$payment_hashes["status"] = "error";
			
			}
		
		}
		
		return $payment_hashes;
		
	}
	
	// Call this function to change the representative for every account that exist in the wallet and for further (if selected)
	// Parameters:
	// $walletID -> the ID of the wallet that contains your accounts
	// $representative -> the representative you want to set
	// $further -> change the representative of wallet for further accounts (default set true)
	
	function raiblocks_representative_all( $walletID, $representative, $further = true ){
		
		global $rb_ext;
		$rep_change = array( "accounts" => array(), "further" => "no", "status" => "ok", "weight_shifted_rai" => 0 );
		
		if($further){ // If change representative for further accounts
			
			$args = array(
			
				"wallet" => $walletID,
				"representative" => $representative
				
			);
			
			$return = $rb_ext->wallet_representative_set( $args );
			
			if( $return["set"] == "1" ){ // If set correctly
				
				$rep_change["further"] = "yes";
			
			}
		
		}
		
		$return = raiblocks_balance_wallet($walletID);
		
		// Change for each account
		
		foreach($return["accounts"] as $account => $balance){
		
			$args = array(
			
				"wallet" => $walletID,
				"account" => $account,
				"representative" => $representative
			
			);
		
			$return2 = $rb_ext->account_representative_set( $args );
			
			if( $return2["block"] != "0000000000000000000000000000000000000000000000000000000000000000" ){ // If change representative performed correctly
			
				$rep_change["accounts"][$account] = $return2["block"];
				$rep_change["weight_shifted_rai"] += $balance["balance_rai"];
			
			}else{
			
				$rep_change["accounts"][$account] = "error";
				$rep_change["status"] = "error";
				
			}
		
		}
		
		return $rep_change;
		
	}
	
	// Call this function to generate n accounts in a wallet
	// Parameters:
	// $walletID -> the ID of the wallet that generates accounts
	// $n -> the number of accounts you wish to generate
	
	function raiblocks_n_accounts( $walletID, $n ){
	
		global $rb_ext;
		$accounts_created = array( "accounts" => array(), "n" => $n, "n_generated" => 0 );
		
		$i = 0;
		
		while( $i < $n ){
			
			$return = $rb_ext->account_create( array( "wallet" => $walletID ) ); // Create a new account
			
			if( $return["account"] != "" ){ $accounts_created["n_generated"]++; $accounts_created["accounts"][] = $return["account"]; }
			else{  }
			
			$i++;
		
		}
		
		return $accounts_created;
	
	}
	
	// Call this function to generate an account starting with a particular string
	// Parameters:
	// $string -> the string you wish your account starts with
	
	function raiblocks_adhoc_account( $string, $position = "start" ){
	
		global $rb_ext;
		
		$i = 0; $a = 0; $start = time();

		do{
			
			$key_create = $rb_ext->key_create();
			$account = $key_create["account"];
			
			if( $position == "start" ){
			
				if( strpos( $account, 'xrb_'.$string ) === 0 || strpos( $account, 'xrb_1'.$string ) === 0 || strpos( $account, 'xrb_3'.$string ) === 0 ){
					
					$i = 1;
					
				}
			
			}else{
				
				if( substr_compare( $account, $string, -strlen( $string ) ) === 0 ){
					
					$i = 1;
					
				}
			
			}
			
			$a++;
			
		}while( $i < 1 );
		
		$end = time();
		$gap = $end - $start;

		$key_create["count"] = $a;
		$key_create["gap"] = $gap;
			
		return $key_create;
	
	}
	
?>