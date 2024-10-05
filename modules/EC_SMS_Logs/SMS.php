<?php
class SMS {
    public $SENDER;
    private $ENDPOINT;
    private $URL_SEND;
    private $TOKEN;
    private $TOKEN_AD;
    private $URL_CREATE_STATIC;
    private $URL_CREATE_DYNAMIC;

    function __construct() {
        $this->SENDER = "Travelpass";
        $this->ENDPOINT = "api-02.worldsms.vn"; // api-05.worldsms.vn, api-01.worldsms.vn
        $this->URL_SEND = "https://".$this->ENDPOINT."/webapi/sendSMS";
        $this->TOKEN = "c25leHRfdHJhdmVsOk1xYkY1UmpY";
        $this->TOKEN_AD = "c25leHRfdHJhdmVsMnFjOmdwdlZyOWpn";
        $this->URL_CREATE_STATIC = "https://".$this->ENDPOINT."/apisms.php/createCampaignAPI ";
        $this->URL_CREATE_DYNAMIC = "https://".$this->ENDPOINT."/api/campaign/create-dynamic";
    }

    public function header() {
        return array(
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Bearer c25leHRfdHJhdmVsOk1xYkY1UmpY'
        );
    }

    /** 
     * Gửi tin nhắn qua số điện thoại
     * 
     * @param string $phone
     * @param string $text
     * @param string $contentid (optional)
     * @return string
     */
    public function send($phone, $text, $contentid = 0) {
        if(is_null($phone) || empty($phone) || strlen($phone) < 10 || strlen($phone) > 12) return false;
        if(is_null($text) || empty($text)) return false;

        $unicode = global_is_unicode($text);
        $body = '{"from":"'.$this->SENDER.'","to":"'.$phone.'","text":"'.$text.'","unicode":'.$unicode.',"contentid":"'.$contentid.'"}';

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong*    
            if ($curl === false) {
                throw new Exception('failed to initialize');
            }
        
            // Better to explicitly set URL
            curl_setopt_array($curl, [
                CURLOPT_URL             => $this->URL_SEND,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_CUSTOMREQUEST   => 'POST',
                CURLOPT_POSTFIELDS      => $body,
                CURLOPT_HTTPHEADER      => [
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "Authorization: Basic $this->TOKEN"
                ],
            ]);
            $json = curl_exec($curl);
        
            // Check the return value of curl_exec(), too
            if ($json === false) {
                return curl_error($curl) . " " . curl_errno($curl);
            }
        
            // $httpReturnCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
            // Process result here
            $json = trim($json);
            $json = trim(str_replace("\\", "",  $json), '"');  // Fix json string
            return $json;
        }
        catch(Exception $e) {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);
        }
        finally {
            if (is_resource($curl)) {
                curl_close($curl);
            }
        }
    }  

    /** 
     * Gửi chiến dịch tin nhắn QC không tham số
     * 
     * @param string $campaign_name
     * @param string $client_request_id
     * @param string $sent_date : Time to excute (d-m-Y H:i:s)
     * @param string $message
     * @param array  $list_phone : List of phone numbers
     * @return string
     */
    public function send_list_static($campaign_name, $client_request_id, $sent_date, $message, $list_phone) {
        if(is_null($campaign_name) || empty($campaign_name)) return false;
        if(is_null($client_request_id) || empty($client_request_id)) return false;
        if(is_null($sent_date) || empty($sent_date)) return false;
        if(is_null($message) || empty($message)) return false;
        if(is_null($list_phone) || empty($list_phone)) return false;

        // Format message
        $message = trim($message);
        $last_character = substr($message, -1);
        if($last_character != '.') $message .= '.';

        // Format sent date
        $sent_date = date('d-m-Y H:i:00', strtotime($sent_date));

        $body = [
            "CampaignName"      => $campaign_name,
            "ClientRequestId"   => $client_request_id,
            "SentDate"          => $sent_date,
            "Sender"            => $this->SENDER,
            "Unicode"           => global_is_unicode($message),
            "Msg"               => $message,
            "ListPhone"         => $list_phone
        ];

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong*    
            if ($curl === false) {
                throw new Exception('failed to initialize');
            }
        
            // Better to explicitly set URL
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $this->URL_CREATE_STATIC,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_CUSTOMREQUEST   => 'POST',
                CURLOPT_POSTFIELDS      => json_encode($body),
                CURLOPT_HTTPHEADER      => [
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "Authorization: Basic $this->TOKEN_AD"
                ],
            ));
            
            $json = curl_exec($curl);
        
            // Check the return value of curl_exec(), too
            if ($json === false) {
                return curl_error($curl) . " " . curl_errno($curl);
            }
        
            // Check HTTP return code, too; might be something else than 200
            $httpReturnCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            // Process result here
            $json = trim($json);
            $json = trim(str_replace("\\", "",  $json), '"');  // Fix json string
            $json = rtrim($json, "n");
            return $json;
        }
        catch(Exception $e) {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);
        }
        finally {
            if (is_resource($curl)) {
                curl_close($curl);
            }
        }
    }

    /** 
     * Gửi chiến dịch tin nhắn QC có tham số
     * 
     * @param string $campaign_name
     * @param string $client_request_id
     * @param string $sent_date : Time to excute (d-m-Y H:i:s)
     * @param string $message
     * @param array  $list_data : List of data to send
     * @return string
     */
    public function send_list_dynamic($campaign_name, $client_request_id, $sent_date, $message, $list_data) {
        if(is_null($campaign_name) || empty($campaign_name)) return false;
        if(is_null($client_request_id) || empty($client_request_id)) return false;
        if(is_null($sent_date) || empty($sent_date)) return false;
        if(is_null($message) || empty($message)) return false;
        if(is_null($list_data) || empty($list_data)) return false;

        // Format message
        $message = trim($message);
        $last_character = substr($message, -1);
        if($last_character != '.') $message .= '.';

        // Format sent date
        $sent_date = date('d-m-Y H:i:00', strtotime($sent_date));

        $body = [
            "CampaignName"      => $campaign_name,
            "ClientRequestId"   => $client_request_id,
            "SentDate"          => $sent_date,
            "Sender"            => $this->SENDER,
            "Unicode"           => global_is_unicode($message),
            "Msg"               => $message,
            "ListData"          => $list_data
        ];

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong*    
            if ($curl === false) {
                throw new Exception('failed to initialize');
            }
        
            // Better to explicitly set URL
            curl_setopt_array($curl, array(
                CURLOPT_URL             => $this->URL_CREATE_DYNAMIC,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_CUSTOMREQUEST   => 'POST',
                CURLOPT_POSTFIELDS      => json_encode($body),
                CURLOPT_HTTPHEADER      => [
                    "Content-Type: application/json",
                    "Accept: application/json",
                    "Authorization: Basic $this->TOKEN_AD"
                ],
            ));
            
            $json = curl_exec($curl);
        
            // Check the return value of curl_exec(), too
            if ($json === false) {
                return curl_error($curl) . " " . curl_errno($curl);
            }
        
            // Check HTTP return code, too; might be something else than 200
            $httpReturnCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            // Process result here
            $json = trim($json);
            $json = trim(str_replace("\\", "",  $json), '"');  // Fix json string
            $json = rtrim($json, "n");
            return $json;
        }
        catch(Exception $e) {
            trigger_error(sprintf(
                'Curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);
        }
        finally {
            if (is_resource($curl)) {
                curl_close($curl);
            }
        }
    }
}