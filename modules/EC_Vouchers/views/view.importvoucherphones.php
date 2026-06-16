<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class Viewimportvoucherphones extends SugarView
{
	public function display()
	{
		@ob_clean();
		header('Content-Type: application/json; charset=utf-8');

		try {
			echo json_encode($this->importPhones(), JSON_UNESCAPED_UNICODE);
		} catch (Throwable $e) {
			echo json_encode([
				'success' => false,
				'message' => $e->getMessage(),
				'phones' => [],
			], JSON_UNESCAPED_UNICODE);
		}

		exit();
	}

	public function preDisplay() {}

	private function importPhones()
	{
		if (empty($_FILES['voucher_phone_file']['tmp_name'])) {
			return $this->error('Vui lòng chọn file Excel.');
		}

		$file = $_FILES['voucher_phone_file'];
		$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

		if (!in_array($extension, ['xls', 'xlsx', 'csv'])) {
			return $this->error('Chỉ hỗ trợ file xls, xlsx hoặc csv.');
		}

		$values = $extension === 'csv'
			? $this->readCsvValues($file['tmp_name'])
			: $this->readExcelValues($file['tmp_name']);

		$phones = $this->extractPhones($values);

		return [
			'success' => true,
			'message' => 'Import thành công ' . count($phones) . ' số điện thoại.',
			'phones' => $phones,
			'count' => count($phones),
		];
	}

	private function readExcelValues($path)
	{
		$spreadsheet = IOFactory::load($path);
		$values = [];

		foreach ($spreadsheet->getAllSheets() as $sheet) {
			foreach ($sheet->getRowIterator() as $row) {
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(true);

				foreach ($cellIterator as $cell) {
					$value = $cell->getFormattedValue();
					if ($value !== null && trim((string)$value) !== '') {
						$values[] = (string)$value;
					}
				}
			}
		}

		return $values;
	}

	private function readCsvValues($path)
	{
		$values = [];
		$handle = fopen($path, 'r');

		if (!$handle) {
			return $values;
		}

		while (($row = fgetcsv($handle)) !== false) {
			foreach ($row as $cell) {
				if ($cell !== null && trim((string)$cell) !== '') {
					$values[] = (string)$cell;
				}
			}
		}

		fclose($handle);
		return $values;
	}

	private function extractPhones($values)
	{
		$phones = [];

		foreach ($values as $value) {
			$parts = preg_split('/[\s,;]+/', (string)$value);
			foreach ($parts as $part) {
				$phone = $this->normalizePhone($part);
				if ($phone !== '') {
					$phones[$phone] = $phone;
				}
			}
		}

		return array_values($phones);
	}

	private function normalizePhone($phone)
	{
		$phone = preg_replace('/\D/', '', (string)$phone);

		if (strpos($phone, '0084') === 0) {
			$phone = '0' . substr($phone, 4);
		} elseif (strpos($phone, '84') === 0 && strlen($phone) >= 11) {
			$phone = '0' . substr($phone, 2);
		} elseif (strlen($phone) === 9) {
			$phone = '0' . $phone;
		}

		if (strlen($phone) < 10 || strlen($phone) > 11) {
			return '';
		}

		return $phone;
	}

	private function error($message)
	{
		return [
			'success' => false,
			'message' => $message,
			'phones' => [],
		];
	}
}
