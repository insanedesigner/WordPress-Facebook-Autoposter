<?php
namespace Facebook\HttpClients;
use Facebook\Http\GraphRawResponse;
use Facebook\Exceptions\FacebookSDKException;
class CustomFacebookCurlHttpClient extends FacebookCurlHttpClient
{
    public $proxy = FALSE;
    public $pupw = FALSE;

    public function setProxy($proxy){
        $this->proxy = $proxy;
    }

    public function setPupw($pupw){
        $this->pupw = $pupw;
    }

    public function openConnection($url, $method, $body, array $headers, $timeOut){
        $options = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->compileRequestHeaders($headers),
            CURLOPT_URL => $url,
            CURLOPT_CONNECTTIMEOUT => $timeOut,
            CURLOPT_TIMEOUT => $timeOut,
            CURLOPT_RETURNTRANSFER => true, // Follow 301 redirects
            CURLOPT_HEADER => true, // Enable header processing
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_CAINFO => __DIR__ . '/certs/DigiCertHighAssuranceEVRootCA.pem',
        ];

        if($this->proxy !== FALSE){
            $options[CURLOPT_PROXYTYPE] = PROXYTYPE;
            $options[CURLOPT_PROXY] = $this->proxy;
            $options[CURLOPT_SSL_VERIFYHOST] = 0;
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            if($this->pupw !== FALSE){
                $options[CURLOPT_PROXYUSERPWD] = $this->pupw;
            }
        }

        if ($method !== "GET") {
            $options[CURLOPT_POSTFIELDS] = $body;
        }

        $this->facebookCurl->init();
        $this->facebookCurl->setoptArray($options);
    }
}
