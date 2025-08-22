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
    private $botToken;
    private $chatId;
    private $threadId;
    private $threadId2;

    public function __construct() {
        global $sugar_config;
        $this->directoryData = "custom/json_files/event_02_09_2025";
        $this->phoneStorage = "$this->directoryData/list_phone.json";
        $this->emailStorage = "$this->directoryData/list_email.json";
        $this->userStorage = "$this->directoryData/users";
        $this->botToken = $sugar_config['telegram']['event020925']['bot_token'] ?? '';
        $this->chatId   = $sugar_config['telegram']['event020925']['chat_id'] ?? '';
        $this->threadId = $sugar_config['telegram']['event020925']['thread_id_lucky_spin'] ?? '';
        $this->threadId2 = $sugar_config['telegram']['event020925']['thread_id_noti'] ?? '';
    }

    /**
     * Get round number from round ID
     * 
     * @return array
     */
    public function getListPhone() {
        if (file_exists($this->phoneStorage)) {
            $json = file_get_contents($this->phoneStorage);
            if(empty($json)) return [];
            return json_decode($json, true);
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
            $json = file_get_contents($this->emailStorage);
            if(empty($json)) return [];
            return json_decode($json, true);
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
        if(!is_string($code) || empty($code)) return ["status" => 0, "message" => "Invalid code value"];

        $fileName = "$this->userStorage/$code.json";
        if (file_exists($fileName)) {
            $userData = json_decode(file_get_contents($fileName), true);

            // Add a new turn in daily if the user has not won
            if($userData['status'] == 0 && (!isset($userData['logs'][date('Ymd')]) || empty($userData['logs'][date('Ymd')]))) {
                $userData['turnsRemaining'] = 1;
                $this->writeFile($fileName, json_encode($userData, JSON_UNESCAPED_UNICODE));
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
        if(!is_string($code) || empty($code)) return ["status" => 0, "message" => "Invalid code value"];

        $fileName = "$this->userStorage/$code.json";
        $data = [
            "zaloId" => $code,
            "phoneNumber" => "",
            "emails" => [],
            "status" => 0,
            "turnsRemaining" => 1,
            "totalPoint" => 0,
            "totalTime" => null,
            "voucher" => 20000, // Default voucher 20k
            "topupCards" => [],
            "logs" => [],
            "createdAt" => date('Y-m-d H:i:s'),
            "updatedAt" => date('Y-m-d H:i:s'),
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
        if(!is_string($code) || empty($code)) return ["status" => 0, "message" => "Invalid code value"];
        if(!is_string($phoneNumber) || strlen($phoneNumber) != 10) return ["status" => 0, "message" => "Invalid phone number value", "messageVi" => "Số điện thoại không hợp lệ"];

        $arr = $this->getUserInfo(['code' => $code]);
        if(isset($arr['status']) && $arr['status'] == 1) {
            $userData = $arr['data'] ?? [];

            $listPhone = $this->getListPhone();
            if(!in_array($phoneNumber, $listPhone)) {
                $userData['phoneNumber'] = $phoneNumber;
                $userData['updatedAt'] = date('Y-m-d H:i:s');

                $fileName = "$this->userStorage/$code.json";
                if($this->writeFile($fileName, json_encode($userData, JSON_UNESCAPED_UNICODE))) {
                    // Update to list
                    array_push($listPhone, $phoneNumber);
                    $this->writeFile($this->phoneStorage, json_encode($listPhone));

                    try {
                        $phoneNumber = $userData['phoneNumber'];
                        $message = "🇻🇳 Người chơi có SĐT $phoneNumber đã tham gia sự kiện\n<i>Code: <b>$code</b></i>";
                        Telegram::sendMessage($message, $this->botToken, $this->chatId, $this->threadId2);
                    }
                    catch(Throwable $th) {}

                    return ["status" => 1, "message" => "Update user phone number success", "data" => $userData];
                }
                return ["status" => 0, "message" => "Update user phone number failed"];
            }
            return ["status" => 0, "message" => "Phone number already in use", "messageVi" => "Số điện thoại đã được sử dụng"];
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
        if(!is_string($code) || empty($code)) return ["status" => 0, "message" => "Invalid code value"];
        if(!is_string($email) || !$this->checkEmail($email)) return ["status" => 0, "message" => "Invalid email value", "messageVi" => "Email không hợp lệ"];

        $arr = $this->getUserInfo(['code' => $code]);
        if(isset($arr['status']) && $arr['status'] == 1) {
            $userData = $arr['data'] ?? [];

            $count = count($userData['logs'][date('Ymd')] ?? 0);
            if($count > 1) return ["status" => 0, "message" => "Can not add more email", "messageVi" => "Đã đạt giới hạn thêm lượt chơi trong ngày"];
            
            $listEmail = $this->getListEmail();
            if(!is_array($listEmail)) $listEmail = [];
            
            if(!in_array($email, $listEmail)) {
                array_push($userData['emails'], $email);
                $userData['turnsRemaining'] = 1;
                $userData['totalPoint'] = 0;
                $userData['updatedAt'] = date('Y-m-d H:i:s');

                $fileName = "$this->userStorage/$code.json";
                if($this->writeFile($fileName, json_encode($userData, JSON_UNESCAPED_UNICODE))) {
                    // Update to list
                    array_push($listEmail, $email);
                    $this->writeFile($this->emailStorage, json_encode($listEmail));

                    return ["status" => 1, "message" => "Update user email success", "data" => $userData];
                }
                return ["status" => 0, "message" => "Update user email failed"];
            }
            return ["status" => 0, "message" => "Email already in use", "messageVi" => "Email đã được sử dụng"];
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
        if(!is_string($code) || empty($code)) return ["status" => 0, "message" => "Invalid code value"];
        if(!is_numeric($value) || $value < 10000 || $value > 100000) return ["status" => 0, "message" => "Invalid card value"];

        $arr = $this->getUserInfo(['code' => $code]);
        if(isset($arr['status']) && $arr['status'] == 1) {
            $userData = $arr['data'] ?? [];

            if(isset($cardId) && !empty($cardId)) {
                $status = 1;
                $date = (string)substr($cardId, 0, 8);
                $time = (string)substr($cardId, 8);
                if(isset($userData['topupCards'][$date]) && isset($userData['topupCards'][$date][$time]) && isset($userData['topupCards'][$date][$time][$value])) $userData['topupCards'][$date][$time][$value] = $status;
                else return ["status" => 0, "message" => "Not found card", "messageVi" => "Không tìm thấy mệnh giá nạp"]; 
            }
            else {
                $listCardInDay = $userData['topupCards'][date('Ymd')] ?? [];
                if(count($listCardInDay) > 2) return ["status" => 0, "message" => "Maximum spins", "messageVi" => "Đã đạt số lần quay thưởng tối đa. Ngày mai quay lại nhé"];
                $date   = (string)date('Ymd');
                $time   = (string)time();
                $cardId = $date . $time;
                $userData['topupCards'][$date][$time] = [$value => $status];
            }
            $userData['updatedAt'] = date('Y-m-d H:i:s');

            $fileName = "$this->userStorage/$code.json";
            if($this->writeFile($fileName, json_encode($userData, JSON_UNESCAPED_UNICODE))) {
                try {
                    if($status == 0) {
                        $phoneNumber = $userData['phoneNumber'];
                        $message = "🎁 Người chơi $phoneNumber đã nhận được thẻ cào <b>". format_number($value, null, 0) ."đ</b>\n<i>Card ID: $cardId</i>";
                        Telegram::sendWebhookMessage($code, $value, $cardId, $message, $this->botToken, $this->chatId, $this->threadId);

                        // $phoneNumber = $userData['phoneNumber'];
                        // $message = "🎁 Người chơi $phoneNumber đã nhận được thẻ cào ". format_number($value, null, 0) ."đ\n<i>Card ID: $cardId</i>";
                        // Telegram::sendMessage($message, $this->botToken, $this->chatId, $this->threadId);
                    }
                }
                catch(Throwable $th) {}

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
        $code      = $params['code'] ?? '';
        $round     = (int)($params['round'] ?? 0);
        $point     = (int)($params['point'] ?? 0);
        $question  = $params['question'] ?? []; // Current question

        if(!is_string($code) || empty($code)) 
            return ["status" => 0, "message" => "Invalid code value"];
        if(!is_numeric($round) || $round < 1 || $round > 3) 
            return ["status" => 0, "message" => "Invalid round value"];
        if(!is_numeric($point) || $point < 0 || $point > 14) 
            return ["status" => 0, "message" => "Invalid point value"];
        if(!is_array($question) || empty($question))
            return ["status" => 0, "message" => "Invalid question"];
        
        $arr = $this->getUserInfo(['code' => $code]);
        if(isset($arr['status']) && $arr['status'] == 1) {
            $userData = $arr['data'] ?? [];

            // Get current turn
            $currentTurnData = [];
            $currentTurnIndex = 0;
            if(isset($userData['logs'][date('Ymd')]) && !empty($userData['logs'][date('Ymd')])) {
                $currentTurnIndex = array_key_last($userData['logs'][date('Ymd')]);
                $currentTurnData = end($userData['logs'][date('Ymd')]);
            }

            // Get current round
            $currentRoundData = [];
            if(!empty($currentTurnData)) {
                $currentRoundData = end($currentTurnData);

                if($round > $currentRoundData["round"]) {
                    if($round > 1 && (!isset($currentTurnData[$round - 2]) || empty($currentTurnData[$round - 2]) || $currentTurnData[$round - 2]['status'] != 1)) {
                        return [
                            "status" => 0,
                            "message" => "Previous round invalid",
                            "messageVi" => "Vui lòng hoàn thành vòng chơi trước",
                        ];
                    }

                    $currentRoundData = [
                        "round"     => $round,
                        "status"    => 0,
                        "point"     => 0,
                        "questions" => [],
                        "createdAt" => date('Y-m-d H:i:s'),
                        "updatedAt" => date('Y-m-d H:i:s'),
                    ];
                }
            }
            else {
                if($round == 1) {
                    if($userData['turnsRemaining'] > 0) {
                        $userData['turnsRemaining'] -= 1;
                        $userData['totalPoint'] = 0; // Reset when playing again in new day
                    }
                    else return ["status" => 0, "message" => "User has run out of turns", "messageVi" => "Bạn đã hết lượt chơi. Mai quay lại nhé"];
                }
                
                $currentRoundData = [
                    "round"     => $round,
                    "status"    => 0,
                    "point"     => 0,
                    "questions" => [],
                    "createdAt" => date('Y-m-d H:i:s'),
                    "updatedAt" => date('Y-m-d H:i:s'),
                ];
            }

            // Play again
            $isPlayAgain = false;
            $lastQuestion = end($currentRoundData['questions']);
            if(is_array($lastQuestion) && $lastQuestion['status'] == 0 && $round == 1) {
                if(isset($userData['turnsRemaining']) && $userData['turnsRemaining'] > 0) $userData['turnsRemaining'] -= 1;
                else return ["status" => 0, "message" => "User has run out of turns", "messageVi" => "Bạn đã hết lượt chơi. Mai quay lại nhé"];

                $isPlayAgain = true;
                $currentTurnIndex++;
                if($currentTurnIndex > 1) {
                    return [
                        "status" => 0,
                        "message" => "Maximum 2 turns per day",
                        "messageVi" => "Đã đạt số lần chơi tối đa trong ngày"
                    ]; 
                }
                $currentTurnData = [];
                $currentRoundData = [
                    "round"     => $round,
                    "status"    => 0,
                    "point"     => 0,
                    "questions" => [],
                    "createdAt" => date('Y-m-d H:i:s'),
                    "updatedAt" => date('Y-m-d H:i:s'),
                ];
            }

            // Update to round data
            foreach($currentRoundData['questions'] as $q) {
                if((!isset($q['status']) || $q['status'] == 0) && !$isPlayAgain) {
                    return [
                        "status" => 0,
                        "message" => "Previous question invalid",
                        "messageVi" => "Bạn đã dừng cuộc chơi vì trả lời sai câu hỏi trước",
                    ];
                }
            }
            $currentRoundData['questions'][] = $question;
            // Complete round
            $isCompletedRound = false;
            if($point > 0 && $question['status'] == 1) {
                $isCompletedRound = true;
                $currentRoundData['point']  = $point;
                $currentRoundData['status'] = 1;
                $userData['totalPoint'] += $point;
            }
            elseif($point > 0 && $question['status'] == 0 && $round == 3) {
                $currentRoundData['point']  = $point;
                $currentRoundData['status'] = 0;
                $userData['totalPoint'] += $point;
            }
            $currentRoundData['updatedAt'] = date('Y-m-d H:i:s');

            // Update to turn data
            $currentTurnData[$round - 1] = $currentRoundData;

            // Update to user data
            if($isCompletedRound && $round == 3) {
                $userData['status'] = 1;
                $userData['voucher'] += 200000;
                $userData['totalTime'] = $this->getTotalTime($currentTurnData);
            }
            $userData['logs'][date('Ymd')][$currentTurnIndex] = $currentTurnData;
            $userData['updatedAt'] = date('Y-m-d H:i:s');

            // Save data
            $fileName = "$this->userStorage/$code.json";
            if($this->writeFile($fileName, json_encode($userData, JSON_UNESCAPED_UNICODE))) {
                return ["status" => 1, "message" => "Set round success", "data" => $currentRoundData];
            }
            return ["status" => 0, "message" => "Set round failed"];



            // // Prepare data
            // $roundId = time() . "-$round";
            // $roundStatus = 1;

            // $roundQuestions = [];
            // foreach($questions as $q) {
            //     if((int)$q['status'] != 1) $roundStatus = 0;
            //     $roundQuestions[$q['id']] = [
            //         "id" => $q['id'],
            //         "answer" => $q['answer'],
            //         "status" => (int)$q['status']
            //     ];
            // }
            // $roundData = [
            //     "id"        => $roundId,
            //     "round"     => $round,
            //     "status"    => $roundStatus,
            //     "point"     => $point,
            //     "questions" => $roundQuestions
            // ];

            // // Map round data
            // $userData = $arr['data'] ?? [];
            // $countTurnsInDay = 0;
            // // First round
            // if(!isset($userData['logs'][date('Ymd')]) || empty($userData['logs'][date('Ymd')])) {
            //     // Check result of previous round before adding
            //     if($round > 1) return [
            //         "status" => 0,
            //         "message" => "Previous round invalid",
            //         "messageVi" => "Chưa hoàn thành vòng chơi trước",
            //     ];

            //     $countTurnsInDay = 1;
            //     $userData['logs'][date('Ymd')][] = [$roundId => $roundData];
            // }
            // else {
            //     $index = $round > 1 ? count($userData['logs'][date('Ymd')]) - 1 : count($userData['logs'][date('Ymd')]);
            //     if($index > 1) return [
            //         "status" => 0,
            //         "message" => "Maximum 2 turns per day",
            //         "messageVi" => "Đã đạt số lần chơi tối đa trong ngày"
            //     ];

            //     // Check result of previous round before adding
            //     if($round > 1) {
            //         foreach($userData['logs'][date('Ymd')][$index] as $r) {
            //             if(!isset($r['status']) || $r['status'] == 0) return [
            //                 "status" => 0,
            //                 "message" => "Did not complete the previous round",
            //                 "messageVi" => "Chưa hoàn thành vòng chơi trước"
            //             ];
            //         }
            //     }

            //     $countTurnsInDay = $index + 1;
            //     $userData['logs'][date('Ymd')][$index][$roundId] = $roundData;
            // }
            // // Update total point
            // if($roundStatus == 1) {
            //     if($round == 1 && $countTurnsInDay > 1) $userData['totalPoint'] = $point; // Reset total point
            //     else $userData['totalPoint'] += $point;
            // }
            // if($round == 1) {
            //     if(isset($userData['turnsRemaining']) && $userData['turnsRemaining'] > 0) $userData['turnsRemaining'] -= 1;
            //     else return ["status" => 0, "message" => "User has run out of turns", "messageVi" => "Bạn đã hết lượt chơi. Mai quay lại nhé"];
            // }
            // if($round == 3) {
            //     if($roundStatus == 1) {
            //         $userData['status'] = 1;
            //         $userData['voucher'] += 200000;
            //     }
            //     else {
            //         $userData['status'] = 0;
            //     }
            // }
            // $userData['updatedAt'] = date('Y-m-d H:i:s');
            
            // // Save data
            // $fileName = "$this->userStorage/$code.json";
            // if($this->writeFile($fileName, json_encode($userData, JSON_UNESCAPED_UNICODE))) {
            //     return ["status" => 1, "message" => "Set round success", "data" => $roundData];
            // }
            // return ["status" => 0, "message" => "Set round failed"];
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
     * Get total completion time by logs
     * 
     * @param array $completedTurn
     * @return int Second
     */
    private function getTotalTime($completedTurn) {
        $totalTime = 0;
        foreach($completedTurn as $round) {
            $totalTime += strtotime($round['updatedAt']) - strtotime($round['createdAt']);
        }
        return $totalTime;
    }

    /**
     * Is valid email
     * 
     * @param string $email
     * @return bool
     */
    private function checkEmail($email) {
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        $arr = explode("@", $email);
        $isGmail = stripos($arr[1], "gmail") ? true : false;
        $localPart = $arr[0] ?? '';
        $localPartLength = strlen($localPart);

        if($isGmail) {
            if($localPartLength < 6 || $localPartLength > 30) return false;
        }
        else {
            if(empty($localPart) || strlen($localPartLength) > 64) return false;
        }
        
        if(is_numeric($localPart)) return false;
        elseif(array_unique(str_split($localPart)) < 4) return false;
        return true;
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