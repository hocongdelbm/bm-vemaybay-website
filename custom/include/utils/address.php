<?php 
/**
 * Get list of cities in Vietnam
 * 
 * @return array
 * @author Duc Pham <phamhoangduc10@gmail.com>
 */
function globalGetCitiesAddress() {
	global $db;
	$arr = [];

	$sql = "SELECT id, fullname FROM cities";
	$res = $db->query($sql);
	while($row = $db->fetchByAssoc($res)) {
		$arr[$row['id']] = $row['fullname'];
	}
	return $arr;
}

/**
 * Get list of districts in Vietnam
 * 
 * @param string $parent_id City ID
 * @return array
 * @author Duc Pham <phamhoangduc10@gmail.com>
 */
function globalGetDistrictsAddress($parent_id = '') {
	global $db;
	$arr = [];

	$sql = !empty($parent_id) ?
		"SELECT id, fullname FROM districts WHERE parent_id = '$parent_id'" :
		"SELECT id, fullname FROM districts";
	$res = $db->query($sql);

	while($row = $db->fetchByAssoc($res)) {
		$arr[$row['id']] = $row['fullname'];
	}

	return $arr;
}

/**
 * Get list of wards in Vietnam
 * 
 * @param string $parent_id District ID
 * @return array
 * @author Duc Pham <phamhoangduc10@gmail.com>
 */
function globalGetWardsAddress($parent_id = '') {
	global $db;
	$arr = [];

	$sql = !empty($parent_id) ?
		"SELECT id, fullname FROM wards WHERE parent_id = '$parent_id'" :
		"SELECT id, fullname FROM wards LIMIT 500";
	$res = $db->query($sql);

	while($row = $db->fetchByAssoc($res)) {
		$arr[$row['id']] = $row['fullname'];
	}
	
	return $arr;
}

/**
 * Get city details by code
 * 
 * @param string $id City code
 * @return array
 * @author Duc Pham <phamhoangduc10@gmail.com>
 */
function globalGetCityDetails($id) {
    $result = [];
    if($id && !empty($id)) {
        global $db;
        $sql = "SELECT id, cities.name, cities.type, fullname FROM cities WHERE id = '$id'";
        $res = $db->query($sql);
        while($row = $db->fetchByAssoc($res)) {
            $result = [
                'id' => $id,
                'name' => $row['name'],
                'fullname' => $row['fullname'],
                'type' => $row['type']
            ];
        }
    }
	return $result;
}

/**
 * Get district details by code
 * 
 * @param string $id District code
 * @return array
 * @author Duc Pham <phamhoangduc10@gmail.com>
 */
function globalGetDistrictDetails($id) {
    $result = [];
    if($id && !empty($id)) {
        global $db;
        $sql = "SELECT id, districts.name, districts.type, fullname, parent_id FROM districts WHERE id = '$id'";
        $res = $db->query($sql);
        while($row = $db->fetchByAssoc($res)) {
            $result = [
                'id' => $id,
                'name' => $row['name'],
                'fullname' => $row['fullname'],
                'type' => $row['type'],
                'parent_id' => $row['parent_id']
            ];
        }
    }
	return $result;
}

/**
 * Get ward details by code
 * 
 * @param string $id Ward code
 * @return array
 * @author Duc Pham <phamhoangduc10@gmail.com>
 */
function globalGetWardDetails($id) {
    $result = [];
    if($id && !empty($id)) {
        global $db;
        $sql = "SELECT id, wards.name, wards.type, fullname, parent_id FROM wards WHERE id = '$id'";
        $res = $db->query($sql);
        while($row = $db->fetchByAssoc($res)) {
            $result = [
                'id' => $id,
                'name' => $row['name'],
                'fullname' => $row['fullname'],
                'type' => $row['type'],
                'parent_id' => $row['parent_id']
            ];
        }
    }
	return $result;
}

/**
 * Get full text address
 * 
 * @param string $id Ward code
 * @return array
 * @author Duc Pham <phamhoangduc10@gmail.com>
 */
function globalShowAddress($street = '', $ward_code  = '', $district_code  = '', $city_code = '', $country = 'VIETNAM') {
    $country        = $country === 'VIETNAM' ? 'VN' : $country;
    $arr_city       = globalGetCityDetails($city_code);
    $arr_district   = globalGetDistrictDetails($district_code);
    $arr_ward       = globalGetWardDetails($ward_code);
    
    $address  = !empty($street)         ? $street : '';
    $address .= !empty($arr_ward)       ? ', '. $arr_ward['fullname'] : '';
    $address .= !empty($arr_district)   ? ', '. $arr_district['fullname'] : '';
    $address .= !empty($arr_city)       ? ', '. $arr_city['fullname'] : '';
    $address .= !empty($country)        ? ', '. $country : '';

    // Format
    $address = trim(ltrim(trim($address), ','));
    if($address === 'VN') $address = '';

    return $address;
}