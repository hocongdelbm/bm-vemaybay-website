<?php
require_once('modules/EC_Post_Categories/EC_Post_Categories.php');

class EC_Post_multiSelectorWidget
{
    // $type: 'categories' or 'tags'
    // $postId: optional post id to load existing selections when $value is empty
    public static function render($fieldName, $value, $type = 'categories', $postId = null)
    {
        // load options
        $options = array();
        if ($type === 'categories') {
            require_once('modules/EC_Post_Categories/EC_Post_Categories.php');
            $bean = new EC_Post_Categories();
            $list = $bean->get_full_list('name');
        } else {
            // tags
            require_once('modules/EC_Post_Tags/EC_Post_Tags.php');
            $bean = new EC_Post_Tags();
            $list = $bean->get_full_list('name');
        }
        if ($list) {
            foreach ($list as $item) {
                $options[$item->id] = htmlspecialchars($item->name, ENT_QUOTES);
            }
        }
        $selected = array();
        if (!empty($value)) {
            $selected = array_filter(array_map('trim', explode(',', $value)));
        }
        // If no explicit value passed and a postId is available, try loading linked beans
        if (empty($selected) && !empty($postId)) {
            try {
                if ($type === 'categories') {
                    require_once('modules/EC_Post_Categories/EC_Post_Categories.php');
                    $postBean = BeanFactory::getBean('EC_Post', $postId);
                    if ($postBean && $postBean->load_relationship('categories')) {
                        $rels = $postBean->get_linked_beans('categories', 'EC_Post_Categories');
                        if (!empty($rels) && is_array($rels)) {
                            foreach ($rels as $r) {
                                if (!empty($r->id)) $selected[] = $r->id;
                            }
                        }
                    }
                } else {
                    require_once('modules/EC_Post_Tags/EC_Post_Tags.php');
                    $postBean = BeanFactory::getBean('EC_Post', $postId);
                    if ($postBean && $postBean->load_relationship('tags')) {
                        $rels = $postBean->get_linked_beans('tags', 'EC_Post_Tags');
                        if (!empty($rels) && is_array($rels)) {
                            foreach ($rels as $r) {
                                if (!empty($r->id)) $selected[] = $r->id;
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                // ignore and render empty
            }
            $selected = array_unique($selected);
        }
        // render a wrapper so dropdown can escape overflow of surrounding containers
        $html = "<div class='ec-multiselect-wrapper' style='position:relative;'>\n";
        // render a hidden input to hold comma-separated ids
        $html .= "  <input type='hidden' name='{$fieldName}' id='{$fieldName}' value='" . htmlspecialchars(implode(',', $selected), ENT_QUOTES) . "' />\n";
        // render select for Choices.js
        $html .= "  <select id='{$fieldName}_select' class='ec-multiselect' multiple data-field='{$fieldName}'>\n";
        foreach ($options as $id => $label) {
            $sel = in_array($id, $selected) ? " selected" : "";
            $html .= "    <option value='" . $id . "'" . $sel . ">" . $label . "</option>\n";
        }
        $html .= "  </select>\n";
        $html .= "</div>\n";
        return $html;
    }
}
