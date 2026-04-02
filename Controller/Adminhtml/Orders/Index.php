<?php
/**
 * Sudha_Mageshopifysync
 *
 * @category  Sudha
 * @package   Sudha_Mageshopifysync
 * @license   https://opensource.org/licenses/OSL-3.0
 */

declare(strict_types=1);

namespace Sudha\Mageshopifysync\Controller\Adminhtml\Orders;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * Authorization level of a basic admin session.
     */
    public const ADMIN_RESOURCE = 'Sudha_Mageshopifysync::orders';

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        private readonly PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Delayed orders dashboard page.
     *
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Sudha_Mageshopifysync::orders');
        $resultPage->getConfig()->getTitle()->prepend(__('Shopify Delayed Orders'));
        return $resultPage;
    }
}
