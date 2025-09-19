<?php
// Loại trừ user Booker - 493ad5e5-ffea-a84f-96d7-6577fed623d6
$array_admin = [
     '168889bb-54c2-59c7-8b3f-649102530d3c', // hungnh
     '622ecf27-f729-7187-7e27-6520e0dab882', // quangnd
     '4f4d7a13-4171-9b7d-251c-64dd8f9885e4', // nhat
     '9eb0f65f-a9f6-65bb-1985-637ca8511491', // trinh
     '1', // DucPham
];

if (isset($_POST['for']) && $_POST['for'] == 'changeStatusAgent') {
     $agent         = isset($_POST['agent']) ? trim($_POST['agent']) : '';
     $agent_status  = isset($_POST['status']) ? trim($_POST['status']) : '';

     if ($agent && $agent_status) {
          agent_change_status($agent, $agent_status);
     }
}

if (isset($_POST['for']) && $_POST['for'] == 'saveLastClickUser') {
     return false;
     global $current_user;
     $time     = $_POST['time'] ?? null;
     $status   = $_POST['agent_status'] ?? 'Available';
     $busy     = ($status == 'Available') ? 0 : 1;

     // Booker
     if ($current_user->id === '493ad5e5-ffea-a84f-96d7-6577fed623d6') {
          content_log($current_user->id, $time, $busy);
          echo 1;
          return;
     }

     // Admin - KT
     $array_adminkt = array(
          '72ece22c-cb25-8e30-9dea-56f2201cd359',
          '9ba5c5a0-a402-02f4-76d3-53ba0481ce45',
          'b5523dbd-b9a7-67c0-77b5-533e6ece89b1',
     );

     if ((!is_admin($current_user) || in_array($current_user->id, $array_adminkt)) && $current_user->id !== '493ad5e5-ffea-a84f-96d7-6577fed623d6') {
          if (is_null($time) || empty($time)) {
               echo 0;
               exit();
          }

          content_log($current_user->id, $time, $busy);
          echo 1;
     }
}