<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('include/SugarObjects/templates/file/File.php');


// User is used to store Forecast information.
class Document extends File
{
    public $id;
    public $document_name;
    public $description;
    public $category_id;
    public $subcategory_id;
    public $status_id;
    public $status;
    public $created_by;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $assigned_user_id;
    public $active_date;
    public $exp_date;
    public $document_revision_id;
    public $filename;
    public $doc_type;

    public $img_name;
    public $img_name_bare;
    public $related_doc_id;
    public $related_doc_name;
    public $related_doc_rev_id;
    public $related_doc_rev_number;
    public $is_template;
    public $template_type;

    //additional fields.
    public $revision;
    public $last_rev_create_date;
    public $last_rev_created_by;
    public $last_rev_created_name;
    public $file_url;
    public $file_url_noimage;

    public $table_name = "documents";
    public $object_name = "Document";
    public $user_preferences;

    public $encodeFields = array();

    // This is used to retrieve related fields from form posts.
    public $additional_column_fields = array('revision');

    public $new_schema = true;
    public $module_dir = 'Documents';

    public $relationship_fields = array(
        'contract_id' => 'contracts',
    );

    public $authenticated;
    public $show_preview = false;

    public function __construct()
    {
        parent::__construct();
        $this->setupCustomFields('Documents'); //parameter is module name
        $this->disable_row_level_security = false;
    }




    public function save($check_notify = false)
    {
        if (empty($this->doc_type)) {
            $this->doc_type = 'Sugar';
        }
        if (empty($this->id) || $this->new_with_id) {
            if (empty($this->id)) {
                $this->id = create_guid();
                $this->new_with_id = true;
            }

            if (isset($_REQUEST) && isset($_REQUEST['duplicateSave']) && $_REQUEST['duplicateSave'] == true && isset($_REQUEST['filename_old_doctype'])) {
                $this->doc_type = $_REQUEST['filename_old_doctype'];
                $isDuplicate = true;
            } else {
                $isDuplicate = false;
            }

            $Revision = BeanFactory::newBean('DocumentRevisions');
            //save revision.
            $Revision->in_workflow = true;
            $Revision->not_use_rel_in_req = true;
            $Revision->new_rel_id = $this->id;
            $Revision->new_rel_relname = 'Documents';
            $Revision->change_log = translate('DEF_CREATE_LOG', 'Documents');
            $Revision->revision = $this->revision;
            $Revision->document_id = $this->id;
            $Revision->filename = $this->filename;

            if (isset($this->file_ext)) {
                $Revision->file_ext = $this->file_ext;
            }

            if (isset($this->file_mime_type)) {
                $Revision->file_mime_type = $this->file_mime_type;
            }

            $Revision->doc_type = $this->doc_type;
            if (isset($this->doc_id)) {
                $Revision->doc_id = $this->doc_id;
            }
            if (isset($this->doc_url)) {
                $Revision->doc_url = $this->doc_url;
            }

            $Revision->id = create_guid();
            $Revision->new_with_id = true;

            $createRevision = false;
            //Move file saved during populatefrompost to match the revision id rather than document id
            if (!empty($_FILES['filename_file'])) {
                rename("upload://{$this->id}", "upload://{$Revision->id}");
                $createRevision = true;
            } else {
                if ($isDuplicate && (empty($this->doc_type) || $this->doc_type == 'Sugar')) {
                    // Looks like we need to duplicate a file, this is tricky
                    $oldDocument = BeanFactory::newBean('Documents');
                    $oldDocument->retrieve($_REQUEST['duplicateId']);
                    $old_name = "upload://{$oldDocument->document_revision_id}";
                    $new_name = "upload://{$Revision->id}";
                    $GLOBALS['log']->debug("Attempting to copy from $old_name to $new_name");
                    copy($old_name, $new_name);
                    $createRevision = true;
                }
            }

            // For external documents, we just need to make sure we have a doc_id
            if (!empty($this->doc_id) && $this->doc_type != 'Sugar') {
                $createRevision = true;
            }

            if ($createRevision) {
                $Revision->save();
                //update document with latest revision id
                $this->process_save_dates = false; //make sure that conversion does not happen again.
                $this->document_revision_id = $Revision->id;

                // Luôn kiểm tra và cập nhật preview khi có file mới
                if (!empty($this->filename)) {
                    $file_path = "upload://{$this->document_revision_id}";
                    if (file_exists($file_path)) {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime_type = finfo_file($finfo, $file_path);
                        finfo_close($finfo);

                        $preview_path = "upload://{$this->id}_preview_image";

                        if (strpos($mime_type, 'image/') === 0) {
                            // File mới LÀ ảnh → Tạo/Cập nhật preview
                            copy($file_path, $preview_path);
                            $this->preview_image = $this->id . '_preview_image';
                            $GLOBALS['log']->fatal("REVISION: Image detected, set preview_image = {$this->preview_image}");
                        } else {
                            // File mới KHÔNG phải ảnh → Xóa preview cũ (nếu có)
                            if (file_exists($preview_path)) {
                                unlink($preview_path);
                                $GLOBALS['log']->fatal("REVISION: Non-image, deleted preview file");
                            }
                            $this->preview_image = '';
                            $GLOBALS['log']->fatal("REVISION: Non-image detected (MIME: {$mime_type}), cleared preview_image");
                        }
                    } else {
                        $GLOBALS['log']->fatal("REVISION: File not found at {$file_path}");
                    }
                } else {
                    $GLOBALS['log']->fatal("REVISION: No filename provided");
                }
            }


            //set relationship field values if contract_id is passed (via subpanel create)
            if (!empty($_POST['contract_id'])) {
                $save_revision['document_revision_id'] = $this->document_revision_id;
                $this->load_relationship('contracts');
                $this->contracts->add($_POST['contract_id'], $save_revision);
            }

            if ((isset($_POST['load_signed_id']) and !empty($_POST['load_signed_id']))) {
                $loadSignedIdQuoted = $this->db->quote($_POST['load_signed_id']);
                $query = "update linked_documents set deleted=1 where id='" . $loadSignedIdQuoted . "'";
                $this->db->query($query);
            }
        } else {
            // XỬ LÝ UPDATE - Khi edit document có sẵn
            if (!empty($_FILES['filename_file'])) {
                // Có upload file mới
                $file_path = "upload://{$this->id}";
                
                if (file_exists($file_path)) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime_type = finfo_file($finfo, $file_path);
                    finfo_close($finfo);
                    
                    $preview_path = "upload://{$this->id}_preview_image";
                    
                    if (strpos($mime_type, 'image/') === 0) {
                        // File mới LÀ ảnh → Update preview
                        copy($file_path, $preview_path);
                        $this->preview_image = $this->id . '_preview_image';
                    } else {
                        // File mới KHÔNG phải ảnh → Xóa preview cũ
                        if (file_exists($preview_path)) {
                            unlink($preview_path);
                        }
                        $this->preview_image = '';
                    }
                }
            }
        }

        return parent::save($check_notify);
    }

    public function get_summary_text()
    {
        return (string)$this->document_name;
    }

    public function is_authenticated()
    {
        if (!isset($this->authenticated)) {
            LoggerManager::getLogger()->warn('Document::$authenticated is not set');
            return null;
        }
        return $this->authenticated;
    }

    public function fill_in_additional_list_fields()
    {
        $this->fill_in_additional_detail_fields();
    }

    public function fill_in_additional_detail_fields()
    {
        global $current_language, $timedate, $locale, $sugar_config;

        parent::fill_in_additional_detail_fields();

        $mod_strings = return_module_language($current_language, 'Documents');

        if (!empty($this->document_revision_id)) {
            $query = "SELECT users.first_name AS first_name, users.last_name AS last_name, document_revisions.date_entered AS rev_date,
            	 document_revisions.filename AS filename, document_revisions.revision AS revision,
            	 document_revisions.file_ext AS file_ext, document_revisions.file_mime_type AS file_mime_type
            	 FROM users, document_revisions
            	 WHERE users.id = document_revisions.created_by AND document_revisions.id = '$this->document_revision_id'";

            $result = $this->db->query($query);
            $row = $this->db->fetchByAssoc($result);

            //populate name
            if (isset($this->document_name)) {
                $this->name = $this->document_name;
            }

            if (isset($row['filename'])) {
                $this->filename = $row['filename'];
            }
            //$this->latest_revision = $row['revision'];
            if (isset($row['revision'])) {
                $this->revision = $row['revision'];
            }

            //image is selected based on the extension name <ext>_icon_inline, extension is stored in document_revisions.
            //if file is not found then default image file will be used.
            global $img_name, $img_name_bare;

            if (!empty($row['file_ext'])) {
                $img_name = SugarThemeRegistry::current()->getImageURL(strtolower($row['file_ext']) . "_image_inline.gif");
                $img_name_bare = strtolower($row['file_ext']) . "_image_inline";

                $allowedPreview = $sugar_config['allowed_preview'] ?? [];

                if (in_array($row['file_ext'], $allowedPreview, true)) {
                    $this->show_preview = true;
                }
            }
        }

        //set default file name.
        if (!empty($img_name) && file_exists($img_name)) {
            $img_name = $img_name_bare;
        } else {
            $img_name = "def_image_inline"; //todo change the default image.
        }
        if ($this->ACLAccess('DetailView')) {
            if (!empty($this->doc_type) && $this->doc_type != 'Sugar' && !empty($this->doc_url)) {
                $file_url = "<a href='" . $this->doc_url . "' target='_blank'>" . SugarThemeRegistry::current()->getImage(
                    $this->doc_type . '_image_inline',
                    'border="0"',
                    null,
                    null,
                    '.png',
                    $mod_strings['LBL_LIST_VIEW_DOCUMENT']
                ) . "</a>";
            } else {
                $file_url = "<a href='index.php?entryPoint=download&id={$this->document_revision_id}&type=Documents' target='_blank'>" . SugarThemeRegistry::current()->getImage(
                    $img_name,
                    'border="0"',
                    null,
                    null,
                    '.gif',
                    $mod_strings['LBL_LIST_VIEW_DOCUMENT']
                ) . "</a>";
            }

            $this->file_url = $file_url;
            $this->file_url_noimage = "index.php?entryPoint=download&type=Documents&id={$this->document_revision_id}";
        } else {
            $this->file_url = "";
            $this->file_url_noimage = "";
        }

        //get last_rev_by user name.
        if (!empty($row)) {
            $this->last_rev_created_name = $locale->getLocaleFormattedName($row['first_name'], $row['last_name']);

            $this->last_rev_create_date = $timedate->to_display_date_time($this->db->fromConvert(
                $row['rev_date'],
                'datetime'
            ));
            $this->last_rev_mime_type = $row['file_mime_type'];
        }

        global $app_list_strings;
        if (!empty($this->status_id)) {
            $this->status = $app_list_strings['document_status_dom'][$this->status_id];
        }
        if (!empty($this->related_doc_id)) {
            $this->related_doc_name = Document::get_document_name($this->related_doc_id);
            $this->related_doc_rev_number = DocumentRevision::get_document_revision_name($this->related_doc_rev_id);
        }
    }

    public function list_view_parse_additional_sections(&$list_form/*, $xTemplateSection*/)
    {
        return $list_form;
    }

    public function create_export_query($order_by, $where, $relate_link_join = '')
    {
        $custom_join = $this->getCustomJoin(true, true, $where);
        $custom_join['join'] .= $relate_link_join;
        $query = "SELECT
						documents.*";
        $query .= $custom_join['select'];
        $query .= " FROM documents ";
        $query .= $custom_join['join'];

        $where_auto = " documents.deleted = 0";

        if ($where != "") {
            $query .= " WHERE $where AND " . $where_auto;
        } else {
            $query .= " WHERE " . $where_auto;
        }

        if ($order_by != "") {
            $query .= " ORDER BY $order_by";
        } else {
            $query .= " ORDER BY documents.document_name";
        }

        return $query;
    }

    public function get_list_view_data()
    {
        global $current_language;
        $app_list_strings = return_app_list_strings_language($current_language);

        $document_fields = $this->get_list_view_array();

        $this->fill_in_additional_list_fields();


        $document_fields['FILENAME'] = $this->filename;
        $document_fields['FILE_URL'] = $this->file_url;
        $document_fields['FILE_URL_NOIMAGE'] = $this->file_url_noimage;
        $document_fields['LAST_REV_CREATED_BY'] = $this->last_rev_created_name;
        if (!isset($app_list_strings['document_category_dom'][$this->category_id])) {
            if (!isset($this->category_id)) {
                LoggerManager::getLogger()->warn('Undefined category id for document list view data.');
            } else {
                LoggerManager::getLogger()->warn('In language app strings[document_category_dom] does not found for category id for document list view data: ' . $this->category_id);
            }
        }

        if (!isset($app_list_strings['document_category_dom'][$this->category_id])) {
            LoggerManager::getLogger()->warn('Category ID is not found in document_category_dom in app_list_string for getting list view date of Document.');
            $appListStringDocumentCategoryDomForThisCategoryId = null;
        } else {
            $appListStringDocumentCategoryDomForThisCategoryId = $app_list_strings['document_category_dom'][$this->category_id];
        }

        if (!isset($app_list_strings['document_category_dom'][$this->category_id])) {
            LoggerManager::getLogger()->warn('Category ID is not found in document_category_dom in app_list_string for getting list view date of Document.');
            $appListStringDocumentCategoryDomForThisSubCategoryId = null;
        } else {
            $appListStringDocumentCategoryDomForThisSubCategoryId = $app_list_strings['document_subcategory_dom'][$this->subcategory_id];
        }

        $document_fields['CATEGORY_ID'] = empty($this->category_id) ? "" : $appListStringDocumentCategoryDomForThisCategoryId;
        $document_fields['SUBCATEGORY_ID'] = empty($this->subcategory_id) ? "" : $appListStringDocumentCategoryDomForThisSubCategoryId;
        $document_fields['NAME'] = $this->document_name;
        $document_fields['DOCUMENT_NAME_JAVASCRIPT'] = DBManagerFactory::getInstance()->quote($document_fields['DOCUMENT_NAME']);

        return $document_fields;
    }


    /**
     * mark_relationships_deleted
     *
     * Override method from SugarBean to handle deleting relationships associated with a Document.  This method will
     * remove DocumentRevision relationships and then optionally delete Contracts depending on the version.
     *
     * @param $id String The record id of the Document instance
     */
    public function mark_relationships_deleted($id)
    {
        $this->load_relationships('revisions');
        $revisions = $this->get_linked_beans('revisions', 'DocumentRevision');

        if (!empty($revisions) && is_array($revisions)) {
            foreach ($revisions as $key => $version) {
                UploadFile::unlink_file($version->id, $version->filename);
                //mark the version deleted.
                $version->mark_deleted($version->id);
            }
        }
        parent::mark_relationships_deleted($id);
    }


    public function bean_implements($interface)
    {
        switch ($interface) {
            case 'ACL':
                return true;
            case 'FILE':
                return true;
        }

        return false;
    }

    //static function.
    public function get_document_name($doc_id)
    {
        if (empty($doc_id)) {
            return null;
        }

        $db = DBManagerFactory::getInstance();
        $query = "select document_name from documents where id='$doc_id'  and deleted=0";
        $result = $db->query($query);
        if (!empty($result)) {
            $row = $db->fetchByAssoc($result);
            if (!empty($row)) {
                return $row['document_name'];
            }
        }

        return null;
    }
}

require_once('modules/Documents/DocumentExternalApiDropDown.php');
