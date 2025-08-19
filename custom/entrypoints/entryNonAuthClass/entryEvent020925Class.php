<?php
require_once 'custom/entrypoints/entryNonAuthClass/entryClass.php';

/**
 * Class entryEvent020925Class
 * 
 * Sự kiện Quốc Khánh rộn ràng - Săn quà siêu sang 02/09/25
 */
class entryEvent020925Class extends entryClass {
    private $directoryData;
    private $userStorage;
    private $phoneStorage;
    private $emailStorage;

    public function __construct() {
        $this->directoryData = "custom/json_files/event_02_09_2025";
        $this->phoneStorage = "$this->directoryData/list_phone.json";
        $this->emailStorage = "$this->directoryData/list_email.json";
        $this->userStorage = "$this->directoryData/users";
    }

    /**
     * Get round number from round ID
     * 
     * @return array
     */
    public function getListPhone() {
        if (file_exists($this->phoneStorage)) {
            return json_decode(file_get_contents($this->phoneStorage), true);
        }
        return [];
    }

    /**
     * Get round number from round ID
     * 
     * @return array
     */
    public function getListEmail() {
        if (file_exists($this->emailStorage)) {
            return json_decode(file_get_contents($this->emailStorage), true);
        }
        return [];
    }

    /**
     * Get user info
     * 
     * @param array $params
     * @return array
     */
    public function getUserInfo($params = []) {
        $code = $params['code'] ?? '';
        if(!is_string($code) || empty($code)) return ["status" => 0, "message" => "Invalid params", "description" => "Missing code"];

        $fileName = "$this->userStorage/$code.json";
        if (file_exists($fileName)) {
            $userData = json_decode(file_get_contents($fileName), true);

            // Add a new turn in daily if the user has not won
            if($userData['status'] == 0 && (!isset($userData['logs'][date('Ymd')]) || empty($userData['logs'][date('Ymd')]))) {
                $userData['turnsRemaining'] = 1;
                $this->writeFile($fileName, json_encode($userData));
            }

            return ["status" => 1, "message" => "Success", "data" => $userData];
        }
        return ["status" => 0, "message" => "User $code not found"];
    }

    /**
     * Add user to event
     * 
     * @param array $params
     * @return array
     */
    public function addUser($params = []) {
        $code = $params['code'] ?? '';
        if(!is_string($code) || empty($code)) return ["status" => 0, "message" => "Invalid params", "description" => "Missing code"];

        $fileName = "$this->userStorage/$code.json";
        $data = [
            "zaloId" => $code,
            "phoneNumber" => "",
            "emails" => [],
            "status" => 0,
            "totalPoint" => 0,
            "voucher" => 20000, // Default voucher 20k
            "topupCards" => [],
            "turnsRemaining" => 1,
            "createdAt" => date('Y-m-d H:i:s'),
            "updatedAt" => date('Y-m-d H:i:s'),
            "logs" => [],
        ];
        
        if (file_exists($fileName)) {
            return ["status" => 0, "message" => "User joined the event"]; 
        }
        elseif($this->writeFile($fileName, json_encode($data))) {
            return ["status" => 1, "message" => "Success", "data" => $data]; 
        }
        return ["status" => 0, "message" => "Add user failed"];
    }

    /**
     * Update user voucher
     * 
     * @param array $params
     * @return array
     */
    public function updateUserPhone($params) {
        $code = $params['code'] ?? '';
        $phoneNumber = $params['phoneNumber'] ?? 0;
        if(!is_string($code) || empty($code)) return ["status" => 0, "message" => "Invalid params", "description" => "Missing code"];
        if(!is_string($phoneNumber) || strlen($phoneNumber) != 10) return ["status" => 0, "message" => "Invalid params", "description" => "Invalid phone number"];

        $arr = $this->getUserInfo(['code' => $code]);
        if(isset($arr['status']) && $arr['status'] == 1) {
            $userData = $arr['data'] ?? [];

            $listPhone = $this->getListPhone();
            if(!in_array($phoneNumber, $listPhone)) {
                $userData['phoneNumber'] = $phoneNumber;
                $userData['updatedAt'] = date('Y-m-d H:i:s');

                $fileName = "$this->userStorage/$code.json";
                if($this->writeFile($fileName, json_encode($userData))) {
                    // Update to list
                    array_push($listPhone, $phoneNumber);
                    $this->writeFile($this->phoneStorage, json_encode($listPhone));

                    return ["status" => 1, "message" => "Update user phone number success", "data" => $userData];
                }
                return ["status" => 0, "message" => "Update user phone number failed"];
            }
            return ["status" => 0, "message" => "Phone number already in use"];
        }
        return $arr;
    }

    /**
     * Update user emails
     * 
     * @param array $params
     * @return array
     */
    public function updateUserEmails($params) {
        $code  = $params['code'] ?? '';
        $email = $params['email'] ?? 0;
        if(!is_string($code) || empty($code)) return ["status" => 0, "message" => "Invalid params", "description" => "Missing code"];
        if(!is_string($email)) return ["status" => 0, "message" => "Invalid params", "description" => "Invalid email"];

        $arr = $this->getUserInfo(['code' => $code]);
        if(isset($arr['status']) && $arr['status'] == 1) {
            $userData = $arr['data'] ?? [];

            $count = count($userData['logs'][date('Ymd')] ?? 0);
            if($count > 1) return ["status" => 0, "message" => "Can not add more email"];
            
            $listEmail = $this->getListEmail();
            if(!is_array($listEmail)) $listEmail = [];
            
            if(!in_array($email, $listEmail)) {
                array_push($userData['emails'], $email);
                $userData['turnsRemaining'] = 1;
                $userData['updatedAt'] = date('Y-m-d H:i:s');

                $fileName = "$this->userStorage/$code.json";
                if($this->writeFile($fileName, json_encode($userData))) {
                    // Update to list
                    array_push($listEmail, $email);
                    $this->writeFile($this->emailStorage, json_encode($listEmail));

                    return ["status" => 1, "message" => "Update user email success", "data" => $userData];
                }
                return ["status" => 0, "message" => "Update user email failed"];
            }
            return ["status" => 0, "message" => "Email already in use"];
        }
        return $arr;
    }

    /**
     * Update user emails
     * 
     * @param array $params
     * @return array
     */
    public function updateUserTopupCards($params) {
        $code  = $params['code'] ?? '';
        $value = (int)($params['value'] ?? 0);
        $status = (int)($params['status'] ?? 0);
        $cardId = (string)($params['cardId'] ?? ''); // Ymd . timestamp
        if(!is_string($code) || empty($code)) return ["status" => 0, "message" => "Invalid params", "description" => "Missing code"];
        if(!is_numeric($value) || $value < 10000 || $value > 100000) return ["status" => 0, "message" => "Invalid params", "description" => "Invalid card value"];

        $arr = $this->getUserInfo(['code' => $code]);
        if(isset($arr['status']) && $arr['status'] == 1) {
            $userData = $arr['data'] ?? [];
            $listCardInDay = $userData['topupCards'][date('Ymd')] ?? [];
            if(count($listCardInDay) > 2) return ["status" => 0, "message" => "Maximum spins"];

            if(isset($cardId) && !empty($cardId)) {
                $date = substr($cardId, 0, 8);
                $time = substr($cardId, 8);
                $userData['topupCards'][$date][$time][$value] = $status;
            }
            else $userData['topupCards'][date('Ymd')][time()] = [$value => $status];
            $userData['updatedAt'] = date('Y-m-d H:i:s');

            $fileName = "$this->userStorage/$code.json";
            if($this->writeFile($fileName, json_encode($userData))) {
                return ["status" => 1, "message" => "Update user email success", "data" => $userData];
            }
            return ["status" => 0, "message" => "Update user email failed"];
        }
        return $arr;
    }

    /**
     * Set round
     * 
     * @param array $params
     * @return array
     */
    public function setRound($params) {
        $code       = $params['code'] ?? '';
        $round      = (int)($params['round'] ?? 0);
        $point      = (int)($params['point'] ?? 0);
        $questions  = $params['questions'] ?? [];

        if(!is_string($code) || empty($code)) 
            return ["status" => 0, "message" => "Invalid params", "description" => "Missing code", "params" => $params];
        if(!is_numeric($round) || $round < 1 || $round > 3) 
            return ["status" => 0, "message" => "Invalid params", "description" => "Invalid round value"];
        if(!is_numeric($point) || $point < 0 || $point > 14) 
            return ["status" => 0, "message" => "Invalid params", "description" => "Invalid point value"];
        if(!is_array($questions) || empty($questions))
            return ["status" => 0, "message" => "Invalid params", "description" => "Invalid list question"];
        
        $arr = $this->getUserInfo(['code' => $code]);
        if(isset($arr['status']) && $arr['status'] == 1) {
            // Prepare data
            $roundId = time() . "-$round";
            $roundStatus = 1;
            $roundQuestions = [];
            foreach($questions as $q) {
                if((int)$q['status'] != 1) $roundStatus = 0;
                $roundQuestions[$q['id']] = [
                    "id" => $q['id'],
                    "answer" => $q['answer'],
                    "status" => (int)$q['status']
                ];
            }
            $roundData = [
                "id"        => $roundId,
                "round"     => $round,
                "status"    => $roundStatus,
                "point"     => $point,
                "questions" => $roundQuestions
            ];

            // Map round data
            $userData = $arr['data'] ?? [];
            $countTurnsInDay = 0;
            if(!isset($userData['logs'][date('Ymd')]) || empty($userData['logs'][date('Ymd')])) {
                // Check result of previous round before adding
                if($round > 1) return ["status" => 0, "message" => "Previous round invalid"];

                $countTurnsInDay = 1;
                $userData['logs'][date('Ymd')][] = [$roundId => $roundData];
            }
            else {
                $index = $round > 1 ? count($userData['logs'][date('Ymd')]) - 1 : count($userData['logs'][date('Ymd')]);
                if($index > 1) return ["status" => 0, "message" => "Maximum 2 turns per day"];

                // Check result of previous round before adding
                if($round > 1) {
                    foreach($userData['logs'][date('Ymd')][$index] as $r) {
                        if(!isset($r['status']) || $r['status'] == 0) return ["status" => 0, "message" => "Did not complete the previous round"];
                    }
                }

                $countTurnsInDay = $index + 1;
                $userData['logs'][date('Ymd')][$index][$roundId] = $roundData;
            }
            // Update total point
            if($roundStatus == 1) {
                if($round == 1 && $countTurnsInDay > 1) $userData['totalPoint'] = $point; // Reset total point
                else $userData['totalPoint'] += $point;
            }
            if($round == 1) {
                if(isset($userData['turnsRemaining']) && $userData['turnsRemaining'] > 0) $userData['turnsRemaining'] -= 1;
                else return ["status" => 0, "message" => "User has run out of turns"];
            }
            if($round == 3) {
                if($roundStatus == 1) {
                    $userData['status'] = 1;
                    $userData['voucher'] += 200000;
                }
                else {
                    $userData['status'] = 0;
                }
            }
            $userData['updatedAt'] = date('Y-m-d H:i:s');
            
            // Save data
            $fileName = "$this->userStorage/$code.json";
            if($this->writeFile($fileName, json_encode($userData))) {
                return ["status" => 1, "message" => "Set round success", "data" => $roundData];
            }
            return ["status" => 0, "message" => "Set round failed"];
        }
        return $arr;
    }

    /**
     * Get round number from round ID
     * 
     * @param string $roundId
     * @return int 1; 2; 3
     */
    private function getRoundNumber($roundId) {
        if(!is_string($roundId) || empty($roundId)) return 0;
        return (int)substr(trim($roundId), -1);
    }

    /**
     * Write content to file
     * 
     * @param string $fileName
     * @param string $content
     * @return bool
     */
    private function writeFile($fileName, $content) {
        if(empty($content)) return false;

        $file = fopen($fileName, "w") or die("Error something !!!");
        fwrite($file, $content);
        fclose($file);
        return true;
    }
}