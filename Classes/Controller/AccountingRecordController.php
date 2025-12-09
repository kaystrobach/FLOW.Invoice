<?php

namespace KayStrobach\Invoice\Controller;

use KayStrobach\Backend\Controller\AbstractPageRendererController;
use KayStrobach\Crud\Controller\Traits\IndexTrait;
use KayStrobach\Crud\Controller\Traits\RequirementsTrait;
use KayStrobach\Invoice\Domain\Model\AccountingRecord;
use KayStrobach\Invoice\View\AccountingRecordsCsvView;
use KayStrobach\Invoice\View\AccountingRecordsView;

class AccountingRecordController extends AbstractPageRendererController
{
    use RequirementsTrait;

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
        'xlsx' => AccountingRecordsView::class,
        'csv' => AccountingRecordsCsvView::class
    ];

    public function indexAction(): void
    {
        $this->view->assign(
            'objects',
            $this->getRepository()->findByDefaultQuery()
        );
        $this->view->assign(
            'values',
            $this->getRepository()->findByDefaultQuery()
        );
    }

    public function getModelClassName()
    {
        return AccountingRecord::class;
    }
}
