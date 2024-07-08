<?php

if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $db, $app_list_strings;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
     if (isset($_POST['for']) && $_POST['for'] == 'checkValidVoucher') {
          $code_voucher       = isset($_POST["code_voucher"]) ? trim($_POST["code_voucher"]) : '';
          $applied_id         = isset($_POST["applied_id"]) ? trim($_POST["applied_id"]) : '';
          $applied_discount   = isset($_POST["discount"]) ? trim($_POST["discount"]) : '';

          $wayflight          = isset($_POST["wayflight"]) ? (int)trim($_POST["wayflight"]) : '';
          $total_amount       = isset($_POST["total_amount"]) ? trim($_POST["total_amount"]) : '';
          $total_qty          = isset($_POST["total_qty"]) ? trim($_POST["total_qty"]) : '';
          $ticket_type        = isset($_POST["ticket_type"]) ? trim($_POST["ticket_type"]) : '';
          $journey            = isset($_POST["journey"]) ? trim($_POST["journey"]) : '';

          $response = array(
               'status'  => 0,
               'content' => null,
               'value'   => null,
               'id'      => null
          );

          if (empty($code_voucher)) {
               if (!empty($applied_id)) {
                    $sql = '
                         SELECT id,
                         reduce_amount
                         FROM ec_vouchers
                         WHERE id = "' . $applied_id . '" AND deleted = 0
                    ';
                    $res = $db->query($sql);
                    while ($row = $db->fetchByAssoc($res)) {
                         $reduce   = isset($row['reduce_amount']) ? $row['reduce_amount'] : 0;
                         $response = array(
                              'status'  => 2,
                              'content' => 'Removed voucher',
                              'value'   => $reduce,
                              'id'      => ''
                         );
                    }
               } else {
                    $response['content'] = 'Vui lòng nhập voucher';
               }
          } else {
               $sql = '
                    SELECT id, name, status,
                    validate_from_date,
                    validate_to_date,
                    reduce_amount,
                    condition_voucher
                    FROM ec_vouchers
                    WHERE name = "' . $code_voucher . '" AND deleted = 0
               ';

               $res = $db->query($sql);
               while ($row = $db->fetchByAssoc($res)) {
                    if ($row['id'] != $applied_id) {
                         if ($row['status'] == 'new') {
                              $response['content'] = 'Voucher chưa được kích hoạt';
                         } else if ($row['status'] == 'done') {
                              $response['content'] = 'Voucher đã được sử dụng';
                         } else if ($row['status'] == 'cancel') {
                              $response['content'] = 'Voucher không có hiệu lực';
                         } elseif (strtotime(date("Y-m-d")) < strtotime($row['validate_from_date'])) {
                              $response['content'] = 'Voucher chưa tới ngày áp dụng (' . date("d-m-Y", strtotime($row['validate_from_date'])) . ')';
                         } elseif ($row['status'] == 'expired' && strtotime(date("Y-m-d")) > strtotime($row['validate_to_date'])) {
                              $response['content'] = 'Voucher đã hết hạn';
                         } else {
                              $condition_voucher = json_decode(html_entity_decode($row['condition_voucher']), true);

                              if (isset($condition_voucher) && !empty($condition_voucher)) {
                                   $is_error = 0;

                                   foreach ($condition_voucher as $condition) {
                                        // Kiểm tra hành trình
                                        if ($condition['field'] == 'journey') {
                                             $result_journey = compareConditionVoucher('journey', $journey, $condition['operator'], $condition['value'], $wayflight);
                                             if ($result_journey['code'] == 400) {
                                                  $response['content'] = 'Hành trình không thỏa điều kiện voucher';
                                                  $is_error = 1;
                                             }
                                        }

                                        // Kiểm tra total_amount
                                        if ($condition['field'] == 'total_amount') {
                                             $result_total_amount = compareConditionVoucher('total_amount', $total_amount, $condition['operator'], $condition['value']);
                                             if ($result_total_amount['code'] == 400) {
                                                  $response['content'] = 'tổng giá trị đơn hàng không thỏa điều kiện voucher';
                                                  $is_error = 1;
                                             }
                                        }

                                        // Kiểm tra sl vé total_qty
                                        if ($condition['field'] == 'total_qty') {
                                             $result_total_qty = compareConditionVoucher('total_qty', $total_qty, $condition['operator'], $condition['value']);
                                             if ($result_total_qty['code'] == 400) {
                                                  $response['content'] = 'tổng số vé không thỏa điều kiện voucher';
                                                  $is_error = 1;
                                             }
                                        }

                                        // Phạm vi áp dụng
                                        if ($condition['field'] == 'ticket_type') {
                                             $result_ticket_type = compareConditionVoucher('ticket_type', $ticket_type, $condition['operator'], $condition['value']);
                                             if ($result_ticket_type['code'] == 400) {
                                                  $response['content'] = 'Phạm vi áp dụng không thỏa điều kiện voucher';
                                                  $is_error = 1;
                                             }
                                        }


                                        // Chuyến bay
                                        if ($condition['field'] == 'flight_type') {
                                             $result_flight_type = compareConditionVoucher('flight_type', $wayflight, $condition['operator'], $condition['value']);

                                             if ($result_flight_type['code'] == 400) {
                                                  $response['content'] = 'Chiều chuyến bay không thỏa điều kiện voucher';
                                                  $is_error = 1;
                                             }
                                        }
                                   }

                                   if ($is_error == 0) {
                                        $response['status']      = 1;
                                        $response['content']     = 'Áp dụng thành công';
                                        $response['value']       = $row['reduce_amount'];
                                        $response['id']          = $row['id'];
                                   } else {
                                        $response['value']       = 0;
                                        $response['id']          = '';
                                   }
                              } else {
                                   $response['status']  = 1;
                                   $response['content'] = 'Áp dụng thành công';
                                   $response['value']   = $row['reduce_amount'];
                                   $response['id']      = $row['id'];
                              }
                         }
                    } else {
                         $response['content'] = 'Voucher đã được áp dụng';
                    }
               }
          }

          echo json_encode($response, JSON_UNESCAPED_UNICODE);
          exit();
     }

     // Save voucher
     if (isset($_POST['for']) && $_POST['for'] == 'saveVoucher') {
          $booking_id    = isset($_POST["booking_id"]) ? trim($_POST["booking_id"]) : '';
          $voucher       = isset($_POST["voucher"]) ? trim($_POST["voucher"]) : '';
          $voucher_id    = isset($_POST["voucher_id"]) ? trim($_POST["voucher_id"]) : '';
          $applied_id    = isset($_POST["applied_id"]) ? trim($_POST["applied_id"]) : '';

          if (empty($voucher_id) && empty($voucher)) {
               // Hủy voucher
               $sql_voucher = '
                    UPDATE ec_vouchers
                    SET status = "cancel"
                    WHERE booking_receive_id ="' . $booking_id . '" and status <> "cancel" and deleted = 0';
               $db->query($sql_voucher);
               exit();
          } else {
               // Cập nhật thông tin booking cho voucher
               $sql = 'SELECT 
                         id
                         , email
                         , address
                         , phone
                         , contact_name
                    FROM ec_flight_bookings
                    WHERE id = "' . $booking_id . '" AND deleted = 0';

               $res = $db->query($sql);
               while ($row = $db->fetchByAssoc($res)) {
                    // Update
                    $sql_update = '
                         UPDATE ec_vouchers
                         SET 
                         booking_receive_id ="' . $booking_id . '"
                         , account_email = "' . $row['email'] . '"
                         , account_address = "' . $row['address'] . '"
                         , account_phone = "' . $row['phone'] . '"
                         , account_name = "' . $row['contact_name'] . '"
                         , applied_date = "' . date('Y-m-d H:i:s') . '"
                         , status = "done"
                         WHERE id ="' . $voucher_id . '" and status <> "cancel" and deleted = 0';
                    $db->query($sql_update);
               }

               // Hủy voucher đã applied trước đó
               if($voucher_id != $applied_id){
                    $sql_cancel = '
                         UPDATE ec_vouchers
                         SET status = "cancel"
                         WHERE id ="' . $applied_id . '" and status <> "cancel" and deleted = 0';
                    $db->query($sql_cancel);
               }
               
               exit();
          }
     }
}


function compareConditionVoucher($field_name, $field_compare, $operator, $value, $wayflight = '')
{
     switch (trim($field_name)) {
          case 'journey':
               $result = checkConditionJourney($field_compare, $wayflight, $operator, $value);
               break;
          case 'total_amount':
               $result = checkConditionTotalAmount($field_compare, $operator, $value);
               break;
          case 'total_qty':
               $result = checkConditionTotalQty($field_compare, $operator, $value);
               break;
          case 'ticket_type':
               $result = checkConditionTicketType($field_compare, $operator, $value);
               break;
          case 'flight_type':
               $result = checkConditionFlightType($field_compare, $operator, $value);
               break;
          default:
     }

     return $result;
}

function checkConditionJourney($journey, $wayflight, $operator, $value)
{
     $res = array(
          'content' => null,
          'error' => null,
          'code' => 200
     );

     $arr_journey = explode("|", $value);

     if ($operator == '!=') {
          if (($wayflight == 0 && !in_array($journey, $arr_journey) && !in_array(implode('-', array_reverse(explode('-', $journey))), $arr_journey))
               || ($wayflight == 1 && !in_array($journey, $arr_journey))
          ) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Hành trình không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     } else if ($operator == '==') {
          if (($wayflight == 0 && in_array($journey, $arr_journey) && in_array(implode('-', array_reverse(explode('-', $journey))), $arr_journey))
               || ($wayflight == 1 && in_array($journey, $arr_journey))
          ) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Hành trình không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     } else {
          $res = array(
               'content' => null,
               'error' => 'Toán tử so sánh journey không hợp lệ!',
               'code' => 400
          );
     }

     return $res;
}

function checkConditionTotalAmount($total_amount, $operator, $value)
{
     $res = array(
          'content' => null,
          'error' => null,
          'code' => 200
     );

     if ($operator == '<') {
          if ((int)$total_amount < (int)$value) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Tổng giá booking không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     } else if ($operator == '<=') {
          if ((int)$total_amount <= (int)$value) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Tổng giá booking không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     } else if ($operator == '==') {
          if ((int)$total_amount == (int)$value) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Tổng giá booking không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     } else if ($operator == '>') {
          if ((int)$total_amount > (int)$value) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Tổng giá booking không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     } else if ($operator == '>=') {
          if ((int)$total_amount >= (int)$value) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Tổng giá booking không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     } else if ($operator == '!=') {
          if ((int)$total_amount != (int)$value) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Tổng giá booking không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     }

     return $res;
}

function checkConditionTotalQty($total_qty, $operator, $value)
{
     $res = array(
          'content' => null,
          'error' => null,
          'code' => 200
     );

     if ($operator == '<') {
          if ((int)$total_qty < (int)$value) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Tổng SL vé không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     } else if ($operator == '<=') {
          if ((int)$total_qty <= (int)$value) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Tổng SL vé không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     } else if ($operator == '==') {
          if ((int)$total_qty == (int)$value) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Tổng SL vé không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     } else if ($operator == '>') {
          if ((int)$total_qty > (int)$value) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Tổng SL vé không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     } else if ($operator == '>=') {
          if ((int)$total_qty >= (int)$value) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Tổng SL vé không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     } else if ($operator == '!=') {
          if ((int)$total_qty != (int)$value) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Tổng SL vé không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     }

     return $res;
}

function checkConditionTicketType($ticket_type, $operator, $value)
{
     $res = array(
          'content' => null,
          'error' => null,
          'code' => 200
     );

     if ($operator == '==') {
          if ($ticket_type == $value) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Phạm vi áp dụng không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     } else {
          $res = array(
               'content' => null,
               'error' => 'Toán tử so sánh ticket_type không hợp lệ!',
               'code' => 400
          );
     }

     return $res;
}

function checkConditionFlightType($wayflight, $operator, $value)
{
     $res = array(
          'content' => null,
          'error' => null,
          'code' => 200
     );

     if ($operator == '==') {
          if ($wayflight == $value) {
               $res = array(
                    'content' => 'Áp dụng thành công',
                    'error' => null,
                    'code' => 200
               );
          } else {
               $res = array(
                    'content' => 'Voucher không thể áp dụng',
                    'error' => 'Direction không thỏa điều kiện voucher',
                    'code' => 400
               );
          }
     } else {

          $res = array(
               'content' => null,
               'error' => 'Toán tử so sánh wayflight không hợp lệ!',
               'code' => 400
          );
     }

     return $res;
}
