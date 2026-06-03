<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.detail.php');

class EC_Post_TagsViewDetail extends ViewDetail {
    public function display() {
        $tagId = isset($this->bean->id) ? $this->bean->id : '';
        $html = '';
        if (!empty($tagId)) {
            global $db, $timedate;

            $perPage = 5;
            $page = isset($_GET['posts_page']) ? (int)$_GET['posts_page'] : 1;
            if ($page < 1) $page = 1;

            $countSql = "SELECT COUNT(*) AS c FROM ec_post p JOIN ec_posts_tags j ON j.post_id = p.id AND j.deleted = 0 WHERE j.tag_id = '" . $db->quote($tagId) . "' AND p.deleted = 0";
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
                    JOIN ec_posts_tags j ON j.post_id = p.id AND j.deleted = 0
                    LEFT JOIN users u ON p.assigned_user_id = u.id AND u.deleted = 0
                    WHERE j.tag_id = '" . $db->quote($tagId) . "' AND p.deleted = 0
                    ORDER BY p.published_at DESC
                    LIMIT " . intval($perPage) . " OFFSET " . intval($offset);

                $res = $db->query($sql);

                $i = $offset + 1;
                $tbody = '';
                while ($row = $db->fetchByAssoc($res)) {
                    $title  = htmlspecialchars($row['post_title'], ENT_QUOTES);
                    $url    = 'index.php?module=EC_Post&action=DetailView&record=' . $row['id'];
                    $author = isset($row['author_name']) ? htmlspecialchars($row['author_name'], ENT_QUOTES) : '';
                    $pub    = !empty($row['published_at']) ? date('d-m-Y H:i', strtotime($row['published_at'])) : '';
                    $desc   = isset($row['description']) ? htmlspecialchars(strip_tags($row['description'])) : '';

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

                // Pagination
                if ($totalPages > 1) {
                    $baseUrl = htmlspecialchars($_SERVER['PHP_SELF'] . '?' . preg_replace('/(&|\?)?posts_page=\d+/', '', $_SERVER['QUERY_STRING']));
                    $sep     = strpos($baseUrl, '?') === false ? '?' : '&';

                    $pager = '<nav aria-label="Posts pagination" style="width:100%;display:block;">'
                           . '<ul class="pagination" style="display:flex;flex-wrap:wrap;justify-content:center;align-items:center;list-style:none;padding:8px 0 0;margin:0;">';

                    // Prev
                    if ($page > 1) {
                        $pager .= '<li class="page-item" style="margin:2px;">'
                                . '<a class="page-link" href="' . $baseUrl . $sep . 'posts_page=' . ($page - 1) . '" style="padding:4px 10px;">&laquo;</a></li>';
                    }

                    for ($p = 1; $p <= $totalPages; $p++) {
                        $isActive    = ($p == $page);
                        $activeStyle = $isActive ? 'background:#0d6efd;color:#fff;border-color:#0d6efd;' : '';
                        $pager .= '<li class="page-item' . ($isActive ? ' active' : '') . '" style="margin:2px;">'
                                . '<a class="page-link" href="' . $baseUrl . $sep . 'posts_page=' . $p . '" style="padding:4px 10px;' . $activeStyle . '">' . $p . '</a></li>';
                    }

                    // Next
                    if ($page < $totalPages) {
                        $pager .= '<li class="page-item" style="margin:2px;">'
                                . '<a class="page-link" href="' . $baseUrl . $sep . 'posts_page=' . ($page + 1) . '" style="padding:4px 10px;">&raquo;</a></li>';
                    }

                    $pager .= '</ul></nav>';
                    $html .= $pager;
                }
            }
        }

        $js = '<script type="text/javascript">(function(){try{var el=document.getElementById("posts_panel");if(el){var row=el.closest(".detail-view-row-item");if(row){var label=row.querySelector(".label.col-1-label");if(label){label.style.display="none";}var field=row.querySelector(".detail-view-field");if(field){field.style.width="100%";field.style.paddingLeft="0";}}}}catch(e){console && console.log(e);}})();</script>';

        $this->ss->assign('POSTS_PANEL', $html . $js);
        parent::display();
    }
}