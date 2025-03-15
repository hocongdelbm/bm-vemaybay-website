<?php

// GR TELE SUPPORT
function sendGrSupportTele($content, $parseMode = 'HTML', $timeout = 8)
{
    // $chat_id = '-1773893748'; // Group support
    $chat_id = '-618676080'; // Group support
    $token = '6713845742:AAF3ilFQEFrUgIN69bpNOeJknCJQhBR4nHU';

    $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chat_id;
    $url = $url . "&parse_mode=" . $parseMode . "&text=" . urlencode($content);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    $result = curl_exec($curl);
    curl_close($curl);
    return

        $result;
}

// GR TEST
function myTelegramBlockIP($postData, $timeout = 20, $format = 'json')
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot1668507961:AAF76B96rWELQlN9lG1g0TO22wcm66jkvTk/sendMessage?chat_id=-1001360390468');

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    $result = json_decode(curl_exec($curl), true);
    curl_close($curl);
    return $result;
}

function sendTestTelegram($content, $parseMode = 'HTML', $timeout = 5)
{
	$chat_id = '-1001360390468'; // Group Test
	$token = '1668507961:AAF76B96rWELQlN9lG1g0TO22wcm66jkvTk';

	$url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chat_id;
	$url = $url . "&parse_mode=" . $parseMode . "&text=" . urlencode($content);
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
	$result = curl_exec($curl);
	curl_close($curl);
	return $result;
}

/**
 * Telegram send message
 * PHIẾU THU
 * PHIẾU CHI
 */
function sendTelegramKeToan2025($postData, $timeout = 20, $format = 'json')
{
    $chat_id = '-387375133'; // Group Ke Toan 2025
	$token = '706494755:AAHpTyV2fo8Jp_r0gCjrvskLyfed-ISKjb4';
    $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chat_id;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=UTF-8',
        'X-API-KEY: MHlV04ML1B8ObhTp7urAF0YImADw656728f095w5',
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    $result = json_decode(curl_exec($curl), true);
    curl_close($curl);
    return $result;
}


/**
 * Telegram send message
 */
function myTelegramSendMessage($postData, $token, $chat_id, $timeout = 20, $format = 'json')
{
    $curl = curl_init();
    // curl_setopt($curl, CURLOPT_URL, 'https://s2.vietnamairlines.bid/index.php/apiv1/telegram/send_message/format/' . $format);
    curl_setopt($curl, CURLOPT_URL, 'https://api.telegram.org/bot' . $token . '/sendMessage?chat_id=' . $chat_id);

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'X-API-KEY: MHlV04ML1B8ObhTp7urAF0YImADw656728f095w5',
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    $result = json_decode(curl_exec($curl), true);
    curl_close($curl);
    return $result;
}


// GR TELE LÝ THÔNG
function sendGrLyThongTele($content, $parseMode = 'HTML', $timeout = 8)
{
    $chat_id = '-1001656085253';
    $token = '2062223399:AAGhuTA3jvRBeCLq8fixOFrY-MecvuA_7AA';

    $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chat_id;
    $url = $url . "&parse_mode=" . $parseMode . "&text=" . urlencode($content);
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}

// GR SUPPORT KT TP
function sendTelegramSupportKTTP($postData, $timeout = 20, $format = 'json')
{
    $chat_id = '-1001756725737'; // Group Support ke toan TP
    $token = '7721433243:AAGz_HUDmSZcXIpsJqYVutROqUOFj4lXAo0';
    $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chat_id;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    $result = json_decode(curl_exec($curl), true);
    curl_close($curl);
    return $result;
}


/**
 * Send warning message to telegram
 *
 * @param string $postData
 * @param int $timeout
 * @param string $format
 * @return array
 */
function sendTelegramWarningSystem($postData, $timeout = 20, $format = 'json')
{
    $chat_id = '-4627498274'; // WARNING SYSTEM BM ECOMMERCE
	$token = '7721433243:AAGz_HUDmSZcXIpsJqYVutROqUOFj4lXAo0';
    $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chat_id;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=UTF-8',
        'X-API-KEY: FyHhxw4weBHbcuzHh5oe9CRhYJd6EjarMHlV04ML1B8ObhTp7urAF0YImADw656',
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
    $result = json_decode(curl_exec($curl), true);
    curl_close($curl);
    return $result;
}