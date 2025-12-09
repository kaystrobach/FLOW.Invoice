<?php
/**
 * Created by kay.
 */

namespace KayStrobach\Invoice\Controller;

use KayStrobach\Backend\Controller\AbstractPageRendererController;
use Neos\Flow\Annotations as Flow;
use KayStrobach\Crud\Controller\Traits\IndexTrait;
use KayStrobach\Crud\Controller\Traits\RequirementsTrait;
use KayStrobach\Invoice\Domain\Model\DailyClosing;
use Neos\Flow\Security\Authentication\AuthenticationManagerInterface;

class DailyClosingController extends AbstractPageRendererController
{
    use RequirementsTrait;
    use IndexTrait;

    /**
     * @var AuthenticationManagerInterface
     * @Flow\Inject
     */
    protected $authenticationManager;

    protected function getModelClassName()
    {
        return DailyClosing::class;
    }

    public function downloadAction(DailyClosing $object)
    {
        $object->addDownload($this->authenticationManager->getSecurityContext()->getAccount());
        $this->getRepository()->update($object);
        $this->persistenceManager->persistAll();

        $this->controllerContext->getResponse()->setContentType(
            $object->getOriginalResource()->getMediaType()
        );
        $this->controllerContext->getResponse()->addHttpHeader(
            'Content-Disposition',
            'attachment;filename="' . $object->getOriginalResource()->getFilename() .'"'
        );
        $this->controllerContext->getResponse()->addHttpHeader(
            'Cache-Control',
            'max-age=0'
        );
        return stream_get_contents($object->getOriginalResource()->getStream());
    }
}
