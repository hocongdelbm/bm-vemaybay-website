<?php
class EC_Airports extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Airports';
    public $object_name = 'EC_Airports';
    public $table_name = 'ec_airports';
    public $importable = false;

    public $id;
    public $name;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $modified_by_name;
    public $created_by;
    public $created_by_name;
    public $description;
    public $deleted;
    public $created_by_link;
    public $modified_user_link;
    public $assigned_user_id;
    public $assigned_user_name;
    public $assigned_user_link;
    public $SecurityGroups;

    public $iata_code;
    public $city_name;
    public $country;
    public $prefix;
    public $geo_country;
    public $is_active;

    public function __construct()
    {
        parent::__construct();
    }

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }

    /**
     * Import/upsert airports from modules/EC_Airports/list_airports
     * .json, entries shaped as
     * {"HAN": {"AirportCode": "HAN", "AirportName": "...", "CityName": "...",
     * "Prefix": "...", "RegionCode": "VN", "Region": "...", "GeoCountryId": 1,
     * "GeoCountryName": "..."}, ...}. Records are matched by iata_code.
     * RegionCode is stored in the "country" field (enum, options=region_dom).
     */
    public function importFromJsonFileAirport($filePath = '')
    {
        if (empty($filePath)) {
            $filePath = dirname(__FILE__) . '/list_airports.json';
        }

        if (!file_exists($filePath)) {
            return array('success' => false, 'message' => 'File not found: ' . $filePath);
        }

        $rows = json_decode(file_get_contents($filePath), true);
        if (!is_array($rows)) {
            return array('success' => false, 'message' => 'Unable to parse JSON file');
        }

        $prefixMap = array(
            'Sân bay quốc tế' => 'san-bay-quoc-te',
            'Sân bay'         => 'san-bay',
        );

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $key => $row) {
            $code = strtoupper(trim(isset($row['AirportCode']) ? $row['AirportCode'] : $key));
            $airportName = trim(isset($row['AirportName']) ? $row['AirportName'] : '');
            $cityName = trim(isset($row['CityName']) ? $row['CityName'] : '');
            $regionCode = strtoupper(trim(isset($row['RegionCode']) ? $row['RegionCode'] : ''));
            $prefixLabel = trim(isset($row['Prefix']) ? $row['Prefix'] : '');
            $geoCountryId = isset($row['GeoCountryId']) ? (string) $row['GeoCountryId'] : '';

            if ($code === '' || $airportName === '') {
                $skipped++;
                continue;
            }

            $bean = BeanFactory::getBean('EC_Airports');
            $existingId = $bean->db->getOne(
                "SELECT id FROM ec_airports WHERE iata_code = '" . $bean->db->quote($code) . "' AND deleted = 0"
            );

            if (!empty($existingId)) {
                $bean->retrieve($existingId);
                $updated++;
            } else {
                $created++;
            }

            $bean->name = $airportName;
            $bean->iata_code = $code;
            $bean->city_name = $cityName;
            $bean->country = $regionCode;
            $bean->prefix = isset($prefixMap[$prefixLabel]) ? $prefixMap[$prefixLabel] : 'san-bay';
            $bean->geo_country = $geoCountryId;
            $bean->is_active = 1;
            $bean->save();
        }

        return array(
            'success' => true,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
        );
    }
}
