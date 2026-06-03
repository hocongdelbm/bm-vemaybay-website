<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.detail.php');

class EC_Post_CategoriesViewDetail extends ViewDetail {
    public function display() {
        $catId = isset($this->bean->id) ? $this->bean->id : '';
        $html = '';
        if (!empty($catId)) {
            global $db, $timedate;

            // Pagination params
            $perPage = 5;
            $page = isset($_GET['posts_page']) ? (int)$_GET['posts_page'] : 1;
            if ($page < 1) $page = 1;

            // Count total
            $countSql = "SELECT COUNT(*) AS c FROM ec_post p JOIN ec_posts_categories j ON j.post_id = p.id AND j.deleted = 0 WHERE j.category_id = '" . $db->quote($catId) . "' AND p.deleted = 0";
            $countRes = $db->query($countSql);
            $countRow = $db->fetchByAssoc($countRes);
            $total = isset($countRow['c']) ? (int)$countRow['c'] : 0;

            if ($total == 0) {
                $html = '<div class="text-left fw-semibold">Không có bài viết nào.</div>';
            } else {
                $totalPages = (int)ceil($total / $perPage);
                if ($page > $totalPages) $page = $totalPages;
                $offset = ($page - 1) * $perPage;

                $sql = "SELECT p.id, p.post_title, p.slug, p.published_at, p.description, CONCAT_WS(' ', u.first_name, u.last_name) AS author_name
                    FROM ec_post p
                    JOIN ec_posts_categories j ON j.post_id = p.id AND j.deleted = 0
                    LEFT JOIN users u ON p.assigned_user_id = u.id AND u.deleted = 0
                    WHERE j.category_id = '" . $db->quote($catId) . "' AND p.deleted = 0
                    ORDER BY p.published_at DESC
                    LIMIT " . intval($perPage) . " OFFSET " . intval($offset);

                $res = $db->query($sql);

                $i = $offset + 1;
                $tbody = '';
                while ($row = $db->fetchByAssoc($res)) {
                    $title = htmlspecialchars($row['post_title'], ENT_QUOTES);
                    $url = 'index.php?module=EC_Post&action=DetailView&record=' . $row['id'];
                    $author = isset($row['author_name']) ? htmlspecialchars($row['author_name'], ENT_QUOTES) : '';
                    $pub = !empty($row['published_at']) ? date('d-m-Y H:i', strtotime($row['published_at'])) : '';
                    $desc = isset($row['description']) ? htmlspecialchars(strip_tags($row['description'])) : '';

                    $tbody .= '<tr>'
                        . '<td class="td-index text-left"><strong>' . $i . '</strong></td>'
                        . '<td class="td-name text-left"><a class="fw-semibold" href="' . $url . '" target="_blank">' . $title . '</a></td>'
                        . '<td class="td-author text-left">' . $author . '</td>'
                        . '<td class="td-date text-left">' . $pub . '</td>'
                        . '<td class="td-description text-left">' . $desc . '</td>'
                        . '</tr>';
                    $i++;
                }

                $html = '<table class="table table-hover m-0">'
                    . '<thead><tr><th>#</th><th>Tiêu đề</th><th>Tác giả</th><th>Ngày</th><th>Mô tả</th></tr></thead>'
                    . '<tbody class="border-bottom-none">' . $tbody . '</tbody></table>';

                // pagination links
                if ($totalPages > 1) {
                    $baseUrl = htmlspecialchars($_SERVER['PHP_SELF'] . '?' . preg_replace('/(&|\?)?posts_page=\\d+/', '', $_SERVER['QUERY_STRING']));
                    $pager = '<nav aria-label="Posts pagination"><ul class="pagination mt-2">';
                    for ($p = 1; $p <= $totalPages; $p++) {
                        $active = ($p == $page) ? ' active' : '';
                        $link = $baseUrl . (strpos($baseUrl, '?') === false ? '?' : '&') . 'posts_page=' . $p;
                        $pager .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $link . '">' . $p . '</a></li>';
                    }
                    $pager .= '</ul></nav>';
                    $html .= $pager;
                }
            }
        }
    // Fallback JS: traverse up from #posts_panel at render time and hide the left label cell for this panel
    $js = '<script type="text/javascript">(function(){try{var el=document.getElementById("posts_panel");if(el){var row=el.closest(".detail-view-row-item");if(row){var label=row.querySelector(".label.col-1-label");if(label){label.style.display="none";}var field=row.querySelector(".detail-view-field");if(field){field.style.width="100%";field.style.paddingLeft="0";}}}}catch(e){console && console.log(e);}})();</script>';

    $this->ss->assign('POSTS_PANEL', $html . $js);
        parent::display();
    }
}
