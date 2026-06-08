<?php
/**
 * ACLRoles: DisableRole view
 * URL: index.php?module=ACLRoles&action=DisableRole&record=ROLE_ID
 *
 * Login check : SuiteCRM entryPoint.php (automatic)
 * Admin check : preDisplay()
 */
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

class ACLRolesViewDisablerole extends SugarView
{
    // ── Admin gate ────────────────────────────────────────────────────────────
    public function preDisplay()
    {
        if (!is_admin($GLOBALS['current_user'])) {
            sugar_die($GLOBALS['app_strings']['ERR_NOT_ADMIN'] ?? 'Administrator access required.');
        }
    }

    // ── Router ────────────────────────────────────────────────────────────────
    public function display()
    {
        echo '<link rel="stylesheet" href="modules/ACLRoles/css/disablerole.css">';

        $db     = DBManagerFactory::getInstance();
        $roleId = $_REQUEST['record'] ?? '';

        if (!$roleId) {
            $this->renderPicker($db);
            echo '<script src="modules/ACLRoles/js/disablerole.js"></script>';
            return;
        }

        $role = BeanFactory::getBean('ACLRoles', $db->quote($roleId));
        if (!$role || empty($role->id)) {
            echo '<div class="drm-wrap"><div class="drm-alert drm-alert--danger">Role not found: <code>' . htmlspecialchars($roleId) . '</code></div></div>';
            echo '<script src="modules/ACLRoles/js/disablerole.js"></script>';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['confirmed'])) {
            $this->renderExecute($db, $role);
        } else {
            $this->renderConfirm($db, $role);
        }

        echo '<script src="modules/ACLRoles/js/disablerole.js"></script>';
    }

    // ── State 1: Role picker ─────────────────────────────────────────────────
    private function renderPicker($db)
    {
        $result = $db->query("SELECT id, name, description FROM acl_roles WHERE deleted = 0 ORDER BY name");
        $roles  = [];
        while ($row = $db->fetchByAssoc($result)) {
            $roles[] = $row;
        }

        echo $this->stepBar(1);
        echo '<div class="drm-wrap">';
        echo '  <div class="drm-card">';
        echo '    <div class="drm-card__header">';
        echo '      <span class="drm-icon drm-icon--shield">&#9679;</span>';
        echo '      <div>';
        echo '        <h2 class="drm-card__title">Select Role to Disable</h2>';
        echo '        <p class="drm-card__sub">Choose the ACL role whose permissions will be set to Disabled (−98).</p>';
        echo '      </div>';
        echo '    </div>';
        echo '    <div class="drm-card__body">';

        if (empty($roles)) {
            echo '<div class="drm-alert drm-alert--warning">No ACL roles found in the database.</div>';
        } else {
            echo '<form method="GET" action="index.php" id="drm-picker-form">';
            echo '  <input type="hidden" name="module" value="ACLRoles">';
            echo '  <input type="hidden" name="action" value="DisableRole">';
            echo '  <input type="hidden" name="record" id="drm-selected-record" value="">';
            echo '  <div class="drm-role-list">';
            foreach ($roles as $r) {
                $id   = htmlspecialchars($r['id']);
                $name = htmlspecialchars($r['name']);
                $desc = htmlspecialchars($r['description'] ?? '');
                $initials = strtoupper(mb_substr($name, 0, 2));
                echo "<div class=\"drm-role-item\" data-id=\"{$id}\" onclick=\"selectRole('{$id}', this)\">";
                echo "  <div class=\"drm-role-avatar\">{$initials}</div>";
                echo '  <div class="drm-role-info">';
                echo "    <div class=\"drm-role-name\">{$name}</div>";
                if ($desc) {
                    echo "    <div class=\"drm-role-desc\">{$desc}</div>";
                }
                echo '  </div>';
                echo '  <div class="drm-role-check">&#10003;</div>';
                echo '</div>';
            }
            echo '  </div>';
            echo '  <div class="drm-actions">';
            echo '    <button type="submit" class="drm-btn drm-btn--primary" id="drm-next-btn" disabled>';
            echo '      Continue <span class="drm-btn-arrow">&#8594;</span>';
            echo '    </button>';
            echo '  </div>';
            echo '</form>';
        }

        echo '    </div>';
        echo '  </div>';
        echo '</div>';
    }

    // ── State 2: Confirmation ────────────────────────────────────────────────
    private function renderConfirm($db, $role)
    {
        $roleId  = $role->id;
        $roleIdQ = $db->quote($roleId);

        $totalActions    = (int) $db->getOne("SELECT COUNT(*) FROM acl_actions WHERE deleted = 0");
        $currentDisabled = (int) $db->getOne("SELECT COUNT(*) FROM acl_roles_actions WHERE role_id = '{$roleIdQ}' AND access_override IN (-98,-99) AND deleted = 0");

        $result  = $db->query("SELECT DISTINCT category FROM acl_actions WHERE deleted = 0 ORDER BY category");
        $modules = [];
        while ($row = $db->fetchByAssoc($result)) {
            $modules[] = $row['category'];
        }

        $roleName = htmlspecialchars($role->name);
        $roleDesc = htmlspecialchars($role->description ?? '');
        $backUrl  = "index.php?module=ACLRoles&action=DetailView&record=" . urlencode($roleId);

        echo $this->stepBar(2);
        echo '<div class="drm-wrap drm-wrap--danger">';
        echo '  <div class="drm-card drm-card--danger">';

        // Danger header
        echo '    <div class="drm-danger-header">';
        echo '      <div class="drm-danger-icon">&#9888;</div>';
        echo '      <div>';
        echo '        <div class="drm-danger-label">DANGER ZONE</div>';
        echo '        <h2 class="drm-danger-title">Disable All Permissions</h2>';
        echo '        <p class="drm-danger-sub">This action will lock out <em>all access</em> for this role across every module.</p>';
        echo '      </div>';
        echo '    </div>';

        echo '    <div class="drm-card__body">';

        // Role badge
        echo '    <div class="drm-role-badge">';
        echo '      <span class="drm-role-badge__label">Target Role</span>';
        echo '      <span class="drm-role-badge__name">' . $roleName . '</span>';
        if ($roleDesc) {
            echo '      <span class="drm-role-badge__desc">' . $roleDesc . '</span>';
        }
        echo '    </div>';

        // Stats
        echo '    <div class="drm-stats">';
        $this->statCard($totalActions,    'Total Actions',     '--clr-stat-blue');
        $this->statCard(count($modules),  'Modules Affected',  '--clr-stat-orange');
        $this->statCard($currentDisabled, 'Already Disabled',  '--clr-stat-red');
        echo '    </div>';

        // Module chips
        echo '    <div class="drm-section">';
        echo '      <div class="drm-section__label">Modules that will be disabled</div>';
        echo '      <div class="drm-chips">';
        foreach ($modules as $mod) {
            $isEc = str_starts_with($mod, 'EC_');
            echo '<span class="drm-chip' . ($isEc ? ' drm-chip--ec' : '') . '">' . htmlspecialchars($mod) . '</span>';
        }
        echo '      </div>';
        echo '    </div>';

        // Warning notice
        echo '    <div class="drm-notice">';
        echo '      <span class="drm-notice__icon">&#8505;</span>';
        echo '      Only role &ldquo;<strong>' . $roleName . '</strong>&rdquo; is affected. No other roles will be changed.';
        echo '    </div>';

        // Fixed permission mapping
        echo '    <div class="drm-section">';
        echo '      <div class="drm-section__label">Permission values that will be applied</div>';
        echo '      <div class="drm-perm-map">';
        $map = [
            'access'     => ['value' => '-98', 'label' => 'Disabled', 'desc' => 'Module visible but locked'],
            'delete'     => ['value' => '-99', 'label' => 'None',     'desc' => 'Absolute denial'],
            'edit'       => ['value' => '-99', 'label' => 'None',     'desc' => 'Absolute denial'],
            'export'     => ['value' => '-99', 'label' => 'None',     'desc' => 'Absolute denial'],
            'import'     => ['value' => '-99', 'label' => 'None',     'desc' => 'Absolute denial'],
            'list'       => ['value' => '-99', 'label' => 'None',     'desc' => 'Absolute denial'],
            'massupdate' => ['value' => '-99', 'label' => 'None',     'desc' => 'Absolute denial'],
            'view'       => ['value' => '-99', 'label' => 'None',     'desc' => 'Absolute denial'],
        ];
        foreach ($map as $action => $info) {
            $isNeg99 = $info['value'] === '-99';
            echo '<div class="drm-perm-row">';
            echo '  <code class="drm-perm-action">' . $action . '</code>';
            echo '  <span class="drm-perm-arrow">&#8594;</span>';
            echo '  <span class="drm-perm-val drm-perm-val--' . ($isNeg99 ? '99' : '98') . '">' . $info['value'] . '</span>';
            echo '  <span class="drm-perm-label">' . $info['label'] . '</span>';
            echo '  <span class="drm-perm-desc">' . $info['desc'] . '</span>';
            echo '</div>';
        }
        echo '      </div>';
        echo '    </div>';

        // Actions
        echo '    <div class="drm-actions drm-actions--split">';
        echo '      <a href="' . $backUrl . '" class="drm-btn drm-btn--ghost">&#8592; Cancel</a>';
        echo '      <form method="POST" action="index.php" onsubmit="return drmConfirmSubmit(this)" data-role="' . $roleName . '">';
        echo '        <input type="hidden" name="module"    value="ACLRoles">';
        echo '        <input type="hidden" name="action"    value="DisableRole">';
        echo '        <input type="hidden" name="record"    value="' . htmlspecialchars($roleId) . '">';
        echo '        <input type="hidden" name="confirmed" value="1">';
        echo '        <button type="submit" class="drm-btn drm-btn--danger" id="drm-confirm-btn">';
        echo '          <span id="drm-confirm-text">Disable All Permissions</span>';
        echo '        </button>';
        echo '      </form>';
        echo '    </div>';

        echo '    </div>';
        echo '  </div>';
        echo '</div>';
    }

    // ── State 3: Execute + Result ─────────────────────────────────────────────
    // action=access → -98 (Disabled)
    // all other actions → -99 (None)
    private function renderExecute($db, $role)
    {
        $roleId    = $db->quote($role->id);
        $now       = $db->now();
        $adminName = htmlspecialchars($GLOBALS['current_user']->user_name);
        $roleName  = htmlspecialchars($role->name);

        $totalActions = (int) $db->getOne("SELECT COUNT(*) FROM acl_actions WHERE deleted = 0");

        // STEP 1: UPDATE các row đã tồn tại trong acl_roles_actions
        $db->query("
            UPDATE acl_roles_actions ara
            JOIN acl_actions a ON a.id = ara.action_id
            SET ara.access_override = CASE a.name WHEN 'access' THEN -98 ELSE -99 END,
                ara.date_modified   = {$now},
                ara.deleted         = 0
            WHERE ara.role_id = '{$roleId}'
              AND a.deleted   = 0
        ");

        // STEP 2: INSERT các row chưa tồn tại — UUID sinh từ PHP per-row tránh bug batch UUID()
        $missingResult = $db->query("
            SELECT a.id AS action_id, a.name AS action_name
            FROM acl_actions a
            LEFT JOIN acl_roles_actions ara
                   ON ara.action_id = a.id AND ara.role_id = '{$roleId}'
            WHERE a.deleted = 0
              AND ara.id IS NULL
        ");
        $insertRows = [];
        while ($miss = $db->fetchByAssoc($missingResult)) {
            $newId       = create_guid();
            $accessValue = ($miss['action_name'] === 'access') ? -98 : -99;
            $insertRows[] = "('$newId', '{$roleId}', '{$miss['action_id']}', $accessValue, {$now}, 0)";
        }
        if (!empty($insertRows)) {
            $db->query("
                INSERT INTO acl_roles_actions
                    (id, role_id, action_id, access_override, date_modified, deleted)
                VALUES " . implode(",
", $insertRows)
            );
        }

        $disabledCount = (int) $db->getOne(
            "SELECT COUNT(*) FROM acl_roles_actions
             WHERE role_id = '{$roleId}' AND access_override IN (-98, -99) AND deleted = 0"
        );

        $result  = $db->query("SELECT DISTINCT category FROM acl_actions WHERE deleted = 0 ORDER BY category");
        $modules = [];
        while ($row = $db->fetchByAssoc($result)) {
            $modules[] = $row['category'];
        }

        $backUrl   = "index.php?module=ACLRoles&action=DetailView&record=" . urlencode($role->id);
        $timestamp = date('Y-m-d H:i:s');

        echo $this->stepBar(3);
        echo '<div class="drm-wrap drm-wrap--success">';
        echo '  <div class="drm-card drm-card--success">';

        echo '    <div class="drm-success-header">';
        echo '      <div class="drm-success-icon">&#10003;</div>';
        echo '      <div>';
        echo '        <div class="drm-success-label">COMPLETE</div>';
        echo '        <h2 class="drm-success-title">All Permissions Disabled</h2>';
        echo '        <p class="drm-success-sub">Role &ldquo;<strong>' . $roleName . '</strong>&rdquo; has been fully locked.</p>';
        echo '      </div>';
        echo '    </div>';

        echo '    <div class="drm-card__body">';

        // Stats
        echo '    <div class="drm-stats">';
        $this->statCard($disabledCount,  'Actions Disabled',  '--clr-stat-red');
        $this->statCard($totalActions,   'Total Actions',     '--clr-stat-blue');
        $this->statCard(count($modules), 'Modules Locked',    '--clr-stat-orange');
        echo '    </div>';

        // Audit trail
        echo '    <div class="drm-audit">';
        echo '      <div class="drm-audit__title">Audit Trail</div>';
        echo '      <div class="drm-audit__grid">';
        $this->auditRow('Role', $roleName);
        $this->auditRow('Role ID', '<code>' . htmlspecialchars($role->id) . '</code>');
        $this->auditRow('access action', '<code>-98</code> Disabled');
        $this->auditRow('All other actions', '<code>-99</code> None');
        $this->auditRow('Executed By', $adminName);
        $this->auditRow('Timestamp', $timestamp);
        echo '      </div>';
        echo '    </div>';

        // Module chips
        echo '    <div class="drm-section">';
        echo '      <div class="drm-section__label">Modules locked</div>';
        echo '      <div class="drm-chips">';
        foreach ($modules as $mod) {
            $isEc = str_starts_with($mod, 'EC_');
            echo '<span class="drm-chip drm-chip--locked' . ($isEc ? ' drm-chip--ec' : '') . '">' . htmlspecialchars($mod) . '</span>';
        }
        echo '      </div>';
        echo '    </div>';

        // Next step
        echo '    <div class="drm-notice drm-notice--info">';
        echo '      <span class="drm-notice__icon">&#8505;</span>';
        echo '      Run <a href="index.php?module=Administration&action=DiagnosticRun">Admin &rarr; Repair &rarr; Quick Repair and Rebuild</a> to flush the ACL cache. Affected users must re-login.';
        echo '    </div>';

        // Actions
        echo '    <div class="drm-actions drm-actions--split">';
        echo '      <a href="index.php?module=ACLRoles&action=DisableRole" class="drm-btn drm-btn--ghost">Disable Another Role</a>';
        echo '      <a href="' . $backUrl . '" class="drm-btn drm-btn--primary">View Role &#8594;</a>';
        echo '    </div>';

        echo '    </div>';
        echo '  </div>';
        echo '</div>';
    }

    // ── Step progress bar ─────────────────────────────────────────────────────
    private function stepBar(int $active): string
    {
        $steps = ['Select Role', 'Confirm', 'Done'];
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

    // ── Helpers ───────────────────────────────────────────────────────────────
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
