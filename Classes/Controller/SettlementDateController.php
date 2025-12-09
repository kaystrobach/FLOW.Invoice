<?php
namespace KayStrobach\Invoice\Controller;

/*
 * This file is part of the KayStrobach.Invoice package.
 */

use KayStrobach\Backend\Controller\AbstractPageRendererController;
use KayStrobach\Crud\Controller\Traits\IndexTrait;
use KayStrobach\Crud\Controller\Traits\RequirementsTrait;
use KayStrobach\Invoice\Domain\Model\SettlementDate;
use KayStrobach\Invoice\View\SettlementDatesExcelView;

class SettlementDateController extends AbstractPageRendererController
{
    use RequirementsTrait;
    use IndexTrait;

    /**
     * A list of formats and object names of the views which should render them.
     *
     * Example:
     *
     * array('html' => 'MyCompany\MyApp\MyHtmlView', 'json' => 'MyCompany\...
     *
     * @var array
     */
    protected $viewFormatToObjectNameMap = [
        'xlsx' => SettlementDatesExcelView::class
    ];

    public function getModelClassName()
    {
        return SettlementDate::class;
    }

}
