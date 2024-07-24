<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token          = isset($_POST['Token']) ? $_POST['Token'] : '';
    $campaign_id    = isset($_POST['CampaignID']) ? $_POST['CampaignID'] : '';
    $url_download   = isset($_POST['URLDownload']) ? $_POST['URLDownload'] : '';
    $status         = isset($_POST['Status']) ? $_POST['Status'] : null;

    if($token != '7edd98c8-f471-d794-4114-6569baa07093') {
        echo json_encode([
            'error' => true,
            'message' => "Not have access",
        ]);
        exit();
    }
    if (empty($campaign_id) || empty($url_download)) {
        echo json_encode([
            'error' => true,
            'message' => "Invalid parameters",
        ]);
        exit();
    }

    // Update
    global $db;
    $arr_map_status = [0=>"fail", 1=>"done"];
    $set_status = !is_null($status) ? ', status="'.$arr_map_status[$status].'"' : '';
    $sql = 'UPDATE ec_sms_logs
        SET url_download = "'. $url_download .'", 
            modified_user_id = "'.$token.'", 
            date_modified = "'.date("Y-m-d H:i:s").'" 
            '. $set_status .'
        WHERE campaign_id = "' . $campaign_id . '" AND deleted = 0;';
    
    $check = $db->query($sql);

    if ($check) echo json_encode(['error' => false, 'message' => "Success"]);
    else echo json_encode(['error' => true, 'message' => "Failed"]);
    exit();
}

header('HTTP/1.1 404 Not Found');
exit();
?>