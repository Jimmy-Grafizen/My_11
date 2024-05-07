<?php
class RfPayout
{
    protected $token;
    protected $baseUrl;
   
    public function __construct($authParams) {
        if(!empty($authParams))
        {
            $clientId = $authParams["clientId"];
            $clientSecret = $authParams["clientSecret"];
            $this->baseUrl = "https://api.razorpay.com/v1";           
            $this->token = $clientId.":".$clientSecret;
         }
    }


    public function addContact($contact_detail) {
      $response1 =["code" => "FAILED", "description" => "Authorization failed"];
      $response['error']=$response1;
      if (!empty($this->token)) {
        $endpoint = $this->baseUrl."/contacts";
        $authToken = $this->token;        
        $curlResponse = $this->postCurl($endpoint, $authToken, $contact_detail);
        return $curlResponse;
      }
      return $response;
    }


     public function getContctById($contact_id) {
      $response1 =["code" => "FAILED", "description" => "Authorization failed"];
      $response['error']=$response1;
      if (!empty($this->token)) {
        $endpoint = $this->baseUrl."/contacts/".$contact_id;
        $authToken = $this->token;        
        $curlResponse = $this->getCurl($endpoint, $authToken);
        return $curlResponse;
      }
      return $response;
    }


    public function addFundAccount($account_detail) {
      $response1 =["code" => "FAILED", "description" => "Authorization failed"];
      $response['error']=$response1;
      if (!empty($this->token)) {
        $endpoint = $this->baseUrl."/fund_accounts";
        $authToken = $this->token;        
        $curlResponse = $this->postCurl($endpoint, $authToken, $account_detail);
        return $curlResponse;
      }
      return $response;
    }


    public function getFundAccountById($fund_account_id) {
       $response1 =["code" => "FAILED", "description" => "Authorization failed"];
      $response['error']=$response1;
      if (!empty($this->token)) {
        $endpoint = $this->baseUrl."/fund_accounts/".$fund_account_id;
        $authToken = $this->token;        
        $curlResponse = $this->getCurl($endpoint, $authToken);
        return $curlResponse;
      }
      return $response;
    }


     public function CreatePayout($payout_detail) {
      $response1 =["code" => "FAILED", "description" => "Authorization failed"];
      $response['error']=$response1;
      if (!empty($this->token)) {
        $endpoint = $this->baseUrl."/payouts";
        $authToken = $this->token;        
        $curlResponse = $this->postCurl($endpoint, $authToken, $payout_detail);
        return $curlResponse;
      }
      return $response;
    }

    public function getPayoutById($payout_id) {
       $response1 =["code" => "FAILED", "description" => "Authorization failed"];
      $response['error']=$response1;
      if (!empty($this->token)) {
        $endpoint = $this->baseUrl."/payouts/".$payout_id;
        $authToken = $this->token;        
        $curlResponse = $this->getCurl($endpoint, $authToken);
        return $curlResponse;
      }
      return $response;
    }
    protected function postCurl ($endpoint, $authToken, $params = []) {
      $postFields = json_encode($params);
      $headers=array();
      array_push($headers,
         'Content-Type: application/json',
         'Content-Length: ' . strlen($postFields));


      $endpoint = $endpoint."?";
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $endpoint);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
      curl_setopt($ch, CURLOPT_USERPWD, $authToken);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $returnData = curl_exec($ch);
      curl_close($ch);
      if ($returnData != "") {
        return json_decode($returnData, true);
      }
      return NULL;
    }

    protected function getCurl ($endpoint,$authToken) {
      $headers=array();
       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERPWD, $authToken);
       curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       $returnData = curl_exec($ch);
       curl_close($ch);
       if ($returnData != "") {
        return json_decode($returnData, true);
       }
       return NULL;
    }



    /*function __destruct()
    {
        $this->token = NULL;
    }*/
}
?>
