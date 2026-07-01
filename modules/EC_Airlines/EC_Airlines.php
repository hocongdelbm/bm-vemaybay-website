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
     * Import/upsert airlines from custom/airlines.xml (<RECORD><code/><name/><country/></RECORD>).
     * Records are matched by iata_code; duplicate codes within the file are skipped (first wins).
     */
    public function importFromXmlFile($filePath = '')
    {
        if (empty($filePath)) {
            $filePath = dirname(dirname(dirname(__FILE__))) . '/custom/airlines.xml';
        }

        if (!file_exists($filePath)) {
            return array('success' => false, 'message' => 'File not found: ' . $filePath);
        }

        $xml = simplexml_load_file($filePath);
        if ($xml === false) {
            return array('success' => false, 'message' => 'Unable to parse XML file');
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $seenCodes = array();

        foreach ($xml->RECORD as $record) {
            $code = trim((string) $record->code);
            $name = trim((string) $record->name);
            $country = trim((string) $record->country);

            if ($code === '' || $name === '' || isset($seenCodes[$code])) {
                $skipped++;
                continue;
            }
            $seenCodes[$code] = true;

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
            $bean->country = $country;
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
