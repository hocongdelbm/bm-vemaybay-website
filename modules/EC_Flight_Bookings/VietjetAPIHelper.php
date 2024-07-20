<?php
class VietjetAPIHelper {
    private $ENDPOINT;
    private $API_KEY_LIST;
    private $CONNECTTIMEOUT;
    private $TIMEOUT;

    public function __construct($supplier_id) {
        if($supplier_id == '3e414dde-85b6-315b-e0ba-6556c458368f') { // Minh Hồng Võ
            // $this->ENDPOINT = "https://apivj3.timchuyenbay.net/api/v2";
            $this->ENDPOINT = "https://apivj4.timchuyenbay.net/api/v2";
        }
        elseif($supplier_id == '7df1cbf9-21b6-4f45-7cc5-62011601a951') { // Travelpass
            // $this->ENDPOINT = "https://apivj.timchuyenbay.net/api/v1";
            $this->ENDPOINT = "https://apivj2.timchuyenbay.net/api/v2";
        }
        else $this->ENDPOINT = $supplier_id;

        $this->API_KEY_LIST = [
            'auth'              => 'mK_F6_flYV6Y1GmCNQ+Pm18c9n66xQrzDPj_8RwUAjG3P8zF6O',
            'flight'            => 'QoY2+9L7TN8b6_Auq5g2Gx+8o7cwy70LO4irw$V1XgsS5irJS0',
            'info'              => 'VCVTo505Lni1DW51gzehT0+QC5iw1fQgYfXl9TFptORE+x11wR',
            'booking_options'   => '47oy72WmePcI2lcLxZFQIw$a6cUgnOLE2075S2iv0UCJeXC2Gj',
            'booking'           => '96pN9u4yn70+f_3dUTfJXZ$F1isZMgiMuek_rV1+BzlXsQBH1j',
            'bm'                => '1G4$vaEYghZv$I9JIj40U6D$oBqTEVHl6hBhqU$9EAfV6RB+Rq'
        ];
        $this->CONNECTTIMEOUT = 100;
        $this->TIMEOUT = 300;
    }

    // GET METHOD
    public function getAPIKey($name) {
        return isset($this->API_KEY_LIST[$name]) ? $this->API_KEY_LIST[$name] : '';
    }

    public function getLastName($str) {
        if (empty($str)) return $str;
        return explode(' ', $str)[0];
    }

    public function getFirstName($str) {
        if (empty($str)) return $str;
        
        $arr = explode(' ', $str);
        $n = count($arr);
        $result = '';
        for ($i = 1 ; $i < $n; $i++) { 
            $result .= $arr[$i] . ' ';
        }
    
        return trim($result);
    }
    
    public function getTitle($gender, $type) {
        if(empty($gender)) return "";

        $title = [
            'Male' => [
                0 => 'Mr',
                1 => 'Master',
                2 => 'Infant'
            ],
            'Female' => [
                0 => 'Ms',
                1 => 'Miss',
                2 => 'Infant'
            ],
        ];

        return $title[$gender][$type];
    }

    /** Lấy thông tin đặt chỗ bằng PNR
     * 
     * @param string $pnr
     * @param int $format 
     * @return string json
     */
    public function getBookingByPNR($pnr, $format = 1) {
        if(empty($pnr)) return json_encode(['error' => 1, 'code' => 400, 'message' => 'Invalid PNR', 'data' => null]);

        $url = "$this->ENDPOINT/getBookingByLocator";
        $post_data = array(
            'api_key' => $this->getAPIKey('booking'),
            'reservation_locator' => $pnr,
            'format' => $format
        );

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_errno($curl) . ': ' .curl_error($curl), 'data' => $url]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }


    /** Lấy thông tin đặt chỗ bằng khóa đặt chỗ
     * 
     * @param string $reservation_key
     * @param int $format
     * @return string json
     */
    public function getBookingByKey($reservation_key, $format = 0) {
        if(empty($reservation_key)) return json_encode(['error' => 1, 'code' => 400, 'message' => 'Invalid reservation key', 'data' => null]);

        $url = "$this->ENDPOINT/getBookingByKey";
        $post_data = array(
            'api_key'           => $this->getAPIKey('booking'),
            'reservation_key'   => $reservation_key,
            'format'            => $format
        );

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_errno($curl) . ': ' .curl_error($curl), 'data' => $url]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** Lấy khóa đặt chỗ bằng PNR
     * 
     * @param string $pnr
     * @return string
     */
    public function getKeyByPNR($pnr) {
        if(empty($pnr)) return json_encode(['error' => 1, 'code' => 400, 'message' => 'Invalid PNR', 'data' => null]);

        $url = "$this->ENDPOINT/getBookingByLocator";
        $post_data = array(
            'api_key' => $this->getAPIKey('booking'),
            'reservation_locator' => $pnr,
            'format' => 1
        );

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_error($curl), 'data' => null]);
            }

            $arr = json_decode($json, true);
            if(isset($arr['error']) && $arr['error'] == 0 && !empty($arr['data'])) return $arr['data']['key'];
            return '';
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** Lấy thông tin hành trình
     * 
     * @param string $reservation_key
     * @param string $journey_key
     * @return string json
     */
    public function getJourney($reservation_key, $journey_key) {
        if (empty($reservation_key) || empty($journey_key)) return json_encode(['error' => 1, 'code' => 400, 'message' => 'Invalid params', 'data' => null]);

        $url = "$this->ENDPOINT/getJourney";
        $post_data = array(
            'api_key'           => $this->getAPIKey('booking'),
            'reservation_key'   => $reservation_key,
            'journey_key'       => $journey_key
        );

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_error($curl), 'data' => null]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** Lấy thông tin hành trình travelOptions - Thay đổi hành trình
     * 
     * @param string $reservation_key
     * @param string $journey_key
     * @return string json
     */
    public function get_flight_update_journey($reservation_key, $journey_key, $depart, $arrival, $date, $class) {
        if (empty($reservation_key) || empty($journey_key) || empty($depart) || empty($arrival) || empty($date)) return json_encode(['error' => 1, 'code' => 400, 'message' => 'Invalid params', 'data' => null]);

        $url = "$this->ENDPOINT/searchFlightChangeJourney";
        $post_data = array(
            'api_key'           => $this->getAPIKey('flight'),
            're_key'            => $reservation_key,
            'journey_key'       => $journey_key,
            'depCode'           => $depart,
            'arvCode'           => $arrival,
            'departureDate'     => $date,
            'class'             => $class,
        );

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_errno($curl) . ': ' .curl_error($curl), 'data' => $url]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** Lấy thông tin giá, thuế, phí, hành lý
     * 
     * @param string $reservation_key
     * @return string json
     */
    public function getCharges($reservation_key) {
        if(empty($reservation_key)) return json_encode(['error' => 1, 'code' => 400, 'message' => 'Invalid reservation key', 'data' => null]);

        $url = "$this->ENDPOINT/getCharges";
        $post_data = array(
            'api_key' => $this->getAPIKey('booking'),
            'reservation_key' => $reservation_key
        );

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_error($curl), 'data' => null]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** Lấy thông tin hành lý
     * 
     * @param string $booking_key
     * @return string json
     */
    public function getBaggage($booking_key) {
        if (empty($booking_key)) return json_encode(['error' => 1, 'code' => 400, 'message' => 'Invalid booking key', 'data' => null]);
        
        $url = "$this->ENDPOINT/getAncillary";
        $post_data = array(
            'api_key'    => $this->getAPIKey('booking_options'),
            'booking_key' => $booking_key
        );

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_error($curl), 'data' => null]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** Lấy danh sách hành khách theo khóa đặt chỗ
     * 
     * @param string $reservation_key
     * @return string json
     */
    public function getPassengers($reservation_key) {
        if (empty($reservation_key)) return json_encode(['error' => 1, 'code' => 400, 'message' => 'Invalid reservation key', 'data' => null]);

        $url = "$this->ENDPOINT/getPassengers";
        $post_data = array(
            'api_key'           => $this->getAPIKey('booking'),
            'reservation_key'   => $reservation_key
        );

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_error($curl), 'data' => null]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** Lấy thông tin chi tiết một hành khách
     * 
     * @param string $reservation_key
     * @param string $passenger_key
     * @return string json
     */
    public function getPassengerByKey($reservation_key, $passenger_key) {
        if (empty($reservation_key) || empty($passenger_key)) return json_encode(['error' => 1, 'code' => 400, 'message' => 'Invalid params', 'data' => null]);

        $url = "$this->ENDPOINT/getPassengerByKey";
        $post_data = array(
            'api_key'           => $this->getAPIKey('booking'),
            'reservation_key'   => $reservation_key,
            'passenger_key'     => $passenger_key
        );

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_error($curl), 'data' => null]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** Lấy thông tin phương thức thanh toán hiện có
     * 
     * @param string $booking_key
     * @return string json
     */
    public function getPaymentMethod($booking_key) {
        if(is_null($booking_key) || empty($booking_key)) return json_encode(['error' => 1, 'code' => 400, 'message' => 'Invalid booking key', 'data' => null]);
        
        $url = "$this->ENDPOINT/getPaymentMethod";
        $post_data = array(
            'api_key'    => $this->getAPIKey('booking_options'),
            'bookingKey' => $booking_key
        );

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_error($curl), 'data' => null]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** Đặt chỗ từ BM
     * 
     * @param array $body_request
     * @return string json
     */
    public function booking($body_request) {
        if (empty($body_request)) return json_encode(['error' => 1, 'code' => 400, 'message' => 'Invalid body request', 'data' => null]);
    
        $url = "$this->ENDPOINT/bookingBM";
        
        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body_request);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_error($curl), 'data' => null]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** Thêm hành lý
     * 
     * @param array $parameters
     * @return string json
     */
    public function addBaggage($parameters) {
        if(empty($parameters)) return json_encode(['error' => 1, 'code' => 400, 'message' => 'Invalid params', 'data' => null]);

        $url = "$this->ENDPOINT/addAncillary";
        $post_data = [
            'api_key'      => $this->getAPIKey('booking_options'),
            'parameters'   => json_encode($parameters)
        ];

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_error($curl), 'data' => null]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** Thanh toán
     * 
     * @param string $reservation_key
     * @param int $totalAmount
     * @param int $totalAmountProcessing
     * @return string json
     */
    public function payBooking($reservation_key, $totalAmount, $totalAmountProcessing = 0) {
        if(empty($reservation_key)) return json_encode(['error' => 1, 'code' => 400, 'message' => 'Invalid booking key', 'data' => null]);

        $url = "$this->ENDPOINT/payBooking";
        $post_data = [
            'api_key' => $this->getAPIKey('booking'),
            'reservation_key' => $reservation_key,
            'totalAmount' => $totalAmount,
            'totalAmountProcessing' => $totalAmountProcessing
        ];

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_error($curl), 'data' => null]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** Lấy thông tin thanh toán
     * 
     * @param string $reservation_key
     * @return string json
     */
    public function getPaymentByKey($reservation_key) {
        if (empty($reservation_key)) return null;

        $url = "$this->ENDPOINT/getPaymentByKey";
        $post_data = array(
            'api_key'           => $this->getAPIKey('booking'),
            'reservation_key'   => $reservation_key
        );

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_error($curl), 'data' => null]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    /** Lấy thông tin đại lý
     * 
     * @return string json
     */
    public function getAgency(){
        $url = "$this->ENDPOINT/getAgency";
        $post_data = [
            'api_key' => $this->getAPIKey('info'),
        ];

        try {
            $curl = curl_init();

            // Check if initialization had gone wrong
            if ($curl === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => 'cURL Failed to initialize', 'data' => null]);
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
            curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
            $json = curl_exec($curl);

            // Check the return value of curl_exec(), too
            if ($json === false) {
                return json_encode(['error' => 1, 'code' => 500, 'message' => curl_error($curl), 'data' => null]);
            }

            return $json;
        }
        catch(Exception $e) {
            return json_encode(['error' => 1, 'code' => 500, 'message' => $e->getCode() . ': ' . $e->getMessage(), 'data' => null]);
        }
        finally {
            if (is_resource($curl)) curl_close($curl);
        }
    }

    // Lấy báo giá cập nhật thông tin hành khách
    public function quoteUpdatePassenger($parameters) {
        if(empty($parameters)) return null;

        $url = "$this->ENDPOINT/getQuotationUpdatePassenger";
        $post_data = array(
            'api_key'      => $this->getAPIKey('booking_options'),
            'parameters'   => json_encode($parameters)
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
        $json = curl_exec($curl);
        curl_close($curl);
        $results = json_decode($json, true);
        return $results;
    }

    // Cập nhật thông tin hành khách
    public function updatePassenger($reservation_key, $passenger_key, $body_request) {
        if(empty($reservation_key) || empty($passenger_key) || empty($body_request)) return null;

        $url = "$this->ENDPOINT/updatePassenger";
        $post_data = array(
            'api_key'           => $this->getAPIKey('booking_options'),
            'reservation_key'   => $reservation_key,
            'passenger_key'     => $passenger_key,
            'body_request'      => $body_request
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->TIMEOUT);
        $json = curl_exec($curl);
        curl_close($curl);
        $results = json_decode($json, true);
        return $results;
    }

}

?>