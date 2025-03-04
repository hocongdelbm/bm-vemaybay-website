<?php
require_once("include/Sugar_Smarty.php");

class Viewmanage extends SugarView {
     function display() {
          $smartyCont = new Sugar_Smarty();
          $this->populateContent($smartyCont);
          $smartyCont->display('modules/Calls/tpls/manage.tpl');

     }

     function populateContent($smartyobj) {
          $tr = '';
          $pbx = BeanFactory::getBean('Calls');
          $list_phone = $pbx->get_list_phone_pbx();

          foreach($list_phone as $carrier => $list) {
               $i = 1;
               $row = '';
               foreach($list as $value) {
                    if($value['only_inbound'] === 1) $text_number = '<b>'.formatPhoneNumber($value['name']) . '</b> <i style="margin-left:15px">(Không gọi ra)</i>';
                    elseif(strtoupper($carrier) === 'HOTLINE') $text_number = '<b>'.$value['name'].'</b>';
                    else $text_number = '<b>'.formatPhoneNumber($value['name']) . '</b>';

                    if($value['label'] == 'Sữa tươi Úc') $text_label = '<span  class="fw-semibold text-primary">Sữa tươi Úc</span>';
                    else if($value['label'] == 'Vietjet (.net)') $text_label = '<span  class="fw-semibold text-danger">Vietjet (.net)</span>';
                    else $text_label = $value['label'];

                    if($value['label'] == 'Sữa tươi Úc') $website = 'suatuoiuc.vn';
                    else $website = getCallSource($value['name']);

                    $row .= '<tr>
                         <td><a href="index.php?module=EC_Outbound_Phone&action=DetailView&record='.$value['id'].'" target="_blank">'.$text_number.'</a></td>
                         <td class="text-center fw-semibold '.($value['brand_name'] == 'Travelpass' ? 'text-primary' : 'text-success').'">'.$value['brand_name'].'</td>
                         <td class="text-center">'.$text_label.'</td>
                         <td class="text-center"><a href="https://'.$website.'" target="_blank">'.$website.'</a></td>
                    </tr>';
                    $i++;
               }

               if(strtoupper($carrier) === 'HOTLINE') $carrier = '<b style="color:red">'.$carrier.'</b>';
               elseif(strtoupper($carrier) === 'VIETTEL') $carrier = '<b style="color:#ea3a59; text-transform:lowercase;">'.$carrier.'</b>';
               elseif(strtoupper($carrier) === 'MOBIPHONE') $carrier = '<b style="color:#006db7">mobi</b><b style="color:#ec1d24">fone</b>';
               elseif(strtoupper($carrier) === 'VINAPHONE') $carrier = '<b style="color:#00aeed; text-transform:lowercase;">'.$carrier.'</b>';
               elseif(strtoupper($carrier) === 'VNPT') $carrier = '<b style="color:#0066ba; letter-spacing:3px;">'.$carrier.'</b>';
               elseif(strtoupper($carrier) === 'FPT') $carrier = '<b style="color:#054da2">F</b><b style="color:#f37021">P</b><b style="color:#52b848">T</b>';

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