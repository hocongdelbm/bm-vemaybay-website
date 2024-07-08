<?php
namespace SuiteCRM\Modules\Administration\PDF;

use Exception;
use SuiteCRM\Modules\Administration\PDF\MVC\Controller as AbstractController;
use SuiteCRM\PDF\PDFConfigurator;

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Class Controller
 * @package SuiteCRM\Modules\Administration\PDF
 */
class Controller extends AbstractController
{
    public function __construct()
    {
        parent::__construct(new PDFView());
    }

    /**
     * Saves the configuration from a POST request.
     *
     * If called from ajax it will return a json.
     * @throws Exception
     */
    public function doSave(): void
    {
        $PDFEngine = filter_input(INPUT_POST, 'pdf-engine', FILTER_SANITIZE_STRING);

        PDFConfigurator::make()
            ->setEngine($PDFEngine)
            ->save();

        if ($this->isAjax()) {
            $this->yieldJson(['status' => 'success']);
        }

        $this->redirect('index.php?module=Administration&action=index');
    }
}
