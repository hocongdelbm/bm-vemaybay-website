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

    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
        }

        return false;
    }

    /**
     * Import/upsert airports from custom/airports.json, entries shaped as
     * {"label1": "City, CC (CODE)", ...}. Records are matched by iata_code;
     * duplicate codes within the file are skipped (first wins).
     */
    public function importFromJsonFile($filePath = '')
    {
        if (empty($filePath)) {
            $filePath = dirname(dirname(dirname(__FILE__))) . '/custom/airports.json';
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
        $seenCodes = array();

        foreach ($rows as $row) {
            $label = trim(isset($row['label1']) ? $row['label1'] : '');

            if (!preg_match('/^(.*),\s*([A-Za-z]{2})\s*\(([A-Za-z0-9]{2,4})\)$/', $label, $m)) {
                $skipped++;
                continue;
            }

            $cityName = trim($m[1]);
            $countryCode = strtoupper(trim($m[2]));
            $code = strtoupper(trim($m[3]));

            if ($code === '' || $cityName === '' || isset($seenCodes[$code])) {
                $skipped++;
                continue;
            }
            $seenCodes[$code] = true;

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

            $bean->name = $label;
            $bean->iata_code = $code;
            $bean->city_name = $cityName;
            $bean->country_code = $countryCode;
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
