<?php

	/*

	easyraialonephp

	Allows you to perform some operations without relying on a node
	
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
	
	To use this extension just do include_once('PATH/easyraialone.php');
	
	*/
	
	// ************************************************************
	// DO NOT EDIT BELOW, BUT DO IT IF YOU KNOW WHAT YOU ARE DOING!
	// ************************************************************
	
	// Call this function to validate RaiBlocks account
	// Requirements: PHP BLAKE2 Extension installed and enabled
	// https://github.com/strawbrary/php-blake2
	// Parameters:
	// $account -> the string representation of xrb_account
	//
	// Clean possible user errors with
	//	$account = str_replace(' ', '', strtolower(trim($account)));
	//	$account = preg_replace('/[^a-z0-9\_]/', '', $account);
	//
	
	function to_uint5($n) {

		$letter_list = str_split("13456789abcdefghijkmnopqrstuwxyz");
		return(array_search($n, $letter_list));

	}
	
	function raiblocks_account_validate($account) {
	
		if (is_string($account)) {
			
			if (((strpos($account, 'xrb_1') === 0) || (strpos($account, 'xrb_3') === 0)) && (strlen($account) == 64)) {
	
				$account = substr("$account", 4);
				$char_validation = preg_match ("/^[13456789abcdefghijkmnopqrstuwxyz]+$/", $account);
				
				if ($char_validation === 1) {

					$account_array = str_split($account);
					$uint5 = array_map("to_uint5", $account_array);
					
					$uint8[0] = (($uint5[0] << 7) + ($uint5[1] << 2) + ($uint5[2] >> 3)) % 256;
					$uint8[1] = (($uint5[2] << 5) + $uint5[3]) % 256;

					for($i = 0; $i < 7; ++$i) {
	
						$uint8[5*$i+2] = ($uint5[8*$i+4] << 3) + ($uint5[8*$i+5] >> 2);
						$uint8[5*$i+3] = (($uint5[8*$i+5] << 6) + ($uint5[8*$i+6] << 1) + ($uint5[8*$i+7] >> 4)) % 256;
						$uint8[5*$i+4] = (($uint5[8*$i+7] << 4) + ($uint5[8*$i+8] >> 1)) % 256;
						$uint8[5*$i+5] = (($uint5[8*$i+8] << 7) + ($uint5[8*$i+9] << 2) + ($uint5[8*$i+10] >> 3)) % 256;
						$uint8[5*$i+6] = (($uint5[8*$i+10] << 5) + $uint5[8*$i+11]) % 256;

					}
					
					$key = array_slice($uint8, 0, 32);
					$key_string = implode(array_map("chr", $key));
					$hash = bin2hex(implode(array_map("chr", array_reverse(array_slice($uint8, 32, 5)))));
					$check = blake2($key_string,5);

					if ($hash===$check) { return true; }
					else { return false; }
					
				} else { return false; }

			} else { return false; }

		} else { return false; }

	}

?>