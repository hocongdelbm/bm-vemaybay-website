<?php
header('Content-Type: application/json');

if (isset($_POST['for']) && $_POST['for'] == 'changeStatusAgent') {
     $agent         = isset($_POST['agent']) ? trim($_POST['agent']) : '';
     $agent_status  = isset($_POST['status']) ? trim($_POST['status']) : '';

     if ($agent && $agent_status) {
          $result = agent_change_status($agent, $agent_status);
          echo json_encode(['success' => $result !== false]);
     } else {
        $GLOBALS['log']->error("changeStatusAgent error: " . $agent . " - " . $agent_status);
        echo json_encode(['success' => false, 'message' => 'Missing agent or status']);
     }
}