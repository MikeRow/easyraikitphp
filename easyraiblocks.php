<?php
/*

easyraiblocksphp

===================

INSPIRED BY Andrew LeCody EasyBitcoin-PHP

A simple class for making calls to Bitcoin's API using PHP.
https://github.com/aceat64/EasyBitcoin-PHP

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

// Initialize RaiBlocks connection/object
$raiblocks = new RaiBlocks();

// Optionally, you can specify a host and port.
$raiblocks = new RaiBlocks('host','port');
// Defaults are:
//	host = localhost
//	port = 7076
//	proto = http

// If you wish to make an SSL connection you can set an optional CA certificate or leave blank
// This will set the protocol to HTTPS and some CURL flags
$raiblocks->setSSL('/full/path/to/mycertificate.cert');

// Make calls to node as methods for your object. Responses are returned as an array.
// Example:

$args = array(
	"account" => "xrb_3e3j5tkog48pnny9dmfzj1r16pg8t1e76dz5tmac6iq689wyjfpi00000000"
);

$response = $raiblocks->account_balance( $args );
echo $response['balance'];

// The full response (not usually needed) is stored in $this->response while the raw JSON is stored in $this->raw_response

// When a call fails for any reason, it will return FALSE and put the error message in $this->error
// Example:
echo $raiblocks->error;

// The HTTP status code can be found in $this->status and will either be a valid HTTP status code or will be 0 if cURL was unable to connect.
// Example:
echo $raiblocks->status;

*/

class RaiBlocks {
    // Configuration options
    private $proto;
    private $host;
    private $port;
    private $url;
    private $CACertificate;

    // Information and debugging
    public $status;
    public $error;
    public $raw_response;
    public $response;

    private $id = 0;

    /**
     * @param string $host
     * @param int $port
     * @param string $proto
     * @param string $url
     */
    function __construct( $host = 'localhost', $port = 7076, $url = null ) {
        $this->host          = $host;
        $this->port          = $port;
        $this->url           = $url;
        // Set some defaults
        $this->proto         = 'http';
        $this->CACertificate = null;
    }

    /**
     * @param string|null $certificate
     */
    function setSSL($certificate = null) {
        $this->proto         = 'https'; // force HTTPS
        $this->CACertificate = $certificate;
    }

    function __call($method, $params) {
        $this->status       = null;
        $this->error        = null;
        $this->raw_response = null;
        $this->response     = null;

        // If no parameters are passed, this will be an empty array
        //$params = array_values($params);

        // The ID should be unique for each call
        $this->id++;

        // Build the request, it's ok that params might have any empty array
        $request = array(
            'action' => $method
            //'params' => $params
            //'id'     => $this->id
        );
		
        if( isset($params[0]) ){
        
		foreach($params[0] as $key=>$value){
				
			$request[$key] = $value;
				
		}
		
        }
		
	$request = json_encode($request);

        // Build the cURL session
        $curl    = curl_init("{$this->proto}://{$this->host}:{$this->port}/{$this->url}");
        $options = array(
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_FOLLOWLOCATION => TRUE,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_HTTPHEADER     => array('Content-type: application/json'),
            CURLOPT_POST           => TRUE,
            CURLOPT_POSTFIELDS     => $request
        );

        // This prevents users from getting the following warning when open_basedir is set:
        // Warning: curl_setopt() [function.curl-setopt]: CURLOPT_FOLLOWLOCATION cannot be activated when in safe_mode or an open_basedir is set
        if (ini_get('open_basedir')) {
            unset($options[CURLOPT_FOLLOWLOCATION]);
        }

        if ($this->proto == 'https') {
            // If the CA Certificate was specified we change CURL to look for it
            if ($this->CACertificate != null) {
                $options[CURLOPT_CAINFO] = $this->CACertificate;
                $options[CURLOPT_CAPATH] = DIRNAME($this->CACertificate);
            }
            else {
                // If not we need to assume the SSL cannot be verified so we set this flag to FALSE to allow the connection
                $options[CURLOPT_SSL_VERIFYPEER] = FALSE;
            }
        }

        curl_setopt_array($curl, $options);

        // Execute the request and decode to an array
        $this->raw_response = curl_exec($curl);
        $this->response     = json_decode($this->raw_response, TRUE);

        // If the status is not 200, something is wrong
        $this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        // If there was no error, this will be an empty string
        $curl_error = curl_error($curl);

        curl_close($curl);

        if (!empty($curl_error)) {
            $this->error = $curl_error;
        }

        if ($this->status != 200) {
            // If node didn't return a nice error message, we need to make our own
            switch ($this->status) {
                case 400:
                    $this->error = 'HTTP_BAD_REQUEST';
                    break;
                case 401:
                    $this->error = 'HTTP_UNAUTHORIZED';
                    break;
                case 403:
                    $this->error = 'HTTP_FORBIDDEN';
                    break;
                case 404:
                    $this->error = 'HTTP_NOT_FOUND';
                    break;
            }
        }

        if ($this->error) {
            return FALSE;
        }

        return $this->response;
    }
}
