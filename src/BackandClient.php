<?php
/**
 * BackandClient.php
 *
 *
 * @package  App
 * @author   Isaque Alves <isaquealves@gmail.com>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link    http://isaquealves.eti.br
 *
*/
namespace App;


use GuzzleHttp\Client;

/**
 * App\BackandClient
 * 
 * Class built to work with backand REST API.
 *
 *
*/
class BackandClient {

    const BACKAND_API_URL   = 'https://api.backand.com:8080';
    const BACKAND_TOKEN_URL = self::BACKAND_API_URL . '/token';
    const BACKAND_REST_URL  = 'https://api.backand.com:8078';
    const BACKAND_TOKENREQUEST_GRANT_TYPE = 'password';

    private $client;
    private $clientOptions;
    private $username;
    private $password;
    private $backandAppName;

    private $accessToken;
    private $tokenType;

    private $anonymousToken;

    public function __construct($options=null)
    {
        $this->username = getenv('BACKAND_USERNAME');
        $this->password = getenv('BACKAND_PASSWORD');
        $this->backandAppName = getenv('BACKAND_APPNAME');
        $this->anonymousToken = getenv('BACKAND_ANONYMOUS_TOKEN');

        if(!$this->username || !$this->password || !$this->backandAppName || !$this->anonymousToken) {
            throw new \Exception("You must properly set the env vars BACKAND_ANONYMOUS_TOKEN, BACKAND_APPNAME, BACKAND_USERNAME and BACKAND_PASSWORD");
        }

        $this->config = ['username'=>$this->username, 'password' => $this->password, 'appname' => $this->backandAppName, 'grant_type' => self::BACKAND_TOKENREQUEST_GRANT_TYPE];

        $this->createClient($options);
    }

    /**
     * @param $options array
     */
    private function createClient ($options=null)
    {
        $client = new Client($options);
        $this->client = $client;

    }
    
    /**
    *
    * @return $tokenData array The token data array with keys access_token and token_type
    */
    public function getToken ()
    {
        try {
            $request = $this
                ->client
                ->createRequest(
                    'POST',
                    self::BACKAND_TOKEN_URL,
                    [
                        'body' => $this->config
                    ]
                );

                $response = $this->client->send($request);
                $tokenData = $response->json();

                $this->accessToken = $tokenData['access_token'];
                $this->tokenType = $tokenData['token_type'];

                return $tokenData;

        } catch(\Exception $e) {

            return $e->getResponse()->json();
        }

    }
    
    /**
     * Build the request headers as needed
     * @param  boolean $anonymous 
     * @return array   the request headers as an array
     */
    private function buildHeaders($anonymous)
    {
        $requestHeaders = [
            'AnonymousToken' => $this->anonymousToken
        ];

        if(!$anonymous) {
            $requestHeaders = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => ''. $this->tokenType . ' ' . $this->accessToken,
                'AppName' => $this->backandAppName
            ];
        }

        return $requestHeaders;
    }

    /**
     * Through a POST request, create an object of given type with the given data
     * @param  string $objectType The object name in backand
     * @param  array $postData   The POST data
     * @return JSON             Returns a json object
     */
    public function createObject ($objectType=null, $postData=null)
    {
        $this->getToken();

        if(is_null($objectType) || is_null($postData) ) {
            throw new \Exception('ObjectType and postData are required to create an object.');
        }
        if(is_array($postData))
        {
            $postData = json_encode($postData);
        }

        try {
            $request = $this
                ->client
                ->createRequest(
                    'POST',
                    self::BACKAND_REST_URL . '/1/objects/' . $objectType . '?returnObject=true',
                    [
                            'headers' => [
                                'Content-Type' => 'application/json',
                                'Accept' => 'application/json',
                                'Authorization' => ''. $this->tokenType . ' ' . $this->accessToken,
                                'AppName' => $this->backandAppName
                            ],
                            'body' => $postData
                    ]
                );

            $response = $this->client->send($request);

            return $response->json();

        }catch(\Exception $e) {
            return $e->getResponse()->json();
        }

    }

    /**
     * Get the list of objects of the given type
     * @param  string  $objectType The object name in backand
     * @param  integer $pageSize   the page size
     * @param  integer $pageNumber the page number
     * @param  boolean $anonymous  boolean
     * @return JSON
     */
    public function getObjectList ($objectType, $pageSize=20, $pageNumber=1, $anonymous=true)
    {
        $requestHeaders = $this->buildHeaders($anonymous);
        try {
            $request = $this
                    ->client
                    ->createRequest(
                        'GET',
                        self::BACKAND_REST_URL . '/1/objects/' . $objectType . '?pageSize=' . $pageSize . '&pageNumber=' . $pageNumber,
                        [
                                'headers' => $requestHeaders
                        ]
                    );
                return $this->client->send($request)->json();
        }catch(\Exception $e) {
            return $e->getResponse()->json();
        }
    }

    /**
     * Gets a single object from backand
     * @param  string  $objectType  The object name in backand
     * @param  integer  $objectId   The object id in backand
     * @param  boolean $anonymous  
     * @return JSON
     */
    public function getSingleObject ($objectType, $objectId, $anonymous=true)
    {
        $requestHeaders = $this->buildHeaders($anonymous);

        $objectUrl = self::BACKAND_REST_URL . '/1/objects/' . $objectType . '/' . $objectId;

        try{
            $request = $this
                ->client
                ->createRequest(
                    'GET',
                    $objectUrl,
                    [
                            'headers' => $requestHeaders
                    ]
                );
            return $this->client->send($request)->json();
        }catch(\Exception $e){
            return $e->getResponse()->json();
        } 
    }


}
