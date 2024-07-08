<?php
class ReadNum {
	public $mangso = array('không', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín');

	function dochangchuc($so, $daydu) {
		$chuoi = "";
		$chuc = floor($so / 10);
		$donvi = $so % 10;
		if ($chuc > 1) {
			$chuoi = " " . $this->mangso[$chuc] . " mươi";
			if ($donvi == 1) {
				$chuoi .= " mốt";
			}
		} else if ($chuc == 1) {
			$chuoi = " mười";
			if ($donvi == 1) {
				$chuoi .= " một";
			}
		} else if ($daydu && $donvi > 0) {
			$chuoi = " lẻ";
		}
		if ($donvi == 5 && $chuc > 1) {
			$chuoi .= " lăm";
		} else if ($donvi > 1 || ($donvi == 1 && $chuc == 0)) {
			$chuoi .= " " . $this->mangso[$donvi];
		}
		return $chuoi;
	}

	function docblock($so, $daydu) {
		$chuoi = "";
		$tram = floor($so / 100);
		$so = $so % 100;
		if ($daydu || $tram > 0) {
			$chuoi = " " . $this->mangso[$tram] . " trăm";
			$chuoi .= $this->dochangchuc($so, true);
		} else {
			$chuoi = $this->dochangchuc($so, false);
		}
		return $chuoi;
	}

	function dochangtrieu($so, $daydu) {
		$chuoi = "";
		$trieu = floor($so / 1000000);
		$so = $so % 1000000;
		if ($trieu > 0) {
			$chuoi = $this->docblock($trieu, $daydu) . " triệu";
			$daydu = true;
		}
		$nghin = floor($so / 1000);
		$so = $so % 1000;
		if ($nghin > 0) {
			$chuoi .= $this->docblock($nghin, $daydu) . " nghìn";
			$daydu = true;
		}
		if ($so > 0) {
			$chuoi .= $this->docblock($so, $daydu);
		}
		return $chuoi;
	}

	function docso($so) {
		if ($so == 0) return $this->mangso[0];
		$chuoi = "";
		$hauto = "";
		do {
			$ty = $so % 1000000000;
			$so = floor($so / 1000000000);
			if ($so > 0) {
				$chuoi = $this->dochangtrieu($ty, true) . $hauto . $chuoi;
			} else {
				$chuoi = $this->dochangtrieu($ty, false) . $hauto . $chuoi;
			}
			$hauto = " tỷ";
		} while ($so > 0);
		return ucfirst(trim($chuoi));
	}
}
