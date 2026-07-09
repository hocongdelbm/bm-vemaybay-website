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

    public $iata_code;
    public $icao_code;
    public $logo;
    public $country;
    public $is_active;

    /**
     * Mã hãng bay legacy/biến thể vẫn còn lưu trong ec_booking_itineraries.airline_code
     * nhưng không tồn tại trong ec_airlines.iata_code. Map về đúng mã IATA chuẩn.
     */
    private const LEGACY_CODE_MAP = [
        'VNA' => 'VN',
        'VJA' => 'VJ',
        'VNP' => 'BL',
        'BBA' => 'QH',
        'VTA' => 'VU',
    ];

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
            SugarApplication::redirect('index.php?module=EC_Airlines&action=EditView&record=' . $this->id);
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

        $query = "SELECT id FROM ec_airlines WHERE iata_code = '" . $this->db->quote($this->iata_code) . "' AND deleted = 0";
        if (!empty($this->id)) {
            $query .= " AND id != '" . $this->db->quote($this->id) . "'";
        }

        return $this->db->getOne($query);
    }

    /**
     * Chuẩn hoá mã hãng bay legacy (VNA, VJA, VNP, BBA, VTA...) về đúng mã IATA
     *
     * @param string $rawCode Mã hãng bay gốc (vd "VNA", "VN")
     * @return string Mã IATA chuẩn
     */
    public static function normalizeIataCode($rawCode)
    {
        $code = strtoupper(trim((string)$rawCode));

        return self::LEGACY_CODE_MAP[$code] ?? $code;
    }

    /**
     * Lấy 1 bản ghi hãng bay (name, logo) theo mã IATA, cache trong request.
     *
     * @param string $iataCode Mã IATA (vd "VJ", "VN")
     * @return array|null ['name' => ..., 'logo' => ...] hoặc NULL nếu không tìm thấy
     */
    private static function getAirlineRecord($iataCode)
    {
        static $cache = [];

        $code = strtoupper(trim((string)$iataCode));
        if ($code === '') {
            return null;
        }

        if (!array_key_exists($code, $cache)) {
            $bean = BeanFactory::getBean('EC_Airlines');
            $row = $bean->db->fetchOne(
                "SELECT name, logo FROM ec_airlines WHERE iata_code = '" . $bean->db->quote($code) . "' AND deleted = 0"
            );
            $cache[$code] = $row ?: null;
        }

        return $cache[$code];
    }

    /**
     * Lấy URL logo hãng bay
     *
     * @param string $iataCode Mã IATA (vd "VJ", "VN")
     * @return string|null URL logo hoặc NULL nếu chưa có/không tìm thấy
     */
    public static function getLogoUrl($iataCode)
    {
        return self::getAirlineRecord($iataCode)['logo'] ?? null;
    }

    /**
     * Lấy tên hãng bay theo mã IATA.
     *
     * @param string $iataCode Mã IATA (vd "VJ", "VN")
     * @return string|null Tên hãng bay hoặc NULL nếu không tìm thấy
     */
    public static function getAirlineName($iataCode)
    {
        return self::getAirlineRecord($iataCode)['name'] ?? null;
    }

    /**
     * Lấy danh sách hãng bay đang active dạng mảng [iata_code => "Tên hãng (MÃ)"].
     *
     * @param bool $includeAll Có thêm phần tử '' => '-- Tất cả --' ở đầu danh sách
     * @return array
     */
    public static function getAirlineList($includeAll = false)
    {
        static $cache = null;

        if ($cache === null) {
            $cache = [];
            $bean = BeanFactory::getBean('EC_Airlines');

            $sql = "SELECT iata_code, name FROM ec_airlines
                    WHERE deleted = 0 AND is_active = 1
                        AND iata_code IS NOT NULL AND iata_code != ''
                    ORDER BY name ASC";
            $res = $bean->db->query($sql);
            while ($row = $bean->db->fetchByAssoc($res)) {
                $code = strtoupper(trim($row['iata_code']));
                if ($code === '') {
                    continue;
                }
                $cache[$code] = $row['name'] . ' (' . $code . ')';
            }
        }

        return $includeAll ? (['' => '-- Tất cả --'] + $cache) : $cache;
    }

    /**
     * Sinh HTML <option> cho select hãng bay, dùng chung cho các view/report cần chọn hãng bay.
     *
     * @param string $selected Mã hãng đang được chọn (để đánh dấu selected)
     * @param bool $includeAll Có thêm option "-- Tất cả --"
     * @return string
     */
    public static function getAirlineOptions($selected = '', $includeAll = true)
    {
        return get_select_options_with_id(self::getAirlineList($includeAll), $selected);
    }
}
