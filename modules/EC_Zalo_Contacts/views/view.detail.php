<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/MVC/View/views/view.detail.php');

class EC_Zalo_ContactsViewDetail extends ViewDetail {
    /** @var EC_Zalo_Contacts */
    public $bean;

    public function display()
    {
        // Các trường hiển thị dạng "rich" được dựng sẵn HTML rồi gắn vào detailviewdefs qua customCode
        $this->ss->assign('CUSTOM_ZALO_ID', $this->buildZaloId());
        $this->ss->assign('CUSTOM_AVATAR', $this->buildAvatar());
        $this->ss->assign('CUSTOM_STATUS', $this->buildStatus());
        $this->ss->assign('CUSTOM_FOLLOWER', $this->buildFollower());
        $this->ss->assign('CUSTOM_TAGS', $this->buildTags());
        $this->ss->assign('CUSTOM_ADDRESS', $this->buildAddress());

        parent::display();
    }

    /**
     * Zalo ID -> link mở trang Zalo của người dùng trong tab mới.
     */
    private function buildZaloId() {
        $url = "https://oa.zalo.me/chat?uid={$this->bean->zalo_id}&oaid={$this->bean->oa_id}";

        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">'
            . $this->bean->zalo_id
            . '</a>';
    }

    /**
     * Ảnh đại diện: avatar lưu dạng link ảnh -> hiển thị thumbnail tròn, click mở ảnh gốc.
     */
    private function buildAvatar()
    {
        $src = trim((string) ($this->bean->avatar ?? ''));
        if ($src === '') {
            return '';
        }

        $src_esc = htmlspecialchars($src, ENT_QUOTES, 'UTF-8');

        return '<a href="' . $src_esc . '" target="_blank" rel="noopener noreferrer">'
            . '<img src="' . $src_esc . '" alt="avatar" '
            . 'style="width:96px;height:96px;object-fit:cover;border-radius:50%;border:1px solid #e2e8f0" '
            . 'onerror="this.onerror=null;this.outerHTML=\'<span class=&quot;text-muted&quot;>Ảnh lỗi</span>\';"/>'
            . '</a>';
    }

    /**
     * Trạng thái người dùng Zalo -> badge màu.
     */
    private function buildStatus() {
        $status = trim((string) ($this->bean->status ?? ''));
        if ($status === '') $status = 'normal';

        $colors = [
            'normal'     => 'success',
            'banned'     => 'danger',
            'restricted' => 'secondary',
        ];

        $labels = [
            'normal'     => 'Bình thường',
            'banned'     => 'Đã chặn',
            'restricted' => 'Hạn chế',
        ];

        $key = strtolower($status);
        $class = isset($colors[$key]) ? $colors[$key] : 'info';
        $label = isset($labels[$key]) ? $labels[$key] : $status;

        return '<span class="badge rounded-pill bg-' . $class . '">'
            . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            . '</span>';
    }

    /**
     * Cờ "Quan tâm" (is_follower) -> badge Có / Chưa.
     */
    private function buildFollower()
    {
        return !empty($this->bean->is_follower)
            ? '<span class="badge rounded-pill bg-primary">Đã quan tâm</span>'
            : '<span class="badge rounded-pill bg-secondary">Chưa quan tâm</span>';
    }

    /**
     * Tags: chuỗi danh sách (phân tách bởi , hoặc ;) -> các badge.
     */
    private function buildTags()
    {
        $raw = trim((string) ($this->bean->tags ?? ''));
        if ($raw === '') {
            return '';
        }

        $html = '';
        foreach (preg_split('/[,;]+/', $raw) as $tag) {
            $tag = trim($tag);
            if ($tag === '') {
                continue;
            }
            $html .= '<span class="badge bg-light text-dark" style="margin:0 4px 4px 0;border:1px solid #e2e8f0">'
                . htmlspecialchars($tag, ENT_QUOTES, 'UTF-8')
                . '</span>';
        }

        return $html !== '' ? $html : '';
    }

    /**
     * Gộp địa chỉ chi tiết -> Phường/Xã -> Tỉnh/Thành phố thành một dòng, bỏ qua phần trống.
     */
    private function buildAddress()
    {
        $parts = array(
            trim((string) ($this->bean->address ?? '')),
            trim((string) ($this->bean->ward_commune ?? '')),
            trim((string) ($this->bean->province_city ?? '')),
        );
        $parts = array_filter($parts, function ($p) {
            return $p !== '';
        });

        if (empty($parts)) {
            return '';
        }

        return htmlspecialchars(implode(', ', $parts), ENT_QUOTES, 'UTF-8');
    }
}
