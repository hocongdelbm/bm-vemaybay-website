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

    const AIRPORT_SCOPE_ALL = 'all';
    const AIRPORT_SCOPE_DOMESTIC = 'domestic';
    const AIRPORT_SCOPE_INTER = 'international';

    /**
     * geo_country = '1' (Việt Nam) trong geo_country_dom là sân bay nội địa, còn lại là quốc tế.
     */
    const AIRPORT_GEO_COUNTRY_DOMESTIC = '1';

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

    public function save($check_notify = false)
    {
        $this->iata_code = strtoupper(trim((string)$this->iata_code));

        $duplicateId = $this->findDuplicateIataCodeId();
        if (!empty($duplicateId)) {
            global $mod_strings;
            $message = !empty($mod_strings['ERR_DUPLICATE_IATA_CODE'])
                ? sprintf($mod_strings['ERR_DUPLICATE_IATA_CODE'], $this->iata_code)
                : 'Mã IATA "' . $this->iata_code . '" đã tồn tại. Vui lòng nhập mã khác.';

            SugarApplication::appendErrorMessage($message);
            SugarApplication::redirect('index.php?module=EC_Airports&action=EditView&record=' . $this->id);
        }

        return parent::save($check_notify);
    }

    /**
     * Tìm id bản ghi khác đang dùng cùng mã IATA (nếu có).
     */
    private function findDuplicateIataCodeId()
    {
        if ($this->iata_code === '') {
            return null;
        }

        $query = "SELECT id FROM ec_airports WHERE iata_code = '" . $this->db->quote($this->iata_code) . "' AND deleted = 0";
        if (!empty($this->id)) {
            $query .= " AND id != '" . $this->db->quote($this->id) . "'";
        }

        return $this->db->getOne($query);
    }

    /**
     * Lấy 1 bản ghi sân bay (name, city_name) theo mã IATA, cache trong request.
     *
     * @param string $iataCode Mã IATA (vd "HAN", "SGN")
     * @return array|null ['name' => ..., 'city_name' => ...] hoặc NULL nếu không tìm thấy
     */
    private static function getAirportRecord($iataCode)
    {
        static $cache = [];

        $code = strtoupper(trim((string)$iataCode));
        if ($code === '') {
            return null;
        }

        if (!array_key_exists($code, $cache)) {
            $bean = BeanFactory::getBean('EC_Airports');
            $row = $bean->db->fetchOne(
                "SELECT name, city_name FROM ec_airports WHERE iata_code = '" . $bean->db->quote($code) . "' AND deleted = 0"
            );
            $cache[$code] = $row ?: null;
        }

        return $cache[$code];
    }

    /**
     * Lấy tên sân bay theo mã IATA.
     *
     * @param string $iataCode Mã IATA (vd "HAN", "SGN")
     * @return string|null Tên sân bay hoặc NULL nếu không tìm thấy
     */
    public static function getAirportName($iataCode)
    {
        return self::getAirportRecord($iataCode)['name'] ?? null;
    }

    /**
     * Lấy tên thành phố theo mã IATA sân bay.
     *
     * @param string $iataCode Mã IATA (vd "HAN", "SGN")
     * @return string|null Tên thành phố hoặc NULL nếu không tìm thấy
     */
    public static function getCityName($iataCode)
    {
        return self::getAirportRecord($iataCode)['city_name'] ?? null;
    }

    /**
     * Lấy danh sách sân bay đang active dạng mảng [iata_code => city_name].
     *
     * @param string $scope AIRPORT_SCOPE_ALL | AIRPORT_SCOPE_DOMESTIC | AIRPORT_SCOPE_INTER
     * @return array
     */
    public static function getAirportList($scope = self::AIRPORT_SCOPE_ALL)
    {
        static $cache = [];

        if (!isset($cache[$scope])) {
            $list = [];
            $bean = BeanFactory::getBean('EC_Airports');

            $sql = "SELECT iata_code, city_name FROM ec_airports
                    WHERE deleted = 0 AND is_active = 1
                        AND iata_code IS NOT NULL AND iata_code != ''";

            if ($scope === self::AIRPORT_SCOPE_DOMESTIC) {
                $sql .= " AND geo_country = '" . self::AIRPORT_GEO_COUNTRY_DOMESTIC . "'";
            } elseif ($scope === self::AIRPORT_SCOPE_INTER) {
                $sql .= " AND geo_country != '" . self::AIRPORT_GEO_COUNTRY_DOMESTIC . "'";
            }

            $res = $bean->db->query($sql);
            while ($row = $bean->db->fetchByAssoc($res)) {
                $code = strtoupper(trim($row['iata_code']));
                if ($code === '') {
                    continue;
                }
                $list[$code] = $row['city_name'];
            }

            $cache[$scope] = $list;
        }

        return $cache[$scope];
    }
}
