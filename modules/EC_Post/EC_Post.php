<?php
/**
 *
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 *
 * SuiteCRM is an extension to SugarCRM Community Edition developed by SalesAgility Ltd.
 * Copyright (C) 2011 - 2018 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo and "Supercharged by SuiteCRM" logo. If the display of the logos is not
 * reasonably feasible for technical reasons, the Appropriate Legal Notices must
 * display the words "Powered by SugarCRM" and "Supercharged by SuiteCRM".
 */


class EC_Post extends Basic
{
    public $new_schema = true;
    public $module_dir = 'EC_Post';
    public $object_name = 'EC_Post';
    public $table_name = 'ec_post';
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
    // relationship placeholders
    public $tags;
    public $categories;
    // selector string fields (non-db)
    public $tags_selector;
    public $categories_selector;
    // date fields
    public $published_at;
	
    public function bean_implements($interface)
    {
        switch($interface)
        {
            case 'ACL':
                return true;
        }

        return false;
    }
    public function populateSelectors() {
        // Relationship join tables used by the relationships are named ec_posts_tags / ec_posts_categories
        // Check for the existence of the join table (plural) before attempting to load the relationship
    if (!empty($this->id) && $this->tableExists('ec_posts_tags') && $this->load_relationship('tags')) {
            try {
                $ids = array();
                $rels = $this->tags->getBeans();
                foreach ($rels as $b) {
                    $ids[] = $b->id;
                }
                $this->tags_selector = implode(',', $ids);
            } catch (Exception $e) {
                $GLOBALS['log']->warn('EC_Post::populateSelectors - tags relationship failed: ' . $e->getMessage());
                $this->tags_selector = '';
            }
        } else {
            $this->tags_selector = '';
        }

        // Categories
    if (!empty($this->id) && $this->tableExists('ec_posts_categories') && $this->load_relationship('categories')) {
            try {
                $ids = array();
                $rels = $this->categories->getBeans();
                foreach ($rels as $b) {
                    $ids[] = $b->id;
                }
                $this->categories_selector = implode(',', $ids);
            } catch (Exception $e) {
                $GLOBALS['log']->warn('EC_Post::populateSelectors - categories relationship failed: ' . $e->getMessage());
                $this->categories_selector = '';
            }
        } else {
            $this->categories_selector = '';
        }
    }

    public function retrieve($id = -1, $encode = true, $deleted = true) {
        $ret = parent::retrieve($id, $encode, $deleted);
        if (empty($ret)) {
            // Debug: log failure to retrieve
            if (!empty($id)) {
                $GLOBALS['log']->fatal("EC_Post::retrieve failed for id={$id} (maybe deleted or no ACL). encode={$encode} deleted={$deleted}");
            } else {
                $GLOBALS['log']->fatal("EC_Post::retrieve called with empty id");
            }
            return $ret;
        }
        $this->populateSelectors();
        return $ret;
    }

    public function save($check_notify = false) {
        // Temporary debug: log incoming selectors to help diagnose missing selections
        try {
            $logData = array(
                'id' => isset($this->id) ? $this->id : null,
                'tags_selector_bean' => isset($this->tags_selector) ? $this->tags_selector : null,
                'categories_selector_bean' => isset($this->categories_selector) ? $this->categories_selector : null,
                'tags_selector_request' => isset($_REQUEST['tags_selector']) ? $_REQUEST['tags_selector'] : null,
                'categories_selector_request' => isset($_REQUEST['categories_selector']) ? $_REQUEST['categories_selector'] : null,
            );
            $GLOBALS['log']->debug('EC_Post::save incoming selectors: ' . print_r($logData, true));
        } catch (Exception $e) {
            // swallow logging errors
        }
        // normalize published_at to DB datetime format if provided as a date string
        if (!empty($this->published_at)) {
            $raw = trim($this->published_at);
            $normalized = '';

            // Fast path: already in YYYY-MM-DD or YYYY-MM-DD HH:MM(:SS)
            if (preg_match('/^\d{4}-\d{2}-\d{2}(?:\s+\d{2}:\d{2}(?:(:\d{2})?)?)?$/', $raw)) {
                // ensure seconds present
                if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}$/', $raw)) {
                    $normalized = $raw . ':00';
                } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
                    $normalized = $raw . ' 00:00:00';
                } else {
                    $normalized = $raw;
                }
            }

            // Try strtotime fallback
            if (empty($normalized)) {
                $ts = strtotime($raw);
                if ($ts !== false && $ts !== -1) {
                    $normalized = date('Y-m-d H:i:s', $ts);
                }
            }

            // Try common explicit formats
            if (empty($normalized)) {
                $formats = array('d-m-Y H:i', 'd/m/Y H:i', 'd-m-Y', 'd/m/Y', 'Y/m/d H:i', 'Y/m/d');
                foreach ($formats as $fmt) {
                    $dt = DateTime::createFromFormat($fmt, $raw);
                    if ($dt !== false) {
                        $normalized = $dt->format('Y-m-d H:i:s');
                        break;
                    }
                }
            }

            // As a last attempt, try the framework TimeDate->to_db which may handle localization
            if (empty($normalized)) {
                try {
                    if (!class_exists('TimeDate')) {
                        require_once('include/timeDate.php');
                    }
                    $td = new TimeDate();
                    $dbDate = $td->to_db($raw);
                    if ($dbDate) $normalized = $dbDate;
                } catch (Exception $e) {
                    $GLOBALS['log']->warn('TimeDate->to_db failed for published_at: ' . $e->getMessage());
                }
            }

            if (!empty($normalized)) {
                // Force published_at to the start of the day (00:00:00) for the selected date.
                try {
                    $dt = new DateTime($normalized);
                    $dt->setTime(0, 0, 0);
                    $this->published_at = $dt->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    // fallback: if DateTime fails, use the normalized value as-is
                    $this->published_at = $normalized;
                }
            } else {
                $GLOBALS['log']->error('convert: Conversion of ' . $raw . ' to Y-m-d H:i:s failed');
            }
        }

        $ret = parent::save($check_notify);

        // After save, synchronize selectors -> relationships but be defensive: if the underlying
        // join tables are missing, skip the sync and log a warning instead of letting it raise a fatal DB error.
        // Tags
        // Tags
        // Only attempt to sync tags if the join table exists (ec_posts_tags). This avoids fatal SQL when the join
        // table hasn't been created.
        if (!empty($this->id) && $this->tableExists('ec_posts_tags')) {
            // fallback to request if needed
            if (empty($this->tags_selector) && !empty($_REQUEST['tags_selector'])) {
                $this->tags_selector = trim($_REQUEST['tags_selector']);
            }
            $ids = array_filter(array_map('trim', explode(',', $this->tags_selector)));
            try {
                // perform direct DB sync on join table: delete existing, insert new
                $db = $GLOBALS['db'];
                $postId = $db->quote($this->id);
                // delete existing
                $sqlDel = "DELETE FROM ec_posts_tags WHERE post_id='" . $postId . "'";
                $db->query($sqlDel, true, "Error clearing ec_posts_tags for post {$this->id}");

                // insert new rows
                foreach ($ids as $tid) {
                    $tid = trim($tid);
                    if (empty($tid)) continue;
                    $rowId = create_guid();
                    $sqlIns = sprintf("INSERT INTO ec_posts_tags (id, post_id, tag_id, date_modified, deleted) VALUES('%s', '%s', '%s', %s, 0)",
                        $db->quote($rowId), $postId, $db->quote($tid), $db->convert('now()', 'datetime'));
                    $db->query($sqlIns, true, "Error inserting ec_posts_tags row for post {$this->id} tag {$tid}");
                }
            } catch (Exception $e) {
                $GLOBALS['log']->warn('EC_Post::save - tags DB sync failed: ' . $e->getMessage());
            }
        } elseif (!empty($this->tags_selector)) {
            $GLOBALS['log']->warn('EC_Post::save - skipping tags sync because table ec_posts_tags is missing');
        }

        // Categories
        if (!empty($this->id) && $this->tableExists('ec_posts_categories')) {
            // fallback to request if needed
            if (empty($this->categories_selector) && !empty($_REQUEST['categories_selector'])) {
                $this->categories_selector = trim($_REQUEST['categories_selector']);
            }
            $ids = array_filter(array_map('trim', explode(',', $this->categories_selector)));
            try {
                $db = $GLOBALS['db'];
                $postId = $db->quote($this->id);
                $sqlDel = "DELETE FROM ec_posts_categories WHERE post_id='" . $postId . "'";
                $db->query($sqlDel, true, "Error clearing ec_posts_categories for post {$this->id}");
                foreach ($ids as $cid) {
                    $cid = trim($cid);
                    if (empty($cid)) continue;
                    $rowId = create_guid();
                    $sqlIns = sprintf("INSERT INTO ec_posts_categories (id, post_id, category_id, date_modified, deleted) VALUES('%s', '%s', '%s', %s, 0)",
                        $db->quote($rowId), $postId, $db->quote($cid), $db->convert('now()', 'datetime'));
                    $db->query($sqlIns, true, "Error inserting ec_posts_categories row for post {$this->id} category {$cid}");
                }
            } catch (Exception $e) {
                $GLOBALS['log']->warn('EC_Post::save - categories DB sync failed: ' . $e->getMessage());
            }
        } elseif (!empty($this->categories_selector)) {
            $GLOBALS['log']->warn('EC_Post::save - skipping categories sync because table ec_posts_categories is missing');
        }

        return $ret;
    }

    /**
     * Helper to verify a table exists in the current DB. Defensive helper used to avoid
     * running relationship queries when the join table has not been created.
     *
     * @param string $table
     * @return bool
     */
    protected function tableExists($table)
    {
        try {
            $sql = "SHOW TABLES LIKE '" . addslashes($table) . "'";
            $result = $GLOBALS['db']->query($sql);
            if ($result) {
                $row = $GLOBALS['db']->fetchByAssoc($result);
                return !empty($row);
            }
        } catch (Exception $e) {
            $GLOBALS['log']->warn('EC_Post::tableExists failed for ' . $table . ' : ' . $e->getMessage());
        }
        return false;
    }
}