<?php
class EC_Airlines extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Airlines';
    public $object_name = 'EC_Airlines';
    public $table_name = 'ec_airlines';
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

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }

    /**
     * Import/upsert airlines from modules/EC_Airlines/list_airlines.json, entries shaped as
     * {"AC": {"AirlineCode": "AC", "AirlineName": "Air Canada", "RegionCode": "CA", "Region": "Canada"}, ...}.
     * Records are matched by iata_code; the country field is set directly from RegionCode
     * (the region_dom key, same dom used by EC_Airports). An empty RegionCode is left blank.
     */
    public function importFromJsonFileAirlines($filePath = '')
    {
        if (empty($filePath)) {
            $filePath = dirname(__FILE__) . '/list_airlines.json';
        }

        if (!file_exists($filePath)) {
            return array('success' => false, 'message' => 'File not found: ' . $filePath);
        }

        $rows = json_decode(file_get_contents($filePath), true);
        if (!is_array($rows)) {
            return array('success' => false, 'message' => 'Unable to parse JSON file');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $key => $row) {
            $code = strtoupper(trim(isset($row['AirlineCode']) ? $row['AirlineCode'] : $key));
            $name = trim(isset($row['AirlineName']) ? $row['AirlineName'] : '');
            $regionCode = strtoupper(trim(isset($row['RegionCode']) ? $row['RegionCode'] : ''));

            if ($code === '' || $name === '') {
                $skipped++;
                continue;
            }

            $bean = BeanFactory::getBean('EC_Airlines');
            $existingId = $bean->db->getOne(
                "SELECT id FROM ec_airlines WHERE iata_code = '" . $bean->db->quote($code) . "' AND deleted = 0"
            );

            if (!empty($existingId)) {
                $bean->retrieve($existingId);
                $updated++;
            } else {
                $created++;
            }

            $bean->name = $name;
            $bean->iata_code = $code;
            $bean->country = $regionCode;
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
