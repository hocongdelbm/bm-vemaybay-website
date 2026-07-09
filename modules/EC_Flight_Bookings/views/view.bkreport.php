<?php

/**
 * Thống kê vé theo Nhà cung cấp (NCC).
 *
 * Suy NCC cho từng chứng từ theo dòng vé ec_booking_details (supplier_id).
 */
class Viewbkreport extends SugarView
{
     function display()
     {
          global $current_user;

          if (is_admin($current_user) || isManagerUser()) {
               $smartyCont = new Sugar_Smarty();
               $this->populateContent($smartyCont);
               $smartyCont->display('modules/EC_Flight_Bookings/tpls/view_bkreport.tpl');
          } else {
               header("Location: index.php?module=EC_Flight_Bookings&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
               exit();
          }
     }

     function populateContent($smarty)
     {
          // đến ngày
          if (empty($_REQUEST['to_date'])) {
               $to_date = date('Y-m-d');
          } else $to_date = $_REQUEST['to_date'];

          // từ ngày
          if (empty($_REQUEST['from_date'])) {
               $from_date = date('Y-m-d', strtotime($to_date));
          } else $from_date = $_REQUEST['from_date'];

          // OPTION DATE - check quarter (đồng bộ với bkagent)
          switch (ceil(date('n') / 3)) {
               case 1:
                    $cq_from_date = '01-01-' . date('Y');
                    $cq_to_date = '31-03-' . date('Y');
                    $lq_from_date = '01-01-' . date('Y', strtotime('- 1 year'));
                    $lq_to_date = '31-03-' . date('Y', strtotime('- 1 year'));
                    break;
               case 2:
                    $cq_from_date = '01-04-' . date('Y');
                    $cq_to_date = '30-06-' . date('Y');
                    $lq_from_date = '01-01-' . date('Y');
                    $lq_to_date = '31-03-' . date('Y');
                    break;
               case 3:
                    $cq_from_date = '01-07-' . date('Y');
                    $cq_to_date = '30-09-' . date('Y');
                    $lq_from_date = '01-04-' . date('Y');
                    $lq_to_date = '30-06-' . date('Y');
                    break;
               case 4:
                    $cq_from_date = '01-10-' . date('Y');
                    $cq_to_date = '31-12-' . date('Y');
                    $lq_from_date = '01-07-' . date('Y');
                    $lq_to_date = '30-09-' . date('Y');
                    break;
               default:
                    $cq_from_date = '';
                    $cq_to_date = '';
                    $lq_from_date = '';
                    $lq_to_date = '';
                    break;
          }
          $report_term_list = '<option from_date="' . date('d-m-Y') . '" to_date="' . date('d-m-Y') . '">Hôm nay</option>';
          $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime("-1 day")) . '" to_date="' . date('d-m-Y', strtotime("-1 day")) . '">Hôm qua</option>';
          $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime("-2 day")) . '" to_date="' . date('d-m-Y', strtotime("-2 day")) . '">Hôm trước</option>';
          $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime("first day of this month")) . '" to_date="' . date('d-m-Y', strtotime("last day of this month")) . '">Tháng này</option>';
          $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime("first day of previous month")) . '" to_date="' . date('d-m-Y', strtotime("last day of previous month")) . '">Tháng trước</option>';
          $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime($cq_from_date)) . '" to_date="' . date('d-m-Y', strtotime($cq_to_date)) . '">Quý này</option>';
          $report_term_list .= '<option from_date="' . date('d-m-Y', strtotime($lq_from_date)) . '" to_date="' . date('d-m-Y', strtotime($lq_to_date)) . '">Quý trước</option>';
          $smarty->assign('REPORT_TERM_LIST', $report_term_list);

          $smarty->assign('FROM_DATE_VALUE', date('d-m-Y', strtotime($from_date)));
          $smarty->assign('TO_DATE_VALUE', date('d-m-Y', strtotime($to_date)));


          $mode = isset($_REQUEST['mode']) && $_REQUEST['mode'] === 'airline' ? 'airline' : 'supplier';
          $smarty->assign('REPORT_MODE', $mode);

          $report = $this->buildRevenueReport($from_date, $to_date, $mode);

          $smarty->assign('SUPPLIER_SUMMARY_TBL', $report['summary']);
          $smarty->assign('SUPPLIER_DETAIL_TBL', $report['detail']);
          $smarty->assign('VERSION', '2.0.2');
     }

     /* ===================== HELPER MAP ===================== */

     private function airlineDisplayName($code)
     {
          static $cache = array();
          if (isset($cache[$code])) return $cache[$code];
          if ($code === 'N/A' || $code === '') return $cache[$code] = 'Khác';

          // Map mã 3 ký tự (hệ thống/Sabre) về mã 2 ký tự (IATA) có trong bảng ec_airlines
          $mapCodes = array('VJA' => 'VJ', 'VNA' => 'VN', 'BBA' => 'QH', 'VTA' => 'VU', 'VNP' => 'BL');
          $searchCode = isset($mapCodes[$code]) ? $mapCodes[$code] : $code;

          global $db;
          $qCode = $db->quote($searchCode);
          $sql = "SELECT name FROM ec_airlines WHERE deleted = 0 AND (iata_code = '$qCode' OR icao_code = '$qCode') LIMIT 1";
          $row = $db->fetchByAssoc($db->query($sql));
          if (!empty($row['name'])) return $cache[$code] = $row['name'];

          if ($code === '0V' || $code === 'VTA') return $cache[$code] = 'VASCO';
          return $cache[$code] = 'Hãng khác';
     }

     private function airlineLabel($code)
     {
          if ($code === 'N/A' || $code === '') return 'Khác (N/A)';
          return $this->airlineDisplayName($code) . ' (' . $code . ')';
     }

     /**
      * Nhãn chiều bay từ tập direction phục vụ.
      */
     private function directionLabel(array $dirs)
     {
          $dirs = array_map('strval', $dirs);
          $h0 = in_array('0', $dirs, true);
          $h1 = in_array('1', $dirs, true);
          if ($h0 && $h1) return 'Lượt đi & về';
          if ($h0) return 'Lượt đi';
          if ($h1) return 'Lượt về';
          return '-';
     }

     /**
      * Map booking_id -> ['airline','dir','dir_airline'=>[dir=>code]] — GIỐNG bkagent::getAirlineMap:
      * hãng theo từng chiều ưu tiên field cấp booking (airline=đi, airline_inbound=về), fallback
      * chặng đầu itinerary (ORDER BY transit_order). Tránh lấy nhầm hãng chặng transit.
      */
     private function getAirlineMap(array $bkIds)
     {
          global $db;
          $map = array();
          $inList = array();
          foreach ($bkIds as $id) {
               if (!empty($id)) $inList[] = "'" . $db->quote($id) . "'";
          }
          if (empty($inList)) return $map;
          $inClause = implode(',', $inList);

          $bkAirline = array();
          $resBk = $db->query("SELECT id, airline, airline_inbound FROM ec_flight_bookings WHERE id IN ($inClause)");
          while ($row = $db->fetchByAssoc($resBk)) {
               $bkAirline[$row['id']] = array(
                    '0' => (isset($row['airline']) && $row['airline'] !== '') ? myNormalizeAirlineCode($row['airline']) : '',
                    '1' => (isset($row['airline_inbound']) && $row['airline_inbound'] !== '') ? myNormalizeAirlineCode($row['airline_inbound']) : '',
               );
          }

          $sql = "SELECT booking_id, direction, airline_code
                  FROM ec_booking_itineraries
                  WHERE deleted = 0 AND booking_id IN ($inClause)
                  ORDER BY booking_id, direction ASC, transit_order ASC, CAST(IFNULL(sabre_logs,0) AS UNSIGNED) ASC";
          $res = $db->query($sql);
          $itiFirst = array();
          while ($row = $db->fetchByAssoc($res)) {
               $bid = $row['booking_id'];
               if (!isset($itiFirst[$bid])) $itiFirst[$bid] = array();
               $d = (string) $row['direction'];
               $code = ($row['airline_code'] !== '') ? myNormalizeAirlineCode($row['airline_code']) : '';
               if ($d !== '' && $code !== '' && !isset($itiFirst[$bid][$d])) {
                    $itiFirst[$bid][$d] = $code;
               }
          }

          $allBids = array_unique(array_merge(array_keys($bkAirline), array_keys($itiFirst)));
          foreach ($allBids as $bid) {
               $iti = isset($itiFirst[$bid]) ? $itiFirst[$bid] : array();
               $bk  = isset($bkAirline[$bid]) ? $bkAirline[$bid] : array('0' => '', '1' => '');
               $dirAirline = array();
               foreach (array('0', '1') as $d) {
                    $present = isset($iti[$d]) || (empty($iti) && !empty($bk[$d]));
                    if (!$present) continue;
                    $code = ($bk[$d] !== '') ? $bk[$d] : (isset($iti[$d]) ? $iti[$d] : '');
                    if ($code !== '') $dirAirline[$d] = $code;
               }
               $has0 = isset($dirAirline['0']);
               $has1 = isset($dirAirline['1']);
               $first = $has0 ? $dirAirline['0'] : ($has1 ? $dirAirline['1'] : '');
               $map[$bid] = array(
                    'airline'     => ($first !== '') ? $first : 'N/A',
                    'dir'         => $this->directionLabel(array_keys($dirAirline)),
                    'dir_airline' => $dirAirline,
               );
          }
          return $map;
     }

     /**
      * Map booking_id -> [supplier_id => ['qty','price','bought','dirs'=>[dir=>true]]] từ ec_booking_details.
      * Dùng để TÁCH tiền booking theo NCC. supplier_id rỗng -> gom nhóm '' (Khác).
      */
     private function getSupplierSplitMap(array $bkIds)
     {
          global $db;
          $map = array();
          $inList = array();
          foreach ($bkIds as $id) {
               if (!empty($id)) $inList[] = "'" . $db->quote($id) . "'";
          }
          if (empty($inList)) return $map;

          $sql = "SELECT booking_id, IFNULL(supplier_id,'') AS supplier_id, direction,
                       SUM(IFNULL(quantity,0))          AS qty,
                       SUM(IFNULL(total_price,0))       AS price,
                       SUM(IFNULL(total_bought_price,0)) AS bought
                  FROM ec_booking_details
                  WHERE deleted = 0 AND booking_id IN (" . implode(',', $inList) . ")
                  GROUP BY booking_id, supplier_id, direction";
          $res = $db->query($sql);
          while ($row = $db->fetchByAssoc($res)) {
               $bid = $row['booking_id'];
               $sid = $row['supplier_id'];
               $d = (string) $row['direction'];

               if (!isset($map[$bid])) $map[$bid] = array();
               $map[$bid][] = array(
                    'sid'    => $sid,
                    'dir'    => $d,
                    'qty'    => (int) $row['qty'],
                    'price'  => (float) $row['price'],
                    'bought' => (float) $row['bought']
               );
          }
          return $map;
     }

     /**
      * Map receipt_voucher_id -> [ ['supplier_id','bought','sell'], ... ] cho tối đa 3 dòng NCC.
      */
     private function getReceiptSupplierLines(array $rvIds)
     {
          global $db;
          $map = array();
          $inList = array();
          foreach ($rvIds as $id) {
               if (!empty($id)) $inList[] = "'" . $db->quote($id) . "'";
          }
          if (empty($inList)) return $map;

          $sql = "SELECT id,
                       IFNULL(supplier_id,'')  AS s1, IFNULL(bought_amount,0)  AS b1, IFNULL(sell_amount,0)  AS se1, IFNULL(sup_direction,'')  AS d1,
                       IFNULL(supplier2_id,'') AS s2, IFNULL(bought_amount2,0) AS b2, IFNULL(sell_amount2,0) AS se2, IFNULL(sup_direction2,'') AS d2,
                       IFNULL(supplier3_id,'') AS s3, IFNULL(bought_amount3,0) AS b3, IFNULL(sell_amount3,0) AS se3, IFNULL(sup_direction3,'') AS d3
                  FROM ec_receipt_voucher
                  WHERE id IN (" . implode(',', $inList) . ")";
          $res = $db->query($sql);
          while ($row = $db->fetchByAssoc($res)) {
               $lines = array();
               foreach (array(array('s1', 'b1', 'se1', 'd1'), array('s2', 'b2', 'se2', 'd2'), array('s3', 'b3', 'se3', 'd3')) as $c) {
                    $sid = $row[$c[0]];
                    $bought = (float) $row[$c[1]];
                    $sell = (float) $row[$c[2]];
                    // giữ dòng có NCC, hoặc có phát sinh tiền (để không mất tổng)
                    if ($sid !== '' || $bought != 0 || $sell != 0) {
                         $lines[] = array('supplier_id' => $sid, 'bought' => $bought, 'sell' => $sell, 'direction' => $row[$c[3]]);
                    }
               }
               if (!empty($lines)) $map[$row['id']] = $lines;
          }
          return $map;
     }

     private function getRefundLines(array $hvIds)
     {
          global $db;
          $map = array();
          $inList = array();
          foreach ($hvIds as $id) {
               if (!empty($id)) $inList[] = "'" . $db->quote($id) . "'";
          }
          if (empty($inList)) return $map;

          $sql = "SELECT hoanve_id, IFNULL(airline_code,'') AS airline_code, IFNULL(chieubay,'') AS direction, IFNULL(nhacc_id,'') AS supplier_id, 
                         IFNULL(sotienkhach,0) AS sell, IFNULL(sotienhang,0) AS bought
                  FROM ec_chitiethoanve
                  WHERE deleted = 0 AND dahoan = 1 AND hoanve_id IN (" . implode(',', $inList) . ")";
          $res = $db->query($sql);
          while ($row = $db->fetchByAssoc($res)) {
               $hvid = $row['hoanve_id'];
               if (!isset($map[$hvid])) $map[$hvid] = array();
               $map[$hvid][] = array(
                    'airline_code' => $row['airline_code'],
                    'direction'    => $row['direction'],
                    'supplier_id'  => $row['supplier_id'],
                    'sell'         => (float) $row['sell'],
                    'bought'       => (float) $row['bought']
               );
          }
          return $map;
     }

     /**
      * Map booking_id -> nhãn loại vé (Nội địa/Quốc tế) từ ec_flight_bookings.ticket_type.
      */


     private function getTicketTypeMap(array $bkIds)
     {
          global $db, $app_list_strings;
          $map = array();
          $inList = array();
          foreach ($bkIds as $id) {
               if (!empty($id)) $inList[] = "'" . $db->quote($id) . "'";
          }
          if (empty($inList)) return $map;
          $sql = "SELECT id, ticket_type FROM ec_flight_bookings WHERE id IN (" . implode(',', $inList) . ")";
          $res = $db->query($sql);
          while ($row = $db->fetchByAssoc($res)) {
               $tt = $row['ticket_type'];
               $map[$row['id']] = isset($app_list_strings['booking_ticket_type_list'][$tt])
                    ? $app_list_strings['booking_ticket_type_list'][$tt] : '-';
          }
          return $map;
     }

     /**
      * Nhãn NCC theo id (memoize). '' -> "Khác (N/A)".
      */
     private function supplierLabel($sid)
     {
          static $cache = null;
          if ($cache === null) $cache = array();
          if ($sid === '' || $sid === null) return 'Khác (N/A)';

          if (isset($cache[$sid])) return $cache[$sid];

          global $db;
          $row = $db->fetchByAssoc($db->query(
               "SELECT name FROM accounts WHERE id = '" . $db->quote($sid) . "' AND deleted = 0 LIMIT 1"
          ));
          if (!$row) return $cache[$sid] = 'N/A';

          $name = !empty($row['name']) ? $row['name'] : 'N/A';

          return $cache[$sid] = trim($name);
     }

     /* ===================== TÁCH CHỨNG TỪ THEO NCC ===================== */

     /**
      * Hãng của phần NCC trong 1 booking: theo chiều mà NCC phục vụ (dir_airline), fallback hãng
      * đại diện của booking.
      */
     private function airlineForDirs(array $dirs, $airlineInfo)
     {
          if ($airlineInfo) {
               sort($dirs);
               foreach ($dirs as $d) {
                    if (isset($airlineInfo['dir_airline'][$d])) return $airlineInfo['dir_airline'][$d];
               }
               if (!empty($airlineInfo['airline'])) return $airlineInfo['airline'];
          }
          return 'N/A';
     }

     /**
      * Tách 1 dòng doanh thu (BK/PT/HV) từ calculateRevenueOfDate thành các phần theo NCC.
      * Mỗi phần: ['supplier_id','airline','direction','ve','dt','gm','ds'].
      * Tỷ lệ cộng lại = 1 nên tổng dt/gm/ds/ve LUÔN bằng giá trị gốc -> khớp bkagent.
      */
     private function splitBySupplier($row, $bkid, $ve, $dt, $gm, $ds, array $supplierSplit, array $rvLines, array $hvLines, $airlineInfo)
     {
          $ptype = $row['parent_type'];

          if ($ptype === 'EC_Receipt_Voucher') {
               $lines = isset($rvLines[$row['parent_id']]) ? $rvLines[$row['parent_id']] : array();
               if (empty($lines)) {
                    // PT không gắn NCC (VD loại 14 khách sạn) -> nhóm Khác
                    return array(array('supplier_id' => '', 'airline' => 'N/A', 'direction' => '-', 've' => 0, 'dt' => $dt, 'gm' => $gm, 'ds' => $ds));
               }
               $sumB = $sumS = 0;
               foreach ($lines as $ln) {
                    $sumB += abs($ln['bought']);
                    $sumS += abs($ln['sell']);
               }
               $n = count($lines);
               $parts = array();
               foreach ($lines as $ln) {
                    $ratioGm = ($sumB > 0) ? abs($ln['bought']) / $sumB : (1 / $n);
                    $ratioDt = ($sumS > 0) ? abs($ln['sell']) / $sumS : (($sumB > 0) ? abs($ln['bought']) / $sumB : (1 / $n));
                    $pdt = $dt * $ratioDt;
                    $pgm = $gm * $ratioGm;
                    $air = 'N/A';
                    $dir = '-';
                    if (!empty($bkid) && !empty($ln['supplier_id'])) {
                         // chiều đã chọn khi tạo phiếu (sup_direction) -> tường minh; trống -> auto
                         $lineDir = (isset($ln['direction']) && $ln['direction'] !== '') ? $ln['direction'] : null;
                         $r = resolveRVSupplierAirline($bkid, $ln['supplier_id'], $lineDir);
                         if (!empty($r['airline_code'])) $air = $r['airline_code'];
                         $useDir = ($r['direction'] !== null && $r['direction'] !== '') ? $r['direction'] : $lineDir;
                         if ($useDir !== null && $useDir !== '') {
                              $dirLabels = array('0' => 'Lượt đi', '1' => 'Lượt về');
                              $dir = isset($dirLabels[(string) $useDir]) ? $dirLabels[(string) $useDir] : '-';
                         }
                    }
                    if ($air === 'N/A' && $airlineInfo && !empty($airlineInfo['airline'])) $air = $airlineInfo['airline'];
                    $parts[] = array('supplier_id' => $ln['supplier_id'], 'airline' => $air, 'direction' => $dir, 've' => 0, 'dt' => $pdt, 'gm' => $pgm, 'ds' => $pdt - $pgm);
               }
               return $parts;
          }

          // BK & HV: tách theo NCC của booking (ec_booking_details)
          $split = (!empty($bkid) && isset($supplierSplit[$bkid])) ? $supplierSplit[$bkid] : array();
          if (empty($split)) {
               $air = ($airlineInfo && !empty($airlineInfo['airline'])) ? $airlineInfo['airline'] : 'N/A';
               $dir = ($ptype === 'EC_Flight_Bookings' && $airlineInfo) ? $airlineInfo['dir'] : '-';
               return array(array('supplier_id' => '', 'airline' => $air, 'direction' => $dir, 've' => $ve, 'dt' => $dt, 'gm' => $gm, 'ds' => $ds));
          }

          $sumPrice = $sumBought = $sumQty = 0;
          foreach ($split as $inf) {
               $sumPrice  += $inf['price'];
               $sumBought += $inf['bought'];
               $sumQty    += $inf['qty'];
          }
          $n = count($split);

          $parts = array();

          if ($ptype === 'EC_HoanVe') {
               $hvid = $row['parent_id'];
               $lines = isset($hvLines[$hvid]) ? $hvLines[$hvid] : array();
               if (!empty($lines)) {
                    $sumS = $sumB = 0;
                    foreach ($lines as $ln) {
                         $sumS += abs($ln['sell']);
                         $sumB += abs($ln['bought']);
                    }
                    $nHv = count($lines);

                    $maxB = -1;
                    $maxIdx = 0;
                    foreach ($lines as $idx => $ln) {
                         if (abs($ln['bought']) > $maxB) {
                              $maxB = abs($ln['bought']);
                              $maxIdx = $idx;
                         }
                    }

                    foreach ($lines as $idx => $ln) {
                         $ratioDt = ($sumS > 0) ? abs($ln['sell']) / $sumS : (($sumB > 0) ? abs($ln['bought']) / $sumB : (1 / $nHv));
                         $ratioGm = ($sumB > 0) ? abs($ln['bought']) / $sumB : (1 / $nHv);

                         $pdt = $dt * $ratioDt;
                         $pgm = $gm * $ratioGm;
                         $pve = ($idx === $maxIdx) ? $ve : 0;

                         $dirLabels = array('0' => 'Lượt đi', '1' => 'Lượt về');
                         $dir = isset($dirLabels[$ln['direction']]) ? $dirLabels[$ln['direction']] : '-';
                         
                         $air = 'N/A';
                         if (!empty($ln['airline_code'])) {
                             $air = $ln['airline_code'];
                         } elseif ($airlineInfo) {
                             if (isset($airlineInfo['dir_airline'][$ln['direction']])) {
                                 $air = $airlineInfo['dir_airline'][$ln['direction']];
                             } elseif (!empty($airlineInfo['airline'])) {
                                 $air = $airlineInfo['airline'];
                             }
                         }
                         $sid = !empty($ln['supplier_id']) ? $ln['supplier_id'] : '';

                         $parts[] = array('supplier_id' => $sid, 'airline' => $air, 'direction' => $dir, 've' => $pve, 'dt' => $pdt, 'gm' => $pgm, 'ds' => $pdt - $pgm);
                    }
               } else {
                    $bySid = array();
                    foreach ($split as $inf) {
                         $sid = $inf['sid'];
                         if (!isset($bySid[$sid])) $bySid[$sid] = array('qty' => 0, 'bought' => 0);
                         $bySid[$sid]['qty'] += $inf['qty'];
                         $bySid[$sid]['bought'] += $inf['bought'];
                    }
                    $maxSid = null;
                    $maxB = -1;
                    foreach ($bySid as $sid => $inf) {
                         if ($inf['bought'] > $maxB) {
                              $maxB = $inf['bought'];
                              $maxSid = $sid;
                         }
                    }
                    $nSid = count($bySid);
                    foreach ($bySid as $sid => $inf) {
                         $ratio = ($sumBought > 0) ? $inf['bought'] / $sumBought : (($sumQty > 0) ? $inf['qty'] / $sumQty : (1 / $nSid));
                         $pdt = $dt * $ratio;
                         $pgm = $gm * $ratio;
                         $pve = ($sid === $maxSid) ? $ve : 0;
                         $dir = '-';
                         $air = ($airlineInfo && !empty($airlineInfo['airline'])) ? $airlineInfo['airline'] : 'N/A';
                         $parts[] = array('supplier_id' => $sid, 'airline' => $air, 'direction' => $dir, 've' => $pve, 'dt' => $pdt, 'gm' => $pgm, 'ds' => $pdt - $pgm);
                    }
               }
          } else {
               foreach ($split as $inf) {
                    $sid = $inf['sid'];
                    $d = $inf['dir'];
                    $ratioDt = ($sumPrice > 0) ? $inf['price'] / $sumPrice : (($sumQty > 0) ? $inf['qty'] / $sumQty : (1 / $n));
                    $ratioGm = ($sumBought > 0) ? $inf['bought'] / $sumBought : (($sumQty > 0) ? $inf['qty'] / $sumQty : (1 / $n));
                    $pdt = $dt * $ratioDt;
                    $pgm = $gm * $ratioGm;
                    $pve = $inf['qty'];

                    $dirLabels = array('0' => 'Lượt đi', '1' => 'Lượt về');
                    $dir = isset($dirLabels[$d]) ? $dirLabels[$d] : '-';

                    $air = 'N/A';
                    if ($airlineInfo) {
                         if (isset($airlineInfo['dir_airline'][$d])) {
                              $air = $airlineInfo['dir_airline'][$d];
                         } elseif (!empty($airlineInfo['airline'])) {
                              $air = $airlineInfo['airline'];
                         }
                    }

                    $parts[] = array('supplier_id' => $sid, 'airline' => $air, 'direction' => $dir, 've' => $pve, 'dt' => $pdt, 'gm' => $pgm, 'ds' => $pdt - $pgm);
               }
          }
          return $parts;
     }

     /* ===================== BUILD REPORT ===================== */

     /**
      * Gom BK + PT + HV theo NCC -> 2 bảng HTML: summary (theo NCC) & detail (theo chứng từ).
      */
     function buildRevenueReport($from_date, $to_date, $mode)
     {
          $fromD = date('Y-m-d', strtotime($from_date));
          $toD   = date('Y-m-d', strtotime($to_date));

          $rev  = calculateRevenueOfDate($fromD, $toD, array());
          $rows = (!empty($rev['details']) && is_array($rev['details'])) ? $rev['details'] : array();

          $bkIds = array();
          $rvIds = array();
          $hvIds = array();
          foreach ($rows as $r) {
               if (!empty($r['booking_id'])) $bkIds[$r['booking_id']] = true;
               if ($r['parent_type'] === 'EC_Receipt_Voucher') $rvIds[$r['parent_id']] = true;
               if ($r['parent_type'] === 'EC_HoanVe') $hvIds[$r['parent_id']] = true;
          }
          $supplierSplit = $this->getSupplierSplitMap(array_keys($bkIds));
          $airlineMap    = $this->getAirlineMap(array_keys($bkIds));
          $rvLines       = $this->getReceiptSupplierLines(array_keys($rvIds));
          $hvLines       = $this->getRefundLines(array_keys($hvIds));
          $ticketTypeMap = $this->getTicketTypeMap(array_keys($bkIds));

          $bySupplier = array();
          $byAirline = array();
          $detailRows = array();
          $g_ve = $g_dt = $g_gm = $g_ds = 0;
          $g_bk_ids = array();

          foreach ($rows as $r) {
               $bkid  = isset($r['booking_id']) ? $r['booking_id'] : '';
               $ptype = $r['parent_type'];
               $ve = (int) $r['total_quantity'];
               $dt = (float) $r['subtotal_amount'];
               $gm = (float) $r['total_bought_price'];
               $ds = (float) $r['profit_amount'];
               $airlineInfo = (!empty($bkid) && isset($airlineMap[$bkid])) ? $airlineMap[$bkid] : null;
               $ticket_type_label = (!empty($bkid) && isset($ticketTypeMap[$bkid])) ? $ticketTypeMap[$bkid] : '-';

               $parts = $this->splitBySupplier($r, $bkid, $ve, $dt, $gm, $ds, $supplierSplit, $rvLines, $hvLines, $airlineInfo);

               $date_show = ($ptype === 'EC_Flight_Bookings')
                    ? (!empty($r['date_ticket_issue']) ? $r['date_ticket_issue'] : '')
                    : (!empty($r['voucher_date']) ? $r['voucher_date'] : '');

               foreach ($parts as $p) {
                    $sid = $p['supplier_id'];
                    $air = $p['airline'];

                    if ($mode === 'supplier') {
                         if (!isset($bySupplier[$sid])) {
                              $bySupplier[$sid] = array(
                                   'label'  => $this->supplierLabel($sid),
                                   'bk_ids' => array(),
                                   've' => 0,
                                   'dt' => 0,
                                   'gm' => 0,
                                   'ds' => 0,
                                   'airlines' => array(),
                              );
                         }
                         $bySupplier[$sid]['ve'] += $p['ve'];
                         $bySupplier[$sid]['dt'] += $p['dt'];
                         $bySupplier[$sid]['gm'] += $p['gm'];
                         $bySupplier[$sid]['ds'] += $p['ds'];
                         if ($ptype === 'EC_Flight_Bookings' && !empty($bkid)) {
                              $bySupplier[$sid]['bk_ids'][$bkid] = true;
                         }

                         if (!isset($bySupplier[$sid]['airlines'][$air])) {
                              $bySupplier[$sid]['airlines'][$air] = array('ve' => 0, 'dt' => 0, 'gm' => 0, 'ds' => 0, 'bk_ids' => array());
                         }
                         $bySupplier[$sid]['airlines'][$air]['ve'] += $p['ve'];
                         $bySupplier[$sid]['airlines'][$air]['dt'] += $p['dt'];
                         $bySupplier[$sid]['airlines'][$air]['gm'] += $p['gm'];
                         $bySupplier[$sid]['airlines'][$air]['ds'] += $p['ds'];
                         if ($ptype === 'EC_Flight_Bookings' && !empty($bkid)) {
                              $bySupplier[$sid]['airlines'][$air]['bk_ids'][$bkid] = true;
                         }
                    } else {
                         if (!isset($byAirline[$air])) {
                              $byAirline[$air] = array(
                                   'name'  => $this->airlineDisplayName($air),
                                   'bk_ids' => array(),
                                   've' => 0,
                                   'dt' => 0,
                                   'gm' => 0,
                                   'ds' => 0,
                              );
                         }
                         $byAirline[$air]['ve'] += $p['ve'];
                         $byAirline[$air]['dt'] += $p['dt'];
                         $byAirline[$air]['gm'] += $p['gm'];
                         $byAirline[$air]['ds'] += $p['ds'];
                         if ($ptype === 'EC_Flight_Bookings' && !empty($bkid)) {
                              $byAirline[$air]['bk_ids'][$bkid] = true;
                         }
                    }

                    $g_ve += $p['ve'];
                    $g_dt += $p['dt'];
                    $g_gm += $p['gm'];
                    $g_ds += $p['ds'];

                    if ($mode === 'supplier') {
                         $detailRows[] = array(
                              'supplier_id' => $sid,
                              'supplier'    => $this->supplierLabel($sid),
                              'airline'     => $air,
                              'direction'   => $p['direction'],
                              'ticket_type' => $ticket_type_label,
                              'parent_type' => $ptype,
                              'parent_id'   => $r['parent_id'],
                              'name'        => $r['parent_name'],
                              've' => $p['ve'],
                              'dt' => $p['dt'],
                              'gm' => $p['gm'],
                              'ds' => $p['ds'],
                              'date_show' => $date_show,
                         );
                    } else {
                         $detailRows[] = array(
                              'airline'     => $air,
                              'direction'   => $p['direction'],
                              'ticket_type' => $ticket_type_label,
                              'parent_type' => $ptype,
                              'parent_id'   => $r['parent_id'],
                              'name'        => $r['parent_name'],
                              've' => $p['ve'],
                              'dt' => $p['dt'],
                              'gm' => $p['gm'],
                              'ds' => $p['ds'],
                              'date_show' => $date_show,
                         );
                    }
               }
               if ($ptype === 'EC_Flight_Bookings' && !empty($bkid)) $g_bk_ids[$bkid] = true;
          }
          if ($mode === 'supplier') {
               // ===== Bảng tổng hợp theo NCC =====
               uasort($bySupplier, function ($a, $b) {
                    if ($b['gm'] != $a['gm']) return ($b['gm'] <=> $a['gm']);
                    return $b['dt'] <=> $a['dt'];
               });

               $summary = '
                 <tr class="supplier-row cursor-pointer bg-label-secondary" data-supplier="ALL" title="Bấm để xem tất cả">
                     <td class="text-center text-decoration-underline" colspan="2"><b>Tổng</b></td>
                     <td class="text-center"><b>' . format_number(count($g_bk_ids)) . '</b></td>
                     <td class="text-center"><b>' . format_number($g_ve) . '</b></td>
                     <td class="text-center"><b>' . format_number($g_dt) . '</b></td>
                     <td class="text-center"><b>' . format_number($g_gm) . '</b></td>
                     <td class="text-center"><b>' . format_number($g_ds) . '</b></td>
                 </tr>
               ';
               $i = 0;
               foreach ($bySupplier as $sid => $s) {
                    $summary .= '
                     <tr class="supplier-row cursor-pointer" data-supplier="' . $sid . '" title="Bấm để lọc chứng từ của NCC này">
                         <td class="text-center fw-semibold">' . ($i + 1) . '</td>
                         <td class="text-center fw-semibold text-decoration-underline">' . htmlspecialchars($s['label']) . '</td>
                         <td class="text-center fw-semibold">' . format_number(count($s['bk_ids'])) . '</td>
                         <td class="text-center fw-semibold">' . format_number($s['ve']) . '</td>
                         <td class="text-center fw-semibold">' . format_number($s['dt']) . '</td>
                         <td class="text-center fw-semibold">' . format_number($s['gm']) . '</td>
                         <td class="text-center fw-semibold">' . format_number($s['ds']) . '</td>
                     </tr>
                    ';
                    if (count($s['airlines']) > 0) {
                         uasort($s['airlines'], function ($a, $b) {
                              if ($b['ve'] != $a['ve']) return $b['ve'] <=> $a['ve'];
                              return $b['dt'] <=> $a['dt'];
                         });
                         foreach ($s['airlines'] as $airCode => $airData) {
                              $summary .= '
                              <tr class="supplier-row-airline cursor-pointer" data-supplier="' . $sid . '" data-airline="' . $airCode . '" title="Bấm để lọc chứng từ của hãng này">
                                  <td></td>
                                  <td class="text-start ps-4">&#8618; ' . htmlspecialchars($this->airlineLabel($airCode)) . '</td>
                                  <td class="text-center">' . format_number(count($airData['bk_ids'])) . '</td>
                                  <td class="text-center">' . format_number($airData['ve']) . '</td>
                                  <td class="text-center">' . format_number($airData['dt']) . '</td>
                                  <td class="text-center">' . format_number($airData['gm']) . '</td>
                                  <td class="text-center">' . format_number($airData['ds']) . '</td>
                              </tr>
                              ';
                         }
                    }
                    $i++;
               }
               if ($i === 0) {
                    $summary .= '<tr><td colspan="7" class="text-center text-muted">Không có dữ liệu trong kỳ.</td></tr>';
               }
          } else {
               // ===== Bảng tổng hợp theo Hãng =====
               uasort($byAirline, function ($a, $b) {
                    if ($b['ve'] !== $a['ve']) return $b['ve'] <=> $a['ve'];
                    return $b['dt'] <=> $a['dt'];
               });

               $summary = '
                   <tr class="airline-row cursor-pointer bg-label-secondary" data-airline="ALL" title="Bấm để xem tất cả">
                       <td></td>
                       <td class="text-center text-decoration-underline"><b>Tổng</b></td>
                       <td class="text-center"><b>' . format_number(count($g_bk_ids)) . '</b></td>
                       <td class="text-center"><b>' . format_number($g_ve) . '</b></td>
                       <td class="text-center"><b>' . format_number($g_dt) . '</b></td>
                       <td class="text-center"><b>' . format_number($g_gm) . '</b></td>
                       <td class="text-center"><b>' . format_number($g_ds) . '</b></td>
                   </tr>
               ';
               $i = 0;
               foreach ($byAirline as $code => $a) {
                    $summary .= '
                       <tr class="airline-row cursor-pointer" data-airline="' . $code . '" title="Bấm để lọc vé của hãng này">
                           <td class="text-center">' . ($i + 1) . '</td>
                           <td class="text-center fw-semibold text-decoration-underline">' . $a['name'] . ' (' . $code . ')</td>
                           <td class="text-center">' . format_number(count($a['bk_ids'])) . '</td>
                           <td class="text-center">' . format_number($a['ve']) . '</td>
                           <td class="text-center">' . format_number($a['dt']) . '</td>
                           <td class="text-center">' . format_number($a['gm']) . '</td>
                           <td class="text-center">' . format_number($a['ds']) . '</td>
                       </tr>
                   ';
                    $i++;
               }
               if ($i === 0) {
                    $summary .= '<tr><td colspan="7" class="text-center text-muted">Không có dữ liệu trong kỳ.</td></tr>';
               }
          }

          // ===== Bảng chi tiết =====
          usort($detailRows, function ($a, $b) {
               if ($b['ve'] != $a['ve']) return ($b['ve'] <=> $a['ve']);
               return $b['dt'] <=> $a['dt'];
          });

          if ($mode === 'supplier') {
               $detail = '
                 <tr id="supplier_detail_total_row" class="bg-label-secondary">
                     <td></td>
                     <td class="text-center"><b>Tổng</b></td>
                     <td></td>
                     <td></td>
                     <td></td>
                     <td></td>
                     <td class="text-center"><b id="total_filtered_ve">' . format_number($g_ve) . '</b></td>
                     <td class="text-end"><b id="total_filtered_dt">' . format_number($g_dt) . '</b></td>
                     <td class="text-end"><b id="total_filtered_gm">' . format_number($g_gm) . '</b></td>
                     <td class="text-end"><b id="total_filtered_ds">' . format_number($g_ds) . '</b></td>
                     <td></td>
                 </tr>
               ';
          } else {
               $detail = '
                 <tr id="booking_list_total_row" class="bg-label-secondary">
                     <td></td>
                     <td class="text-center"><b>Tổng</b></td>
                     <td></td>
                     <td></td>
                     <td></td>
                     <td class="text-center"><b id="total_filtered_ticket_qty">' . format_number($g_ve) . '</b></td>
                     <td class="text-end"><b id="total_filtered_dt">' . format_number($g_dt) . '</b></td>
                     <td class="text-end"><b id="total_filtered_gm">' . format_number($g_gm) . '</b></td>
                     <td class="text-end"><b id="total_filtered_ds">' . format_number($g_ds) . '</b></td>
                     <td></td>
                 </tr>
               ';
          }

          $d = 0;
          foreach ($detailRows as $r) {
               $ve_cell = ($r['ve'] != 0) ? format_number($r['ve']) : '-';
               $ticket_type_html = $r['ticket_type'];
               if ($r['ticket_type'] === 'Quốc tế') {
                    $ticket_type_html = '<span class="badge bg-label-success">' . $r['ticket_type'] . '</span>';
               } elseif ($r['ticket_type'] === 'Nội địa') {
                    $ticket_type_html = '<span class="badge bg-label-dark">' . $r['ticket_type'] . '</span>';
               }

               $row_class = $mode === 'supplier' ? 'supplier-detail-row' : 'booking-row';
               if ($r['parent_type'] === 'EC_Receipt_Voucher') {
                    $row_class .= ' bg-label-info';
               } elseif ($r['parent_type'] === 'EC_HoanVe') {
                    $row_class .= ' bg-label-danger';
               }

               if ($mode === 'supplier') {
                    $detail .= '
                     <tr class="' . $row_class . '" data-supplier="' . $r['supplier_id'] . '" data-airline="' . $r['airline'] . '" data-qty="' . (int)$r['ve'] . '" data-dt="' . round($r['dt']) . '" data-gm="' . round($r['gm']) . '" data-ds="' . round($r['ds']) . '">
                         <td class="text-center detail-stt">' . ($d + 1) . '</td>
                         <td class="text-center"><a href="index.php?module=' . $r['parent_type'] . '&action=DetailView&record=' . $r['parent_id'] . '" target="_blank">' . htmlspecialchars($r['name']) . '</a></td>
                         <td class="text-center">' . htmlspecialchars($r['supplier']) . '</td>
                         <td class="text-center">' . htmlspecialchars($this->airlineLabel($r['airline'])) . '</td>
                         <td class="text-center">' . $r['direction'] . '</td>
                         <td class="text-center">' . $ticket_type_html . '</td>
                         <td class="text-center">' . $ve_cell . '</td>
                         <td class="text-end">' . format_number($r['dt']) . '</td>
                         <td class="text-end">' . format_number($r['gm']) . '</td>
                         <td class="text-end">' . format_number($r['ds']) . '</td>
                         <td class="text-center">' . $r['date_show'] . '</td>
                     </tr>
                    ';
               } else {
                    $airline_disp = $this->airlineDisplayName($r['airline']) . ' (' . $r['airline'] . ')';
                    $detail .= '
                     <tr class="' . $row_class . '" data-airline="' . $r['airline'] . '" data-qty="' . (int)$r['ve'] . '" data-dt="' . round($r['dt']) . '" data-gm="' . round($r['gm']) . '" data-amount="' . round($r['ds']) . '">
                         <td class="center stt-cell">' . ($d + 1) . '</td>
                         <td class="center"><a href="index.php?module=' . $r['parent_type'] . '&action=DetailView&record=' . $r['parent_id'] . '" target="_blank">' . htmlspecialchars($r['name']) . '</a></td>
                         <td class="center">' . $airline_disp . '</td>
                         <td class="center">' . $r['direction'] . '</td>
                         <td class="center">' . $ticket_type_html . '</td>
                         <td class="center">' . $ve_cell . '</td>
                         <td class="text-end">' . format_number($r['dt']) . '</td>
                         <td class="text-end">' . format_number($r['gm']) . '</td>
                         <td class="text-end">' . format_number($r['ds']) . '</td>
                         <td class="center">' . $r['date_show'] . '</td>
                     </tr>
                    ';
               }
               $d++;
          }
          if ($d === 0) {
               $colCount = $mode === 'supplier' ? 11 : 10;
               $detail .= '<tr><td colspan="' . $colCount . '" class="text-center text-muted">Không có dữ liệu trong kỳ.</td></tr>';
          }

          return array('summary' => $summary, 'detail' => $detail);
     }
}
