<?php
// Bỏ dòng guard này — không phù hợp khi gọi từ {php} block
// if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class EC_Post_DisplayTaxonomyHelper
{
    public static function render($postId, $type = 'categories')
    {
        if (empty($postId)) return '';
        
        $post = BeanFactory::getBean('EC_Post', $postId);
        if (!$post || empty($post->id)) {
            if (!empty($GLOBALS['log'])) {
                $GLOBALS['log']->debug("EC_Post_DisplayTaxonomyHelper::render - missing post for id={$postId}");
            }
            return '';
        }

        $db = DBManagerFactory::getInstance();
    $out = '';
    $rendered = false;

        // Cấu hình theo type
        $config = array(
            'categories' => array(
                'join_table'    => 'ec_posts_categories',
                'join_key'      => 'category_id',
                'relationship'  => 'categories',
                'bean_module'   => 'EC_Post_Categories',
            ),
            'tags' => array(
                'join_table'    => 'ec_posts_tags',
                'join_key'      => 'tag_id',
                'relationship'  => 'tags',
                'bean_module'   => 'EC_Post_Tags',
            ),
        );

        if (!isset($config[$type])) return '';
        $cfg = $config[$type];

        // Thử dùng relationship API trước
        if (!empty($GLOBALS['log'])) {
            $GLOBALS['log']->debug("EC_Post_DisplayTaxonomyHelper::render start post={$postId} type={$type} join_table={$cfg['join_table']} relationship={$cfg['relationship']}");
        }
        try {
            if ($db->tableExists($cfg['join_table']) 
                && $post->load_relationship($cfg['relationship'])) 
            {
                $beans = $post->get_linked_beans(
                    $cfg['relationship'], 
                    $cfg['bean_module']
                );
                if (!empty($beans) && is_array($beans)) {
                    // collect ids for logging
                    $beanIds = array();
                    foreach ($beans as $bb) {
                        if (!empty($bb->id)) $beanIds[] = $bb->id;
                    }
                    if (!empty($GLOBALS['log'])) {
                        $GLOBALS['log']->debug("EC_Post_DisplayTaxonomyHelper::render - method=api beans_count=" . count($beanIds) . " ids=" . json_encode($beanIds));
                    }
                    // Build pill-style links for nicer display
                    $items = array();
                    foreach ($beans as $b) {
                        $items[] = array(
                            'id' => $b->id ?? '',
                            'name' => $b->name ?? '',
                            'slug' => $b->slug ?? '',
                        );
                    }
                    $out .= self::renderItemsHtml($items, $type, $cfg['bean_module']);
                    $rendered = true;
                }
            }
        } catch (Exception $e) {
            if (!empty($GLOBALS['log'])) {
                $GLOBALS['log']->debug("EC_Post_DisplayTaxonomyHelper::render - api exception: " . $e->getMessage());
            }
            // fallthrough to SQL fallback
        }

        // Fallback: query trực tiếp bảng trung gian
        if (!$rendered) {
            if (empty($db->tableExists($cfg['join_table']))) {
                if (!empty($GLOBALS['log'])) {
                    $GLOBALS['log']->debug("EC_Post_DisplayTaxonomyHelper::render - join_table_missing {$cfg['join_table']}");
                }
            }
        }

        // Fallback: query trực tiếp bảng trung gian
        if (!$rendered && $db->tableExists($cfg['join_table'])) {
            // Kiểm tra column deleted có tồn tại không
            $hasDeleted = false;
            try {
                // Use SHOW COLUMNS as a safe fallback — some DBManager implementations
                // don't provide a columnExists() helper.
                $checkSql = "SHOW COLUMNS FROM `" . $cfg['join_table'] . "` LIKE 'deleted'";
                $checkRes = $db->query($checkSql);
                if (!empty($checkRes) && $db->fetchByAssoc($checkRes)) {
                    $hasDeleted = true;
                }
            } catch (Exception $e) {
                // ignore and treat as not having the column
                $hasDeleted = false;
            }
            $deletedClause = $hasDeleted ? " AND deleted = 0" : "";

            $sql = "SELECT " . $cfg['join_key'] . " 
                    FROM " . $cfg['join_table'] . " 
                    WHERE post_id = '" . $db->quote($postId) . "'" 
                    . $deletedClause;

            if (!empty($GLOBALS['log'])) {
                $GLOBALS['log']->debug("EC_Post_DisplayTaxonomyHelper::render - fallback sql=" . $sql);
            }

            $res  = $db->query($sql);
            $rows = array();
            while ($row = $db->fetchByAssoc($res)) {
                $id = $row[$cfg['join_key']] ?? '';
                if (!empty($id)) $rows[] = $id;
            }

            if (!empty($GLOBALS['log'])) {
                $GLOBALS['log']->debug("EC_Post_DisplayTaxonomyHelper::render - method=fallback join_ids=" . json_encode($rows));
            }

            if (!empty($rows)) {
                $items = array();
                foreach ($rows as $id) {
                    $b = BeanFactory::getBean($cfg['bean_module'], $id);
                    if (!$b || empty($b->id)) continue;
                    $items[] = array(
                        'id' => $b->id,
                        'name' => $b->name ?? '',
                        'slug' => $b->slug ?? '',
                    );
                }
                if (!empty($items)) {
                    $out .= self::renderItemsHtml($items, $type, $cfg['bean_module']);
                }
            }
        }

        return $out ?: '<em>Chưa có dữ liệu</em>';
    }

    /**
     * Render array of taxonomy items into pill-style HTML
     * @param array $items each item is ['id'=>..., 'name'=>..., 'slug'=>...]
     * @param string $type 'categories'|'tags'
     * @param string $module module name for link generation
     * @return string
     */
    protected static function renderItemsHtml(array $items, $type, $module = '')
    {
        if (empty($items)) return '';

        // type-specific colors
        $bg = ($type === 'tags') ? '#e6f7ef' : '#e9f2ff';
        $color = ($type === 'tags') ? '#0a6f3a' : '#0b56b6';

        $html = '<div class="ec-post-taxonomy ec-post-taxonomy--' . htmlspecialchars($type, ENT_QUOTES) . '">';
        foreach ($items as $it) {
            $id = htmlspecialchars($it['id'] ?? '', ENT_QUOTES);
            $name = htmlspecialchars($it['name'] ?? '', ENT_QUOTES);
            $slug = htmlspecialchars($it['slug'] ?? '', ENT_QUOTES);

            // Build link to the related record if id and module present
            $href = '';
            if (!empty($id) && !empty($module)) {
                $href = 'index.php?module=' . rawurlencode($module) . '&action=DetailView&record=' . rawurlencode($id);
            }

            $pill = '<span style="display:inline-block;padding:.28rem .6rem;border-radius:999px;background:' . $bg . ';color:' . $color . ';margin-right:6px;margin-bottom:6px;font-size:.92rem;text-decoration:none;line-height:1;">'
                  . ($href ? '<a href="' . $href . '" style="color:' . $color . ';text-decoration:none;">' . $name . '</a>' : $name)
                  . (!empty($slug) ? ' <small style="color:rgba(0,0,0,0.45);font-size:.8rem;">(' . $slug . ')</small>' : '')
                  . '</span>';

            $html .= $pill;
        }
        $html .= '</div>';
        return $html;
    }
}