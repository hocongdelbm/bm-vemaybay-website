<?php
require_once 'custom/entrypoints/entryNonAuthClass/entryClass.php';

class entryNationalDayEvent25 extends entryClass {
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
        $fileData = "$this->directoryData/$code.json";
        
        if (file_exists($fileData)) {
            $userData = json_decode(file_get_contents($fileData), true);
            return ["status" => 1, "message" => "Success", "data" => $userData];
        }
        else {
            return ["status" => 0, "message" => "User has not joined the event"];
        }
    }

    /**
     * Add user to event
     * 
     * @param string $code Zalo ID
     * @return array
     */
    public function addUser($code) {
        $fileName = "$this->directoryData/$code.json";
        $data = [
            "zaloId" => $code,
            "phoneNumber" => "",
            "name" => "",
            "point" => 0,
            "playsRemaining " => 2,
            "currentRound" => 1,
            "logs" => [],
        ];
        
        if($this->writeFile($fileName, json_encode($data))) {
            return ["status" => 1, "message" => "User has not joined the event", "data" => $data]; 
        }
        return ["status" => 0, "message" => "Add user failed"];
    }

    /**
     * Update round user
     * 
     * @param int $round 1, 2, 3
     * @param array $logs
     * @param int $status 0:Fail 1:Sucess -1:Pending
     * @param string $typePrize point, card
     * @param int $prize
     * @return array
     */
    public function updateRoundUser($round, $logs, $status, $typePrize = null, $prize = null) {
        $data = [
            "round"     => $round,
            "logs"      => $logs,
            "status"    => $status,
            "typePrize" => $typePrize ?? null,
            "prize"     => $round ?? null,
        ];
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