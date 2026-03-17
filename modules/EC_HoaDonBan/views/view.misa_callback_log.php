<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class Viewmisa_callback_log extends SugarView
{
    public function display()
    {
        $smarty = new Sugar_Smarty();
        $this->populateContent($smarty);
        $smarty->display('modules/' . $this->bean->object_name . '/tpls/misa_callback_log.tpl');
    }

    public function populateContent($smarty)
    {
        global $app_list_strings;

        $baseDir = 'secure_sessions/misa_logs';

        // ============================================================
        // Lấy tháng/năm từ request, mặc định là tháng hiện tại
        // ============================================================
        $selectedYear  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
        $selectedMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

        // Giới hạn hợp lệ
        $selectedYear  = max(2020, min((int)date('Y'), $selectedYear));
        $selectedMonth = max(1,    min(12, $selectedMonth));

        // ============================================================
        // Quét thư mục để build danh sách năm có dữ liệu
        // ============================================================
        $availableYears = [];
        if (is_dir($baseDir)) {
            foreach (scandir($baseDir) as $entry) {
                if (is_numeric($entry) && is_dir("$baseDir/$entry")) {
                    $availableYears[] = (int)$entry;
                }
            }
            sort($availableYears);
        }

        // Nếu không có năm nào, fallback năm hiện tại
        if (empty($availableYears)) {
            $availableYears = [(int)date('Y')];
        }

        // ============================================================
        // Quét thư mục tháng của năm đã chọn
        // ============================================================
        $availableMonths = [];
        $yearDir = "$baseDir/$selectedYear";
        if (is_dir($yearDir)) {
            foreach (scandir($yearDir) as $entry) {
                if (is_numeric($entry) && is_dir("$yearDir/$entry")) {
                    $availableMonths[] = (int)$entry;
                }
            }
            sort($availableMonths);
        }

        // ============================================================
        // Đọc nội dung file log
        // ============================================================
        $logFile    = sprintf('%s/%d/%02d/amis_callback.log', $baseDir, $selectedYear, $selectedMonth);
        $errorFile  = sprintf('%s/%d/%02d/amis_callback_error.log', $baseDir, $selectedYear, $selectedMonth);

        $logLines   = $this->readLogFile($logFile);
        $errorLines = $this->readLogFile($errorFile);

        // ============================================================
        // Assign Smarty
        // ============================================================
        $smarty->assign('selectedYear',    $selectedYear);
        $smarty->assign('selectedMonth',   $selectedMonth);
        $smarty->assign('availableYears',  $availableYears);
        $smarty->assign('availableMonths', $availableMonths);
        $smarty->assign('logLines',        $logLines);
        $smarty->assign('errorLines',      $errorLines);
        $smarty->assign('logFile',         $logFile);
        $smarty->assign('errorFile',       $errorFile);
        $smarty->assign('hasLog',          !empty($logLines));
        $smarty->assign('hasError',        !empty($errorLines));
        $smarty->assign('months',          $this->getMonthLabels());
    }

    /**
     * Đọc file log, trả về mảng các dòng (mới nhất lên đầu)
     */
    private function readLogFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        if ($content === false || trim($content) === '') {
            return [];
        }

        $lines = array_filter(
            array_map('trim', explode(PHP_EOL, $content)),
            fn($l) => $l !== ''
        );

        // Mới nhất lên đầu
        return array_reverse(array_values($lines));
    }

    /**
     * Nhãn tháng tiếng Việt
     */
    private function getMonthLabels(): array
    {
        return [
            1  => 'Tháng 1',
            2  => 'Tháng 2',
            3  => 'Tháng 3',
            4  => 'Tháng 4',
            5  => 'Tháng 5',
            6  => 'Tháng 6',
            7  => 'Tháng 7',
            8  => 'Tháng 8',
            9  => 'Tháng 9',
            10 => 'Tháng 10',
            11 => 'Tháng 11',
            12 => 'Tháng 12',
        ];
    }
}