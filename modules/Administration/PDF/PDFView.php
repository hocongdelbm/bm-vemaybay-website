<?php
namespace SuiteCRM\Modules\Administration\PDF;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

use SuiteCRM\Modules\Administration\PDF\MVC\View as AbstractView;
use SuiteCRM\PDF\PDFWrapper;

/**
 * Class View renders the PDF settings.
 */
class PDFView extends AbstractView
{
    public function __construct()
    {
        parent::__construct(__DIR__ . '/view.tpl');
    }

    public function preDisplay(): void
    {
        parent::preDisplay();

        $this->smarty->assign('selectedController', PDFWrapper::getController());
        $this->smarty->assign('selectedEngine', PDFWrapper::getDefaultEngine());
        $engines = $this->getEngines();

        $this->smarty->assign('engines', [
            translate('LBL_PDF_WRAPPER_ENGINES') => $engines
        ]);
    }

    /**
     * @see SugarView::display()
     */
    public function display(): void
    {
        global $mod_strings, $app_strings;

        $this->smarty->assign('APP', $app_strings);
        $this->smarty->assign('MOD', $mod_strings);

        $template = $this->templateFile;
        if (file_exists('custom/' . $this->templateFile)) {
            $template = 'custom/' . $this->templateFile;
        }

        $this->smarty->display($template);
    }
}
