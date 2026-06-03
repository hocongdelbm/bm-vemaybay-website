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
$dictionary['EC_Post'] = array(
    'table' => 'ec_post',
    'audited' => true,
    'inline_edit' => true,
    'duplicate_merge' => true,
    'fields' => array(
        'post_title' => array(
            'required' => true,
            'name' => 'post_title',
            'vname' => 'LBL_POST_TITLE',
            'type' => 'varchar',
            'source' => 'db',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'enabled',
            'duplicate_merge_dom_value' => '1',
            'audited' => 1,
            'reportable' => 1,
            'len' => 255,
        ),
        'post_content' => array(
            'required' => false,
            'name' => 'post_content',
            'vname' => 'LBL_POST_CONTENT',
            'type' => 'text',
            'source' => 'db',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 1,
            'reportable' => 0,
        ),
        'post_status' => array(
            'required' => false,
            'name' => 'post_status',
            'vname' => 'LBL_POST_STATUS',
            'type' => 'enum',
            'options' => 'post_status_list',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => 1,
            'reportable' => 0,
            'default' => 'draft',
            'studio' => 'visible',
            'dependency' => false,
        ),
        'post_type' => array(
            'required' => false,
            'name' => 'post_type',
            'vname' => 'LBL_POST_TYPE',
            'type' => 'enum',
            'options' => 'post_type_list',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => 1,
            'reportable' => 0,
            'default' => 'post',
            'studio' => 'visible',
            'dependency' => false,
        ),
        'slug' => array(
            'required' => false,
            'name' => 'slug',
            'vname' => 'LBL_SLUG',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 1,
            'reportable' => 0,
            'len' => 255,
        ),
        'published_at' => array(
            'required' => false,
            'name' => 'published_at',
            'vname' => 'LBL_PUBLISHED_AT',
            'type' => 'datetime',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 1,
            'reportable' => 0,
        ),
        'thumbnail_url' => array(
            'required' => false,
            'name' => 'thumbnail_url',
            'vname' => 'LBL_THUMBNAIL_URL',
            'type' => 'varchar',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 1,
            'reportable' => 0,
            'len' => 500,
        ),
        'post_parent_id' => array(
            'required' => false,
            'name' => 'post_parent_id',
            'vname' => 'LBL_POST_PARENT',
            'type' => 'id',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 1,
            'reportable' => 0,
        ),
        'author_id' => array(
            'required' => false,
            'name' => 'author_id',
            'vname' => 'LBL_AUTHOR',
            'type' => 'id',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 1,
            'reportable' => 0,
        ),
        // relationship links — metadata không áp dụng cho link fields
        'categories' => array(
            'name' => 'categories',
            'type' => 'link',
            'relationship' => 'ec_post_categories',
            'source' => 'non-db',
        ),
        'tags' => array(
            'name' => 'tags',
            'type' => 'link',
            'relationship' => 'ec_post_tags',
            'source' => 'non-db',
        ),
        // non-db selector fields
        'categories_selector' => array(
            'required' => false,
            'name' => 'categories_selector',
            'vname' => 'LBL_CATEGORIES_SELECTOR',
            'type' => 'varchar',
            'source' => 'non-db',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 0,
            'reportable' => 0,
            'len' => 1024,
        ),
        'tags_selector' => array(
            'required' => false,
            'name' => 'tags_selector',
            'vname' => 'LBL_TAGS_SELECTOR',
            'type' => 'varchar',
            'source' => 'non-db',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'true',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'audited' => 0,
            'reportable' => 0,
            'len' => 1024,
        ),
        'categories_display' => array(
            'name' => 'categories_display',
            'vname' => 'LBL_CATEGORIES',
            'type' => 'varchar',
            'source' => 'non-db',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'reportable' => 0,
            'audited' => 0,
            'len'=> 0,

        ),
        'tags_display' => array(
            'name' => 'tags_display',
            'vname' => 'LBL_TAGS',
            'type' => 'varchar',
            'source' => 'non-db',
            'massupdate' => 0,
            'comments' => '',
            'help' => '',
            'importable' => 'false',
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '',
            'reportable' => 0,
            'audited' => 0,
            'len' => 0,
        ),
    ),
    'indices' => array(
        array('name' => 'idx_posts_name', 'type' => 'index', 'fields' => array('name')),
        array('name' => 'idx_posts_slug', 'type' => 'index', 'fields' => array('slug')),
    ),
    'relationships' => array(
        'ec_post_categories' => array(
            'lhs_module' => 'EC_Post',
            'lhs_table' => 'ec_post',
            'lhs_key' => 'id',
            'rhs_module' => 'EC_Post_Categories',
            'rhs_table' => 'ec_post_categories',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'ec_posts_categories',
            'join_key_lhs' => 'post_id',
            'join_key_rhs' => 'category_id',
        ),
        'ec_post_tags' => array(
            'lhs_module' => 'EC_Post',
            'lhs_table' => 'ec_post',
            'lhs_key' => 'id',
            'rhs_module' => 'EC_Post_Tags',
            'rhs_table' => 'ec_post_tags',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'ec_posts_tags',
            'join_key_lhs' => 'post_id',
            'join_key_rhs' => 'tag_id',
        ),
    ),
    'optimistic_locking' => true,
    'unified_search' => true,
);
if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('EC_Post', 'EC_Post', array('basic', 'assignable', 'security_groups'));