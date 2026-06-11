<?php
if (!defined('sugarEntry')) define('sugarEntry', true);
class EC_PostHooks {
    public function afterRelationshipAdd($bean, $event, $arguments) {
        $this->updateCounter($arguments);
    }
    public function afterRelationshipDelete($bean, $event, $arguments) {
        $this->updateCounter($arguments);
    }
    protected function updateCounter($arguments) {
        if (empty($arguments['link'])) return;
        $link = $arguments['link'];
        if ($link == 'ec_post_categories' || $link == 'categories') {
            $taxBean = BeanFactory::getBean('EC_Post_Categories', $arguments['related_id']);
            $linkName = 'categories';
        } elseif ($link == 'ec_post_tags' || $link == 'tags') {
            $taxBean = BeanFactory::getBean('EC_Post_Tags', $arguments['related_id']);
            $linkName = 'tags';
        } else {
            return;
        }
    if (!$taxBean) return;
    if (empty($arguments['related_id'])) return;
        // recount posts
        // use canonical link name on the taxonomy bean
        $count = 0;
        if (!empty($linkName)) {
            // Only attempt if join table exists
            $joinTable = ($linkName == 'categories') ? 'ec_posts_categories' : 'ec_posts_tags';
            if (DBManagerFactory::getInstance()->tableExists($joinTable) && $taxBean->load_relationship($linkName)) {
                $beans = $taxBean->get_linked_beans($linkName, 'EC_Post');
                if (is_array($beans)) $count = count($beans);
            }
        }
        $taxBean->post_count = $count;
        $taxBean->save();
    }

    /**
     * Before save hook to auto-generate slug from title (remove diacritics, spaces -> '-')
     * and ensure uniqueness by appending -2, -3... when necessary.
     */
    public function beforeSaveGenerateSlug($bean, $event, $arguments) {
        // if slug already provided, normalize it
        $titleField = 'post_title';
        $slugField = 'slug';

        $slug = isset($bean->$slugField) ? trim($bean->$slugField) : '';
        $title = isset($bean->$titleField) ? trim($bean->$titleField) : '';

        if (empty($slug) && !empty($title)) {
            $slug = $this->slugify($title);
        } else {
            $slug = $this->slugify($slug);
        }

        // ensure uniqueness
        $unique = $this->ensureUniqueSlug($bean, $slug);
        $bean->$slugField = $unique;
    }

    protected function slugify($text) {
        // remove BOM and trim
        $text = trim(preg_replace('/\x{FEFF}/u', '', $text));
        // replace non-letter or digits by -
        // Normalize unicode to NFD to separate diacritics
        if (class_exists('Normalizer')) {
            $text = Normalizer::normalize($text, Normalizer::FORM_D);
        }
        // remove diacritics
        $text = preg_replace('~[\p{Mn}]~u', '', $text);
        // replace any non alnum with -
        $text = preg_replace('/[^A-Za-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        $text = strtolower($text);
        if ($text === '') return 'n-a';
        return $text;
    }

    protected function ensureUniqueSlug($bean, $baseSlug) {
        $db = DBManagerFactory::getInstance();
        $moduleTable = $bean->table_name;
        $id = isset($bean->id) ? $bean->id : '';

        $slug = $baseSlug;
        $i = 1;
        while (true) {
            $sql = "SELECT id FROM {$moduleTable} WHERE slug = '" . $db->quote($slug) . "' AND deleted = 0";
            if (!empty($id)) {
                $sql .= " AND id != '" . $db->quote($id) . "'";
            }
            $res = $db->query($sql);
            $row = $db->fetchByAssoc($res);
            if (!$row) break;
            $i++;
            $slug = $baseSlug . '-' . $i;
        }
        return $slug;
    }
}
