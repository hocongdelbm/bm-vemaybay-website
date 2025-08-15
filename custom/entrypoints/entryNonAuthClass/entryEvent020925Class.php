<?php
require_once 'custom/entrypoints/entryNonAuthClass/entryClass.php';

class entryEvent020925Class extends entryClass {
    private $directoryData;

    public function __construct($request = []) {
        $this->request = $request;
        $this->directoryData = "custom/json_files/event_02_09_2025";
    }

    /**
     * Get user info
     * 
     * @param string $code Zalo ID
     * @return array
     */
    public function getUserInfo($code) {
        if(!is_string($code) || empty($code)) return ["status" => 0, "message" => "Invalid params"];

        $fileName = "$this->directoryData/$code.json";
        if (file_exists($fileName)) {
            $userData = json_decode(file_get_contents($fileName), true);
            return ["status" => 1, "message" => "Success", "data" => $userData];
        }
        return ["status" => 0, "message" => "User $code not found"];
    }

    /**
     * Add user to event
     * 
     * @param string $code Zalo ID
     * @return array
     */
    public function addUser($code) {
        if(!is_string($code) || empty($code)) return ["status" => 0, "message" => "Invalid params"];

        $fileName = "$this->directoryData/$code.json";
        $data = [
            "zaloId" => trim($code),
            "phoneNumber" => "",
            "name" => "",
            "point" => 0,
            "playsRemaining" => 2,
            "currentRound" => "",
            "roundLogs" => [],
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
     * Add new round
     * 
     * @param string $code Zalo ID
     * @param int $roundNum 1, 2, 3
     * @return array
     */
    public function addNewRound($code, $roundNum) {
        if($roundNum < 1 || $roundNum > 3) return ["status" => 0, "message" => "Invalid params"];

        $arr = $this->getUserInfo($code);
        if(isset($arr['status']) && $arr['status'] == 1) {
            $userData = $arr['data'] ?? [];
            $roundId = "$roundNum-" . time();
            $newRound = [
                "id"        => $roundId,
                "round"     => (int)$roundNum,
                "status"    => -1, // Default is not started
                "typePrize" => "",
                "prize"     => null,
                "logs"      => []
            ];

            if(is_array($userData) && !empty($userData) && count($userData["roundLogs"]) < 7) {
                // Check order round
                if($roundNum == 3 && substr($userData["currentRound"], 0, 1) != '2') return ["status" => 0, "message" => "Can not add round 3 without round 2"]; 
                if($roundNum == 2 && substr($userData["currentRound"], 0, 1) != '1') return ["status" => 0, "message" => "Can not add round 2 without round 1"]; 

                $userData["currentRound"] = $roundId;
                $userData["roundLogs"][$roundId] = $newRound;
                $fileName = "$this->directoryData/$code.json";
                if($this->writeFile($fileName, json_encode($userData))) {
                    return ["status" => 1, "message" => "Add new round success", "data" => $newRound];
                }
                return ["status" => 0, "message" => "Add new round fail"];
            }
            return ["status" => 0, "message" => "User $code can not add new round"];
        }
        return $arr;
    }

    /**
     * Update user info
     * 
     * @param string $code Zalo ID
     * @param string $phoneNumber
     * @param string $name
     * @return array
     */
    public function updateUserInfo($code, $phoneNumber, $name) {
        if(!is_string($phoneNumber) || empty($phoneNumber)) return ["status" => 0, "message" => "Invalid params"];

        $arr = $this->getUserInfo($code);
        if(isset($arr['status']) && $arr['status'] == 1) {
            $userData = $arr['data'] ?? [];

            $userData['phoneNumber'] = trim($phoneNumber);
            $userData['name'] = trim($name);
            $fileName = "$this->directoryData/$code.json";
            if($this->writeFile($fileName, json_encode($userData))) {
                return ["status" => 1, "message" => "Update user success", "data" => $userData];
            }
            return ["status" => 0, "message" => "Update user fail"];
        }
        return $arr;
    }

    /**
     * Update user point
     * 
     * @param string $code Zalo ID
     * @param int $point
     * @return array
     */
    public function updateUserPoint($code, $point) {
        if($point < 0 || $point > 15) return ["status" => 0, "message" => "Invalid params"];

        $arr = $this->getUserInfo($code);
        if(isset($arr['status']) && $arr['status'] == 1) {
            $userData = $arr['data'] ?? [];

            $userData['point'] = $point;
            $fileName = "$this->directoryData/$code.json";
            if($this->writeFile($fileName, json_encode($userData))) {
                return ["status" => 1, "message" => "Update user point success", "data" => $userData];
            }
            return ["status" => 0, "message" => "Update user point fail"];
        }
        return $arr;
    }

    /**
     * Update user plays remaining
     * 
     * @param string $code Zalo ID
     * @param int $playsRemaining
     * @return array
     */
    public function updateUserPlaysRemaining($code, $playsRemaining) {
        if($playsRemaining < 0 || $playsRemaining > 1) return ["status" => 0, "message" => "Invalid params"];

        $arr = $this->getUserInfo($code);
        if(isset($arr['status']) && $arr['status'] == 1) {
            $userData = $arr['data'] ?? [];

            $userData['playsRemaining'] = (int)$playsRemaining;
            $fileName = "$this->directoryData/$code.json";
            if($this->writeFile($fileName, json_encode($userData))) {
                return ["status" => 1, "message" => "Update user plays remaining success", "data" => $userData];
            }
            return ["status" => 0, "message" => "Update user plays remaining fail"];
        }
        return $arr;
    } 

    /**
     * Update round user
     * 
     * @param string $code Zalo ID
     * @param string $roundId
     * @param int $status 0:Fail 1:Sucess -1:Pending
     * @param string $typePrize point, card
     * @param int $prize
     * @return array
     */
    public function updateRound($code, $roundId, $status, $typePrize = '', $prize = null) {
        if($status < -1 || $status > 1) return ["status" => 0, "message" => "Invalid params"];

        $arr = $this->getUserInfo($code);
        if(isset($arr['status']) && $arr['status'] == 1) {
            $userData = $arr['data'] ?? [];
            $roundData = $userData['roundLogs'][$roundId] ?? [];
            if(!empty($roundData)) {
                $roundData['status'] = $status;
                $roundData['typePrize'] = $typePrize ?? '';
                $roundData['prize'] = (int)$prize;

                // Has user completed all questions in this round?
                $roundNum = $this->getRoundNumber($roundId);
                if($status == 1 && $roundNum < 3) {
                    $check = !isset($roundData['logs']) || empty($roundData['logs']) ? false : true;
                    foreach($roundData['logs'] as $rdetail) {
                        if($rdetail['status'] != 1) {
                            $check = false;
                            break;
                        }
                    }
                    if(!$check) return ["status" => 0, "message" => "Please complete all questions before completing the round"];
                }

                $userData['roundLogs'][$roundId] = $roundData;
                $fileName = "$this->directoryData/$code.json";
                if($this->writeFile($fileName, json_encode($userData))) {
                    return ["status" => 1, "message" => "Update round success", "data" => $roundData];
                }
                return ["status" => 0, "message" => "Update round fail"];
            }
            return ["status" => 0, "message" => "Round ID $roundId not found"];
        }
        return $arr;
    }

    /**
     * Set round detail (Add or update)
     * 
     * @param string $code Zalo ID
     * @param string $roundId
     * @param string $questionId
     * @param string $questionAnswer
     * @param int $status -1; 0; 1
     * @return array
     */
    public function setRoundDetail($code, $roundId, $questionId, $questionAnswer = "", $status = -1) {
        if($status < -1 || $status > 1) return ["status" => 0, "message" => "Invalid params"];
        
        $arr = $this->getUserInfo($code);
        if(isset($arr['status']) && $arr['status'] == 1) {
            $userData = $arr['data'] ?? [];
            $roundData = $userData['roundLogs'][$roundId] ?? [];

            if(!empty($roundData)) {
                if(isset($roundData['logs'][$questionId])) {
                    $roundData['logs'][$questionId]['questionId'] = $questionId;
                    $roundData['logs'][$questionId]['questionAnswer'] = $questionAnswer ?? '';
                    $roundData['logs'][$questionId]['status'] = $status ?? -1;
                    $roundData['logs'][$questionId]['updatedAt'] = date('Y-m-d H:i:s');
                }
                else {
                    $roundData['logs'][$questionId] = [
                        "questionId"    => $questionId,
                        "questionAnswer"=> $questionAnswer ?? '',
                        "status"        => $status ?? -1,
                        "createdAt"     => date('Y-m-d H:i:s'),
                        "updatedAt"     => date('Y-m-d H:i:s')
                    ];
                }
                $userData['roundLogs'][$roundId] = $roundData;

                $fileName = "$this->directoryData/$code.json";
                if($this->writeFile($fileName, json_encode($userData))) {
                    return ["status" => 1, "message" => "Set round detail success", "data" => $roundData['logs'][$questionId]];
                }
                return ["status" => 0, "message" => "Set round detail fail"];
            }
            return ["status" => 0, "message" => "Round ID $roundId not found"];
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
        return (int)substr(trim($roundId), 0, 1);
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