<?php
$hook_version = 1;
$hook_array = Array();

$hook_array['after_relationship_add'][] = Array(
    1,
    'update_taxonomy_count_on_add',
    'custom/modules/EC_Post/EC_PostHooks.php',
    'EC_PostHooks',
    'afterRelationshipAdd'
);
$hook_array['after_relationship_delete'][] = Array(
    1,
    'update_taxonomy_count_on_delete',
    'custom/modules/EC_Post/EC_PostHooks.php',
    'EC_PostHooks',
    'afterRelationshipDelete'
);

$hook_array['before_save'][] = Array(
    1,
    'generate_and_validate_slug',
    'custom/modules/EC_Post/EC_PostHooks.php',
    'EC_PostHooks',
    'beforeSaveGenerateSlug'
);

return;
