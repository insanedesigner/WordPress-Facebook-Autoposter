<?php 
// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

require_once WPW_AUTO_POSTER_SOCIAL_DIR . "/facebook-android/autoload.php";

class Wpw_Auto_Poster_REST_API
{
	private $host;
	private $apiVersion;
	private $params;
	private $endPoint;
	private $node;
	private $method;
	private $accessToken;
	private $timeOut;
	public $error;
	private $proxy = FALSE;
	private $pupw = FALSE;

	// Setters and getters Auto Generate  
    public function __call($function, $args)
    {
        $functionType = strtolower(substr($function, 0, 3));
        $propName = lcfirst(substr($function, 3));
        switch ($functionType) {
            case 'get':
                if (property_exists($this, $propName)) {
                    return $this->$propName;
                }
                break;
            case 'set':
                if (property_exists($this, $propName)) {
                    $this->$propName = $args[0];
                }
                break;
        }
    }
    
	public function __construct() {
		$this->initParams();
	}

	public function request(array $params = null,$method = "GET",$noAT = TRUE){

		set_time_limit(60000);
		
		if($noAT && trim($this->accessToken) == ""){
			$this->error = "Access token is missing!";
			return false;
		}

		$url = $this->host;
		$url .= $this->apiVersion.'/';
		$url .=	$this->node != null ? $this->node.'/' : '';
		$url .=	$this->endPoint != null ? $this->endPoint : '';

		$url .= '?';

		if($params != null){
			foreach ($params as $key => $value) {
				$url .= $key . '=' . $value . '&';
			}
		}

		$url .=	$this->accessToken != null ? 'access_token='.$this->accessToken : '';
		$url .=	$this->method != null ? '&method='.$this->method : '';

		$headers = array(
		  "Content-Type"	=> "application/x-www-form-urlencoded",
		  "User-Agent"		=> "fb-php-5.4.4",
		  "Accept-Encoding"	=> "*"
		);

		$body = NULL;

		if($this->endPoint == "photos" && isset($params['source']) && !isset($params['attached_media'])){
			$method = "POST";
			$boundary = hash('sha256', uniqid('', true));
			$headers = array(
			  "Content-Type"=> "multipart/form-data;boundary=---------------------------".$boundary,
			  "User-Agent"=> "fb-php-5.4.4",
			  "Accept-Encoding"=> "*"
			);

			$file_url = $params['source'];
			$pi = pathinfo($params['source']);

			$CI =& get_instance();
			$CI->load->helper("curl_helper");
			$file = curl_helper($file_url);

			if($file == FALSE){
				$this->error = "Failed to load image: image URL not found or invalid";
				return FALSE;
			}

			$eol = "\r\n";   
			$body="";
			$body.= '-----------------------------'.$boundary. $eol; 
			$body.= 'Content-Disposition: form-data; name="source"; filename="'.$pi['basename'].'"' . $eol; 
			$body.= 'Content-Type: application/octet-stream'. $eol;
			$body.= 'Content-Transfer-Encoding: multipart/form-data' . $eol . $eol; 
			$body.= $file. $eol; 
			$body.= '-----------------------------'.$boundary .'--' . $eol. $eol;

			$url = $this->host;
			$url .= $this->apiVersion.'/';
			$url .=	$this->node != null ? $this->node.'/' : '';
			$url .=	$this->endPoint != null ? $this->endPoint : '';
			$url .= '?';
			if($params != null){
				foreach ($params as $key => $value) {
					$url .= $key . '=' . $value . '&';
				}
			}
			$url .=	$this->accessToken != null ? 'access_token='.$this->accessToken : '';
		}

		$this->initParams();
		
		$r = new Facebook\HttpClients\CustomFacebookCurlHttpClient();

		$r->setProxy($this->proxy);
		$r->setPupw($this->pupw);
		//echo $url;
		try{
			$res = $r->send($url, $method, $body, $headers, $this->timeOut);
		}catch(Facebook\Exceptions\FacebookSDKException $e){
			$this->error = $e->getMessage();
			return FALSE;
		}catch(Facebook\Exceptions\FacebookResponseException $e) {
			$this->error = $e->getMessage();
			return FALSE;
		}
		//var_dump($res);

		return $res;
	}

	public function initParams()
	{
		$this->host  = "https://graph.facebook.com/";
		$this->apiVersion = "v".WPW_AUTO_POSTER_FB_API_VERSION;
		$this->params = null;
		$this->endPoint = null;
		$this->node = null;
		$this->method = null;
		$this->accessToken = null;
		$this->timeOut = 60;
	}

}
