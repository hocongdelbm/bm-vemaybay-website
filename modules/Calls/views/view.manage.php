<?php
require_once("include/Sugar_Smarty.php");

class Viewmanage extends SugarView {
     private $list_phone = [
          'HOTLINE' => [
               '1900636060' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'Travelpass',
               ],
               '1900636063' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'Travelpass',
               ],
          ],
          'VIETTEL' => [
               '0968304455' => [
                    'action' => 'inbound-only',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'Travelpass',
               ],
               '02866509900' => [
                    'action' => 'inbound-only',
                    'label'   => 'Sữa tươi Úc',
                    'brandname'   => 'Travelpass',
               ],

               // 07/11/2024
               '0385291429' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0385295550' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0385295676' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0385297839' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0385299921' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0385299946' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0385300174' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0385300984' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0385301071' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0385301087' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],

               // 04/09/2024
               '0984280718' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'Travelpass',
               ],
               '0984195219' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'Travelpass',
               ],
               '0964031020' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'Travelpass',
               ],
          ],
          'MOBIFONE' => [
               '0933296508' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0933297608' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0933026416' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0933625233' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'Travelpass',
               ],
               '0933799860' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'Travelpass',
               ],
               '0933611306' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0937523198' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'Travelpass',
               ],
               '0937451098' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'Travelpass',
               ],
          ],
          'VINAPHONE' => [
               '0913030802' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'Travelpass',
               ],
               // '0914491010' => [
               //      'action' => 'inbound-only',
               //      'label'   => 'Sữa tươi Úc',
               //      'brandname'   => 'Travelpass',
               // ],
               '0914491010' => [
                    'action' => 'inbound-only',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'Travelpass',
               ],
               '0918038348' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0919018102' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'GIAO NHANH',
               ],
               '0911236600' => [
                    'action' => 'all',
                    'label'   => 'Laptop',
                    'brandname'   => 'GIAO NHANH',
               ],
          ],
          'VNPT' => [
               '02839977788' => [
                    'action' => 'all',
                    'label'   => 'Tìm chuyến bay',
                    'brandname'   => 'Travelpass',
               ],
               '02839977799' => [
                    'action' => 'all',
                    'label'   => 'Sanvemaybay (.com.vn)',
                    'brandname'   => 'Travelpass',
               ],
          ],
          'FPT' => [
               '02873001886' => [
                    'action' => 'all',
                    'label'   => 'Vietjet (.net)',
                    'brandname'   => 'Travelpass',
               ]
          ]
     ];

     function display() {
          $smartyCont = new Sugar_Smarty();
          $this->populateContent($smartyCont);
          $smartyCont->display('modules/Calls/tpls/manage.tpl');

     }

     function populateContent($smartyobj) {
          $tr = '';
          foreach($this->list_phone as $carrier => $list) {
               $i = 1;
               $row = '';
               foreach($list as $number => $value) {
                    if($value['action'] == 'inbound-only') $text_number = '<b>'.formatPhoneNumber($number) . '</b> <i style="margin-left:15px">(Không gọi ra)</i>';
                    else if($value['action'] == 'blocked') $text_number = '<b>'.formatPhoneNumber($number) . '</b> <i class="text-danger" style="margin-left:15px">(Blocked)</i>';
                    elseif($carrier == 'HOTLINE') $text_number = '<b>'.$number.'</b>';
                    else $text_number = '<b>'.formatPhoneNumber($number) . '</b>';

                    if($value['label'] == 'Sữa tươi Úc') $text_label = '<span  class="fw-semibold text-primary">Sữa tươi Úc</span>';
                    else if($value['label'] == 'Vietjet (.net)') $text_label = '<span  class="fw-semibold text-danger">Vietjet (.net)</span>';
                    else $text_label = $value['label'];

                    if($value['label'] == 'Sữa tươi Úc') $website = 'suatuoiuc.vn';
                    else $website = getCallSource($number);

                    $row .= '<tr>
                         <td>'.$text_number.'</td>
                         <td class="text-center fw-semibold '.($value['brandname'] == 'Travelpass' ? 'text-primary' : 'text-success').'">'.$value['brandname'].'</td>
                         <td class="text-center">'.$text_label.'</td>
                         <td class="text-center"><a href="https://'.$website.'" target="_blank">'.$website.'</a></td>
                    </tr>';
                    $i++;
               }

               if($carrier == 'HOTLINE') $carrier = '<b style="color:red">'.$carrier.'</b>';
               elseif($carrier == 'VIETTEL') $carrier = '<b style="color:#ea3a59; text-transform:lowercase;">'.$carrier.'</b>';
               elseif($carrier == 'MOBIFONE') $carrier = '<b style="color:#006db7">mobi</b><b style="color:#ec1d24">fone</b>';
               elseif($carrier == 'VINAPHONE') $carrier = '<b style="color:#00aeed; text-transform:lowercase;">'.$carrier.'</b>';
               elseif($carrier == 'VNPT') $carrier = '<b style="color:#0066ba; letter-spacing:3px;">'.$carrier.'</b>';
               elseif($carrier == 'FPT') $carrier = '<b style="color:#054da2">F</b><b style="color:#f37021">P</b><b style="color:#52b848">T</b>';

               $tr .= '<tr>
                         <td rowspan="'.$i.'" class="text-center"><b>'.$carrier.'</b></td>
                         '.$row.'
                    </tr>';
          }

        	$smartyobj->assign('TBODY', $tr);
     }

     function formatPhoneNumber($phoneNumber) {
          // Loại bỏ các ký tự không phải số
          $cleaned = preg_replace('/\D/', '', $phoneNumber);

          // Kiểm tra xem có đủ số điện thoại không
          preg_match('/^(\d{3})(\d{4})(\d{3})$/', $cleaned, $match);

          if (!empty($match)) {
               // Nếu có, định dạng số điện thoại
               return $match[1] . ' ' . $match[2] . ' ' . $match[3];
          }

          // Nếu không, trả về số điện thoại không đổi
          return $phoneNumber;
     }
}