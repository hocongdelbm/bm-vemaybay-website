<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}



require_once('include/SugarFields/Parsers/Rules/BaseRule.php');

class DocumentsParseRule extends BaseRule
{
    public function __construct() {}




    public function preParse($panels, $view)
    {
        foreach ($panels as $name => $panel) {
            foreach ($panel as $rowCount => $row) {
                foreach ($row as $key => $column) {
                    if ($this->matches($column, '/^related_doc_id$/')) {
                        $panels[$name][$rowCount][$key] = 'related_doc_name';
                    } else {
                        if ($this->matches($column, '/^related_doc_rev_id$/')) {
                            $panels[$name][$rowCount][$key] = ($view == 'EditView') ? 'related_doc_rev_number' : 'related_doc_name';
                        } else {
                            if ($this->matches($column, '/^user_date_format$/')) {
                                $panels[$name][$rowCount][$key] = 'active_date';
                            } else {
                                if ($this->matches($column, '/^is_template_checked$/')) {
                                    $panels[$name][$rowCount][$key] = 'is_template';
                                } else {
                                    if ($this->matches($column, '/^last_rev_creator$/')) {
                                        $panels[$name][$rowCount][$key] = 'last_rev_created_name';
                                    } else {
                                        if ($this->matches($column, '/^last_rev_date$/')) {
                                            $panels[$name][$rowCount][$key] = 'last_rev_create_date';
                                        } else {
                                            if ($this->matches($column, '/^save_file$/')) {
                                                $panels[$name][$rowCount][$key] = 'filename';
                                            } else {
                                                if ($this->matches($column, '/^subcategory$/')) {
                                                    $panels[$name][$rowCount][$key] = 'subcategory_id';
                                                } else {
                                                    if ($this->matches($column, '/^category$/')) {
                                                        $panels[$name][$rowCount][$key] = 'category_id';
                                                    } else {
                                                        if ($this->matches($column, '/^related_document_version$/')) {
                                                            $panels[$name][$rowCount][$key] = 'related_doc_rev_number';
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } //foreach
            } //foreach
        } //foreach
        return $panels;
    }

    public function parsePanels(&$panels, $view)
    {
        foreach ($panels as $name => $panel) {
            foreach ($panel as $rowCount => $row) {
                foreach ($row as $key => $column) {
                    if (
                        $this->matches($column, '/related_doc_id/si') ||
                        $this->matches($column, '/related_doc_rev_id/si') ||
                        $this->matches($column, '/latest_revision/si') ||
                        $this->matches($column, '/file_name/si')
                    ) {
                        $panels[$name][$rowCount][$key] = '';
                    }
                } //foreach
            } //foreach
        } //foreach

        return $panels;
    }
}
