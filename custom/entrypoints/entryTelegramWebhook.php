<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER["REQUEST_METHOD"] === "POST") {}

http_response_code(404);
echo json_encode(["status" => 0, "message" => "Not found"]);
exit();