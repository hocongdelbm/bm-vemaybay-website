<?php
/**
 * ACLRoles: DisableModuleRole view
 * URL: index.php?module=ACLRoles&action=DisableModuleRole
 *
 * Cho phép admin chọn nhiều Role + nhiều Module,
 * sau đó disable toàn bộ actions của các module đó trên các role đã chọn.
 */
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class ACLRolesViewDisablemodulerole extends SugarView
{
    // ── Admin gate ─────────────────────────────────────────────────────────────
    public function preDisplay()
    {
        if (!is_admin($GLOBALS['current_user'])) {
            sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN'] ?? 'Administrator access required.');
        }
    }

    // ── Router ─────────────────────────────────────────────────────────────────
    public function display()
    {
        echo '<link rel="stylesheet" href="modules/ACLRoles/css/disablerole.css">';
        echo '<link rel="stylesheet" href="modules/ACLRoles/css/disablemodulerole.css">';

        $db = DBManagerFactory::getInstance();
        $this->ensureAclActionsForModules($db);

        // Bước 3: thực thi (POST confirmed=1)
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['confirmed'])) {
            $this->renderExecute($db);
            echo '<script src="modules/ACLRoles/js/disablerole.js"></script>';
            return;
        }

        // Bước 2: xác nhận (POST với role_ids[] + module_names[])
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $roleIds     = $_POST['role_ids']     ?? [];
            $moduleNames = $_POST['module_names'] ?? [];

            if (empty($roleIds) || empty($moduleNames)) {
                $this->renderPicker($db, 'Vui lòng chọn ít nhất 1 role và 1 module.');
                echo '<script src="modules/ACLRoles/js/disablerole.js"></script>';
                echo '<script src="modules/ACLRoles/js/disablemodulerole.js"></script>';
                return;
            }

            $this->renderConfirm($db, $roleIds, $moduleNames);
            echo '<script src="modules/ACLRoles/js/disablerole.js"></script>';
            echo '<script src="modules/ACLRoles/js/disablemodulerole.js"></script>';
            return;
        }

        // Bước 1: hiển thị picker
        $this->renderPicker($db);
        echo '<script src="modules/ACLRoles/js/disablerole.js"></script>';
        echo '<script src="modules/ACLRoles/js/disablemodulerole.js"></script>';
    }

    // ── Bước 1: Picker (chọn Roles + Modules) ─────────────────────────────────
    private function renderPicker($db, string $error = '')
    {
        // Load danh sách roles
        $rResult = $db->query("SELECT id, name, description FROM acl_roles WHERE deleted = 0 ORDER BY name");
        $roles   = [];
        while ($row = $db->fetchByAssoc($rResult)) {
            $roles[] = $row;
        }

        // Load danh sách modules (distinct category)
        $mResult = $db->query("SELECT DISTINCT category FROM acl_actions WHERE deleted = 0 ORDER BY category");
        $modules = [];
        while ($row = $db->fetchByAssoc($mResult)) {
            $modules[] = $row['category'];
        }

        echo $this->stepBar(1);
        echo '<div class="drm-wrap">';
        echo '  <div class="drm-card">';
        echo '    <div class="drm-card__header">';
        echo '      <span class="drm-icon drm-icon--shield">&#9679;</span>';
        echo '      <div>';
        echo '        <h2 class="drm-card__title">Vô hiệu hóa theo Module</h2>';
        echo '        <p class="drm-card__sub">Chọn role và module cần vô hiệu hóa. Có thể chọn nhiều role và nhiều module cùng lúc.</p>';
        echo '      </div>';
        echo '    </div>';
        echo '    <div class="drm-card__body">';

        if ($error) {
            echo '<div class="drm-alert drm-alert--warning">' . htmlspecialchars($error) . '</div>';
        }

        if (empty($roles) || empty($modules)) {
            echo '<div class="drm-alert drm-alert--warning">Không tìm thấy dữ liệu ACL roles hoặc modules.</div>';
        } else {
            echo '<form method="POST" action="index.php" id="dmr-picker-form">';
            echo '  <input type="hidden" name="module" value="ACLRoles">';
            echo '  <input type="hidden" name="action" value="DisableModuleRole">';

            // ── Cột Roles ──────────────────────────────────────────────────────
            echo '  <div class="dmr-columns">';

            // --- Panel Roles ---
            echo '    <div class="dmr-panel">';
            echo '      <div class="dmr-panel__header">';
            echo '        <span class="dmr-panel__title">Roles <span class="dmr-badge" id="dmr-role-count">0</span></span>';
            echo '        <label class="dmr-select-all">';
            echo '          <input type="checkbox" id="dmr-role-all" onchange="dmrToggleAll(\'role\', this.checked)"> Chọn tất cả';
            echo '        </label>';
            echo '      </div>';
            echo '      <div class="dmr-search-wrap">';
            echo '        <input type="text" class="dmr-search" placeholder="Tìm role..." oninput="dmrFilter(\'role\', this.value)">';
            echo '      </div>';
            echo '      <div class="dmr-list" id="dmr-role-list">';
            foreach ($roles as $r) {
                $id      = htmlspecialchars($r['id']);
                $name    = htmlspecialchars($r['name']);
                $desc    = htmlspecialchars($r['description'] ?? '');
                $initials = strtoupper(mb_substr($r['name'], 0, 2));
                echo "        <label class=\"dmr-item\" data-name=\"" . strtolower($name) . "\">";
                echo "          <input type=\"checkbox\" name=\"role_ids[]\" value=\"{$id}\" onchange=\"dmrUpdateCount('role')\">";
                echo "          <div class=\"dmr-item__avatar\">{$initials}</div>";
                echo "          <div class=\"dmr-item__info\">";
                echo "            <div class=\"dmr-item__name\">{$name}</div>";
                if ($desc) {
                    echo "            <div class=\"dmr-item__desc\">{$desc}</div>";
                }
                echo "          </div>";
                echo "          <div class=\"dmr-item__check\">&#10003;</div>";
                echo "        </label>";
            }
            echo '      </div>';
            echo '    </div>'; // dmr-panel roles

            // --- Panel Modules ---
            echo '    <div class="dmr-panel">';
            echo '      <div class="dmr-panel__header">';
            echo '        <span class="dmr-panel__title">Modules <span class="dmr-badge" id="dmr-module-count">0</span></span>';
            echo '        <label class="dmr-select-all">';
            echo '          <input type="checkbox" id="dmr-module-all" onchange="dmrToggleAll(\'module\', this.checked)"> Chọn tất cả';
            echo '        </label>';
            echo '      </div>';
            echo '      <div class="dmr-search-wrap">';
            echo '        <input type="text" class="dmr-search" placeholder="Tìm module..." oninput="dmrFilter(\'module\', this.value)">';
            echo '      </div>';
            echo '      <div class="dmr-list" id="dmr-module-list">';
            foreach ($modules as $mod) {
                $modH  = htmlspecialchars($mod);
                $isEc  = str_starts_with($mod, 'EC_');
                $extra = $isEc ? ' dmr-item--ec' : '';
                echo "        <label class=\"dmr-item{$extra}\" data-name=\"" . strtolower($modH) . "\">";
                echo "          <input type=\"checkbox\" name=\"module_names[]\" value=\"{$modH}\" onchange=\"dmrUpdateCount('module')\">";
                echo "          <div class=\"dmr-item__mod-icon\">M</div>";
                echo "          <div class=\"dmr-item__info\">";
                echo "            <div class=\"dmr-item__name\">{$modH}</div>";
                if ($isEc) {
                    echo "            <div class=\"dmr-item__desc\">EC Module</div>";
                }
                echo "          </div>";
                echo "          <div class=\"dmr-item__check\">&#10003;</div>";
                echo "        </label>";
            }
            echo '      </div>';
            echo '    </div>'; // dmr-panel modules

            echo '  </div>'; // dmr-columns

            // ── Summary bar ────────────────────────────────────────────────────
            echo '  <div class="dmr-summary" id="dmr-summary">';
            echo '    <span id="dmr-summary-text" class="dmr-summary__text">Chưa chọn gì</span>';
            echo '  </div>';

            echo '  <div class="drm-actions">';
            echo '    <a href="index.php?module=ACLRoles&action=index" class="drm-btn drm-btn--ghost">&#8592; Quay lại</a>';
            echo '    <button type="submit" class="drm-btn drm-btn--primary" id="dmr-next-btn" disabled>';
            echo '      Xem trước & Xác nhận <span class="drm-btn-arrow">&#8594;</span>';
            echo '    </button>';
            echo '  </div>';

            echo '</form>';
        }

        echo '    </div>'; // drm-card__body
        echo '  </div>'; // drm-card
        echo '</div>'; // drm-wrap
    }

    /**
     * Đồng bộ các module có hỗ trợ ACL vào bảng acl_actions.
     *
     * Một số custom module truy cập được bình thường nhưng chưa có dòng
     * acl_actions, nên picker không thấy module đó khi query DISTINCT category.
     */
    private function ensureAclActionsForModules($db): void
    {
        global $beanList;

        if (empty($beanList) || !is_array($beanList)) {
            return;
        }

        require_once('modules/ACLActions/ACLAction.php');

        $existing = [];
        $result = $db->query("SELECT DISTINCT category FROM acl_actions WHERE deleted = 0");
        while ($row = $db->fetchByAssoc($result)) {
            $existing[$row['category']] = true;
        }

        foreach ($beanList as $module => $class) {
            if ($class === 'Tracker') {
                continue;
            }

            $bean = BeanFactory::newBean($module);
            if (!$bean || !empty($bean->acl_display_only) || !$bean->bean_implements('ACL')) {
                continue;
            }

            $category = $bean->getACLCategory();
            if (isset($existing[$category])) {
                continue;
            }

            if (!empty($bean->acltype)) {
                ACLAction::addActions($category, $bean->acltype);
            } else {
                ACLAction::addActions($category);
            }
            $existing[$category] = true;
        }
    }

    // ── Bước 2: Xác nhận ──────────────────────────────────────────────────────
    private function renderConfirm($db, array $roleIds, array $moduleNames)
    {
        // Sanitize inputs
        $safeRoleIds     = array_filter(array_map([$db, 'quote'], $roleIds));
        $safeModuleNames = array_filter(array_map('htmlspecialchars', $moduleNames));

        if (empty($safeRoleIds) || empty($safeModuleNames)) {
            $this->renderPicker($db, 'Dữ liệu không hợp lệ.');
            return;
        }

        // Load role details
        $roleIdList = implode("','", $safeRoleIds);
        $rResult = $db->query("SELECT id, name FROM acl_roles WHERE id IN ('{$roleIdList}') AND deleted = 0 ORDER BY name");
        $roles   = [];
        while ($row = $db->fetchByAssoc($rResult)) {
            $roles[] = $row;
        }

        // Load action count per selected module
        $modList = implode("','", $safeModuleNames);
        $totalActions = (int) $db->getOne("SELECT COUNT(*) FROM acl_actions WHERE deleted = 0 AND category IN ('{$modList}')");

        // Load action names per module for preview
        $actResult = $db->query("SELECT category, name FROM acl_actions WHERE deleted = 0 AND category IN ('{$modList}') ORDER BY category, name");
        $actionsByModule = [];
        while ($row = $db->fetchByAssoc($actResult)) {
            $actionsByModule[$row['category']][] = $row['name'];
        }

        $totalCombinations = count($roles) * count($safeModuleNames);

        echo $this->stepBar(2);
        echo '<div class="drm-wrap drm-wrap--danger">';
        echo '  <div class="drm-card drm-card--danger">';

        // Danger header
        echo '    <div class="drm-danger-header">';
        echo '      <div class="drm-danger-icon">&#9888;</div>';
        echo '      <div>';
        echo '        <div class="drm-danger-label">XÁC NHẬN THAO TÁC</div>';
        echo '        <h2 class="drm-danger-title">Vô hiệu hóa theo Module</h2>';
        echo '        <p class="drm-danger-sub">Kiểm tra kỹ trước khi thực hiện. Thao tác này sẽ ghi đè quyền truy cập.</p>';
        echo '      </div>';
        echo '    </div>';

        echo '    <div class="drm-card__body">';

        // Stats
        echo '    <div class="drm-stats">';
        $this->statCard(count($roles),          'Roles bị ảnh hưởng',  '--clr-stat-orange');
        $this->statCard(count($safeModuleNames), 'Modules bị khóa',     '--clr-stat-red');
        $this->statCard($totalActions,           'Actions / module',    '--clr-stat-blue');
        echo '    </div>';

        // Danh sách roles được chọn
        echo '    <div class="drm-section">';
        echo '      <div class="drm-section__label">Roles sẽ bị ảnh hưởng</div>';
        echo '      <div class="drm-chips">';
        foreach ($roles as $r) {
            echo '<span class="drm-chip">' . htmlspecialchars($r['name']) . '</span>';
        }
        echo '      </div>';
        echo '    </div>';

        // Danh sách modules theo bảng mở rộng
        echo '    <div class="drm-section">';
        echo '      <div class="drm-section__label">Modules + actions sẽ bị vô hiệu hóa</div>';
        echo '      <div class="dmr-module-table">';
        foreach ($safeModuleNames as $mod) {
            $isEc     = str_starts_with($mod, 'EC_');
            $actions  = $actionsByModule[$mod] ?? [];
            echo '<div class="dmr-module-row">';
            echo '  <div class="dmr-module-row__name' . ($isEc ? ' dmr-module-row__name--ec' : '') . '">' . htmlspecialchars($mod) . '</div>';
            echo '  <div class="dmr-module-row__actions">';
            foreach ($actions as $act) {
                $isAccess = ($act === 'access');
                $valClass = $isAccess ? 'dmr-act--98' : 'dmr-act--99';
                $valText  = $isAccess ? '-98' : '-99';
                echo "<span class=\"dmr-act {$valClass}\"><code>{$act}</code> → {$valText}</span>";
            }
            echo '  </div>';
            echo '</div>';
        }
        echo '      </div>';
        echo '    </div>';

        // Giá trị sẽ áp dụng
        echo '    <div class="drm-notice">';
        echo '      <span class="drm-notice__icon">&#8505;</span>';
        echo '      <strong>access</strong> → <code>-98</code> (Disabled) &nbsp;|&nbsp; Tất cả action khác → <code>-99</code> (None)';
        echo '    </div>';

        // Form submit
        echo '    <div class="drm-actions drm-actions--split">';
        echo '      <a href="index.php?module=ACLRoles&action=DisableModuleRole" class="drm-btn drm-btn--ghost">&#8592; Quay lại chọn lại</a>';

        echo '      <form method="POST" action="index.php" onsubmit="return drmConfirmSubmitModule(this)" data-count="' . $totalCombinations . '">';
        echo '        <input type="hidden" name="module" value="ACLRoles">';
        echo '        <input type="hidden" name="action" value="DisableModuleRole">';
        echo '        <input type="hidden" name="confirmed" value="1">';

        // Truyền lại arrays
        foreach ($safeRoleIds as $id) {
            echo '        <input type="hidden" name="role_ids[]" value="' . htmlspecialchars($id) . '">';
        }
        foreach ($safeModuleNames as $mod) {
            echo '        <input type="hidden" name="module_names[]" value="' . htmlspecialchars($mod) . '">';
        }

        echo '        <button type="submit" class="drm-btn drm-btn--danger" id="drm-confirm-btn">';
        echo '          <span id="drm-confirm-text">&#9888; Xác nhận vô hiệu hóa</span>';
        echo '        </button>';
        echo '      </form>';
        echo '    </div>';

        echo '    </div>'; // drm-card__body
        echo '  </div>'; // drm-card
        echo '</div>'; // drm-wrap
    }

    // ── Bước 3: Thực thi ──────────────────────────────────────────────────────
    private function renderExecute($db)
    {
        $roleIds     = $_POST['role_ids']     ?? [];
        $moduleNames = $_POST['module_names'] ?? [];

        $safeRoleIds     = array_filter(array_map([$db, 'quote'], $roleIds));
        $safeModuleNames = array_filter(array_map([$db, 'quote'], $moduleNames));

        if (empty($safeRoleIds) || empty($safeModuleNames)) {
            echo '<div class="drm-wrap"><div class="drm-alert drm-alert--danger">Dữ liệu không hợp lệ. <a href="index.php?module=ACLRoles&action=DisableModuleRole">Thử lại</a></div></div>';
            return;
        }

        $adminName = htmlspecialchars($GLOBALS['current_user']->user_name);
        $now       = $db->now();
        $timestamp = date('Y-m-d H:i:s');

        $modList    = implode("','", $safeModuleNames);
        $roleIdList = implode("','", $safeRoleIds);

        // Load role names cho audit
        $rResult = $db->query("SELECT id, name FROM acl_roles WHERE id IN ('{$roleIdList}') AND deleted = 0 ORDER BY name");
        $roles   = [];
        while ($row = $db->fetchByAssoc($rResult)) {
            $roles[$row['id']] = $row['name'];
        }

        // Thực thi: với mỗi role, disable tất cả actions thuộc modules đã chọn
        $totalDisabled = 0;
        foreach ($safeRoleIds as $roleId) {
            $db->query("
                INSERT INTO acl_roles_actions
                    (id, role_id, action_id, access_override, date_modified, deleted)
                SELECT
                    UUID(),
                    '{$roleId}',
                    a.id,
                    CASE a.name WHEN 'access' THEN -98 ELSE -99 END,
                    {$now},
                    0
                FROM acl_actions a
                WHERE a.deleted = 0
                  AND a.category IN ('{$modList}')
                ON DUPLICATE KEY UPDATE
                    access_override = VALUES(access_override),
                    date_modified   = VALUES(date_modified),
                    deleted         = 0
            ");
        }

        // Đếm kết quả
        $totalDisabled = (int) $db->getOne("
            SELECT COUNT(*) FROM acl_roles_actions ara
            JOIN acl_actions a ON a.id = ara.action_id
            WHERE ara.role_id IN ('{$roleIdList}')
              AND a.category IN ('{$modList}')
              AND ara.access_override IN (-98, -99)
              AND ara.deleted = 0
        ");

        $displayModules = array_map('htmlspecialchars', $moduleNames);

        echo $this->stepBar(3);
        echo '<div class="drm-wrap drm-wrap--success">';
        echo '  <div class="drm-card drm-card--success">';

        echo '    <div class="drm-success-header">';
        echo '      <div class="drm-success-icon">&#10003;</div>';
        echo '      <div>';
        echo '        <div class="drm-success-label">HOÀN THÀNH</div>';
        echo '        <h2 class="drm-success-title">Đã vô hiệu hóa thành công</h2>';
        echo '        <p class="drm-success-sub">' . count($roles) . ' role × ' . count($displayModules) . ' module đã được khóa.</p>';
        echo '      </div>';
        echo '    </div>';

        echo '    <div class="drm-card__body">';

        // Stats
        echo '    <div class="drm-stats">';
        $this->statCard($totalDisabled,          'Bản ghi đã ghi',     '--clr-stat-red');
        $this->statCard(count($roles),           'Roles bị ảnh hưởng', '--clr-stat-orange');
        $this->statCard(count($displayModules),  'Modules đã khóa',    '--clr-stat-blue');
        echo '    </div>';

        // Audit trail
        echo '    <div class="drm-audit">';
        echo '      <div class="drm-audit__title">Audit Trail</div>';
        echo '      <div class="drm-audit__grid">';
        $this->auditRow('Roles bị ảnh hưởng', implode(', ', array_map('htmlspecialchars', array_values($roles))));
        $this->auditRow('Modules đã khóa',    implode(', ', $displayModules));
        $this->auditRow('access action',       '<code>-98</code> Disabled');
        $this->auditRow('Các action khác',     '<code>-99</code> None');
        $this->auditRow('Thực hiện bởi',       $adminName);
        $this->auditRow('Thời gian',           $timestamp);
        echo '      </div>';
        echo '    </div>';

        // Roles chips
        echo '    <div class="drm-section">';
        echo '      <div class="drm-section__label">Roles đã xử lý</div>';
        echo '      <div class="drm-chips">';
        foreach ($roles as $rName) {
            echo '<span class="drm-chip drm-chip--locked">' . htmlspecialchars($rName) . '</span>';
        }
        echo '      </div>';
        echo '    </div>';

        // Module chips
        echo '    <div class="drm-section">';
        echo '      <div class="drm-section__label">Modules đã khóa</div>';
        echo '      <div class="drm-chips">';
        foreach ($displayModules as $mod) {
            $isEc = str_starts_with($mod, 'EC_');
            echo '<span class="drm-chip drm-chip--locked' . ($isEc ? ' drm-chip--ec' : '') . '">' . $mod . '</span>';
        }
        echo '      </div>';
        echo '    </div>';

        echo '    <div class="drm-notice drm-notice--info">';
        echo '      <span class="drm-notice__icon">&#8505;</span>';
        echo '      Vào <a href="index.php?module=Administration&action=DiagnosticRun">Admin &rarr; Repair &rarr; Quick Repair and Rebuild</a> để xóa cache ACL. Người dùng bị ảnh hưởng cần đăng nhập lại.';
        echo '    </div>';

        echo '    <div class="drm-actions drm-actions--split">';
        echo '      <a href="index.php?module=ACLRoles&action=DisableModuleRole" class="drm-btn drm-btn--ghost">Thực hiện lại</a>';
        echo '      <a href="index.php?module=ACLRoles&action=index" class="drm-btn drm-btn--primary">Danh sách Roles &#8594;</a>';
        echo '    </div>';

        echo '    </div>'; // drm-card__body
        echo '  </div>'; // drm-card
        echo '</div>'; // drm-wrap
    }

    // ── Step bar ───────────────────────────────────────────────────────────────
    private function stepBar(int $active): string
    {
        $steps = ['Chọn Role & Module', 'Xác nhận', 'Hoàn thành'];
        $html  = '<div class="drm-steps">';
        foreach ($steps as $i => $label) {
            $n    = $i + 1;
            $cls  = 'drm-step';
            if ($n < $active)  $cls .= ' drm-step--done';
            if ($n === $active) $cls .= ' drm-step--active';
            $icon = $n < $active ? '&#10003;' : $n;
            $html .= '<div class="' . $cls . '">';
            $html .= '  <div class="drm-step__dot">' . $icon . '</div>';
            $html .= '  <div class="drm-step__label">' . $label . '</div>';
            $html .= '</div>';
            if ($n < count($steps)) {
                $lineCls = $n < $active ? 'drm-step-line drm-step-line--done' : 'drm-step-line';
                $html .= '<div class="' . $lineCls . '"></div>';
            }
        }
        $html .= '</div>';
        return $html;
    }

    // ── Helpers ────────────────────────────────────────────────────────────────
    private function statCard($value, string $label, string $colorVar): void
    {
        echo '<div class="drm-stat">';
        echo '  <div class="drm-stat__value" style="color:var(' . $colorVar . ')">' . $value . '</div>';
        echo '  <div class="drm-stat__label">' . $label . '</div>';
        echo '</div>';
    }

    private function auditRow(string $key, string $value): void
    {
        echo '<div class="drm-audit__row">';
        echo '  <div class="drm-audit__key">' . $key . '</div>';
        echo '  <div class="drm-audit__val">' . $value . '</div>';
        echo '</div>';
    }
}
