<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once("include/Sugar_Smarty.php");
require_once('custom/include/helpers/api/WinInvoice.php');
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Viewsignedinvoice extends SugarView
{
    function display()
    {
        if (ACLController::checkAccess('EC_HoaDonBan', 'list', true)) {
            $smarty = new Sugar_Smarty();

            if (isset($_POST['from_date']) && !empty($_POST['from_date'])) {
                $from_date = $_POST['from_date'];
            } else {
                $from_date = date('d-m-Y');
            }
            if (isset($_POST['to_date']) && !empty($_POST['to_date'])) {
                $to_date = $_POST['to_date'];
            } else {
                $to_date = date('d-m-Y');
            }

            if (isset($_POST['btnExportInvoice'])) {
                $this->exportExcelInvoice($_POST);
            }

            // $list_invoice = $this->getSignedInvoiceList($from_date, $to_date);
            $list_invoice = $this->getRecordInvoice($from_date, $to_date);
            $smarty->assign('SIGNED_INVOICE', $list_invoice);
            $smarty->assign('FROM_DATE_VALUE', date('d-m-Y', strtotime($from_date)));
            $smarty->assign('TO_DATE_VALUE', date('d-m-Y', strtotime($to_date)));
            $smarty->display('modules/EC_HoaDonBan/tpls/signedinvoice.tpl');
        } else {
            header("Location: index.php?module=EC_HoaDonBan&action=Error&error_string=" . urlencode("Bạn không được quyền truy cập vào mục này"));
            exit();
        }
    }

    function getSignedInvoiceList($from_date, $to_date)
    {
        $from_date = date('Y/m/d', strtotime($from_date));
        $to_date = date('Y/m/d', strtotime($to_date));
        $html = '';
        $total_amount_bought = 0;
        $total_amount_vat = 0;
        $total_amount_total = 0;

        $winInv = new WinInvoice();
        $list_invoice_signed = json_decode($winInv->get_list($from_date, $to_date), true);

        foreach ($list_invoice_signed as $invoice) {
            if ($invoice['buyerName'] == "") {
                $contact = $invoice['buyerCompany'];
            } else {
                $contact = $invoice['buyerName'];
            }

            $html .= '<tr>';
            $html .= '<td align="center">
                              <input type="checkbox" name="sochungtu_id[]" value="' . $invoice['invRef'] . '" />
                         </td>';
            $html .= '<td class="text-center ngayhoadon">' . date('d-m-Y', strtotime($invoice['invRefDate'])) . '</td>';
            $html .= '<td class="text-center ngayky">' . date('d-m-Y', strtotime($invoice['invDate'])) . '</td>';
            $html .= '<td class="text-center sochungtu"><a target="_blank" href="https://timchuyenbay.com/tra-cuu?invRef=' . $invoice['invRef'] . '">' . $invoice['invRef'] . '</a></td>';
            $html .= '<td class="text-center sohoadon">' . $invoice['invNumber'] . '</td>';
            $html .= '<td class="text-start name_customer">' . $contact . '</td>';
            $html .= '<td class="text-center invSubTotal">' . format_number($invoice['invSubTotal']) . '</td>';
            $html .= '<td class="text-center invVatAmount">' . format_number($invoice['invVatAmount']) . '</td>';
            $html .= '<td class="text-center invTotalAmount">' . format_number($invoice['invTotalAmount']) . '</td>';
            $html .= '</tr>';

            $total_amount_bought += $invoice['invSubTotal'];
            $total_amount_vat += $invoice['invVatAmount'];
            $total_amount_total += $invoice['invTotalAmount'];
        }

        $html .= '
               <tr class="footer-tr">
                    <td colspan="6"></td>
                    <td class="invSubTotal">' . format_number($total_amount_bought) . '</td>
                    <td class="invVatAmount">' . format_number($total_amount_vat) . '</td>
                    <td class="invTotalAmount">' . format_number($total_amount_total) . '</td>
               </tr>';

        return $html;
    }

    function getRecordInvoice($from_date, $to_date)
    {
        $sql = 'SELECT id, name, ngayhoadon, sohoadon, tinhtrang, diachi, masothue, tongthanhtoan, company_unit, tencongty, lienhe
               FROM ec_hoadonban
               WHERE tinhtrang IN (1, 2)
                    AND DATE_FORMAT(DATE_ADD(ngayhoadon, INTERVAL 7 HOUR), "%Y-%m-%d")
                         BETWEEN "' . date('Y-m-d', strtotime($from_date)) . '" 
                         AND "' . date('Y-m-d', strtotime($to_date)) . '"
                    AND deleted = 0
               ORDER BY date_entered DESC';

        $res = $this->bean->db->query($sql);
        $html = '';
        $i = $total_thanhtoan = 0;
        while ($row = $this->bean->db->fetchByAssoc($res)) {
            if ($row['lienhe'] == "") {
                $contact = $row['tencongty'];
            } else {
                $contact = $row['lienhe'];
            }

            $html .= '<tr>
                    <td align="center">
                         <input type="checkbox" name="sochungtu_id[]" value="' . $row['name'] . '" />
                    </td>
                    <td class="text-center ngayhoadon">' . date('d-m-Y', strtotime($row['ngayhoadon'])) . '</td>
                    <td class="text-center sochungtu"><a target="_blank" href="https://timchuyenbay.com/tra-cuu?invRef=' . $row['name'] . '">' . $row['name'] . '</a></td>
                    <td class="text-center tinhtrang">' . $GLOBALS['app_list_strings']['invoice_status_list'][$row['tinhtrang']] . '</td>
                    <td class="text-center sohoadon">' . $row['sohoadon'] . '</td>
                    <td class="text-start name_customer">' . $contact . '</td>
                    <td class="text-start diachi">' . $row['diachi'] . '</td>
                    <td class="text-start mst">' . $row['masothue'] . '</td>
                    <td class="text-center thanhtien">' . format_number($row['tongthanhtoan']) . '</td>
                    <td class="text-center company_unit">' . $row['company_unit'] . '</td>
               </tr>';

            $total_thanhtoan += $row['tongthanhtoan'];
            $i++;
        }
        $html .= '
               <tr class="footer-tr">
                    <td colspan="8"></td>
                    <td class="invSubTotal">' . format_number($total_thanhtoan) . '</td>
                    <td></td>
               </tr>';


        return $html;
    }

    function exportExcelInvoice()
    {
        global $current_user;
        $winInv = new WinInvoice();

        // $objReader     = new Spreadsheet();
        // $objReader     = IOFactory::load('custom/templates_export/HD_BANHANG_TEMPLATE.xls');
        $hd_count      = count($_POST['sochungtu_id']);

        $filesToDownload = [];
        for ($i = 0; $i < $hd_count; $i++) {
            $objReader = IOFactory::load('custom/templates_export/HD_BANHANG_TEMPLATE.xls');
            $sheet = $objReader->getActiveSheet();

            $sochungtu_id = $_POST['sochungtu_id'][$i];
            $invRes = json_decode($winInv->get($sochungtu_id), true);

            if (!isset($invRes['error']) || $invRes['error'] === 1) continue;
            $invData = $invRes['data'][0] ?? [];

            // Ngày hạch toán
            $date_accounting = date('d-m-Y', strtotime($invData['invRefDate']));
            // Ngày hóa đơn
            $date_invoice = date('d-m-Y', strtotime($invData['invDate']));
            // Số hóa đơn
            $number_invoice = (int)$invData['invNumber'];
            // Mã khách hàng
            $buyerCode = $invData['buyerCode'];
            // Tên khách hàng
            $name_customer = empty($invData['buyerName']) ? $invData['buyerCompany'] : $invData['buyerName'];
            // Địa chỉ
            $buyerAddress = $invData['buyerAddress'];
            // MST
            $buyerTax = $invData['buyerTax'];

            foreach (($invData['items'] ?? []) as $index => $item) {
                $sheet->setCellValue("A" . ($index + 2), '');
                $sheet->setCellValue("B" . ($index + 2), '');
                $sheet->setCellValue("C" . ($index + 2), '');
                $sheet->setCellValue("D" . ($index + 2), 0);
                $sheet->setCellValue("E" . ($index + 2), '');
                $sheet->setCellValue("F" . ($index + 2), '');
                $sheet->setCellValue("G" . ($index + 2), '');
                $sheet->setCellValue("H" . ($index + 2), $date_accounting);
                $sheet->setCellValue("I" . ($index + 2), $date_invoice);
                $sheet->setCellValue("J" . ($index + 2), $sochungtu_id);
                $sheet->setCellValue("K" . ($index + 2), '');
                $sheet->setCellValue("L" . ($index + 2), '');
                $sheet->setCellValue("M" . ($index + 2), $number_invoice);
                $sheet->setCellValue("N" . ($index + 2), '');
                $sheet->setCellValue("O" . ($index + 2), $buyerCode);
                $sheet->setCellValue("P" . ($index + 2), $name_customer);
                $sheet->setCellValue("Q" . ($index + 2), $buyerAddress);
                $sheet->setCellValue("R" . ($index + 2), $buyerTax);
                $sheet->setCellValue("S" . ($index + 2), '');
                $sheet->setCellValue("T" . ($index + 2), '');
                $sheet->setCellValue("U" . ($index + 2), '');
                $sheet->setCellValue("V" . ($index + 2), $item['itemCode']);
                $sheet->setCellValue("W" . ($index + 2), $item['itemName']);
                $sheet->setCellValue("X" . ($index + 2), '');
                $sheet->setCellValue("Y" . ($index + 2), '131');
                $sheet->setCellValue("Z" . ($index + 2), '5111');
                $sheet->setCellValue("AA" . ($index + 2), $item['itemUnit']);
                $sheet->setCellValue("AB" . ($index + 2), (int)$item['itemQuantity']);
                $sheet->setCellValue("AC" . ($index + 2), $this->formatCurrencyInvoice($item['itemPrice']));
                $sheet->setCellValue("AD" . ($index + 2), $this->formatCurrencyInvoice($item['itemPrice']));
                $sheet->setCellValue("AE" . ($index + 2), $this->formatCurrencyInvoice($item['itemAmountNoVat']));
                $sheet->setCellValue("AP" . ($index + 2), '33311');
            }

            $outputFilePath = 'custom/templates_export/' . $sochungtu_id . '.xlsx';
            $writer = IOFactory::createWriter($objReader, 'Xlsx');
            $writer->save($outputFilePath);
            $filesToDownload[] = $outputFilePath;
        }

        if (count($filesToDownload) == 1) {
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Content-Type: application/force-download");
            header("Content-Type: application/octet-stream");
            header("Content-Type: application/download");;
            header("Content-Disposition: attachment;filename=" . $sochungtu_id . "_" . time() . ".xlsx");
            header("Content-Transfer-Encoding: binary ");

            ob_end_clean();
            ob_start();
            $writer->save('php://output');

            // Xóa file tạm
            foreach ($filesToDownload as $file) {
                unlink($file);
            }
            exit;
        } else {
            // Tạo zip file chứa các file excel
            $zipFile = 'custom/templates_export/output.zip';
            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
                foreach ($filesToDownload as $file) {
                    $zip->addFile($file, basename($file));
                }
                $zip->close();
            }

            // Tải về zip file
            header("Pragma: public");
            header("Content-Type: application/zip");
            header("Content-disposition: attachment;filename=XUATHD_" . date('dmY') . ".zip");
            header("Content-Length: " . filesize($zipFile));
            ob_clean();
            flush();
            readfile($zipFile);

            // Xóa các file tạm sau khi đã tạo zip
            unlink($zipFile);
            foreach ($filesToDownload as $file) unlink($file);
            exit;
        }
    }


    public function formatCurrencyInvoice($amount)
    {
        $formattedNumber = number_format(floatval($amount), 0, '', '');
        return $formattedNumber;
    }
}
