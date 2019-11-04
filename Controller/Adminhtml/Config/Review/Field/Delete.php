<?php
/**
 * Created by PhpStorm.
 * User: jgriffin
 * Date: 10/9/2019
 * Time: 11:34 AM
 */

namespace TransPerfect\GlobalLink\Controller\Adminhtml\Config\Review\Field;

use Magento\Framework\View\Result\PageFactory;
use TransPerfect\GlobalLink\Controller\Adminhtml\Config\Review\Field as Action;
use TransPerfect\GlobalLink\Helper\Data;
use TransPerfect\GlobalLink\Helper\Ui\Logger;

/**
 * Class Delete
 *
 * @package TransPerfect\GlobalLink\Controller\Adminhtml\Config\Cms\Block\Field
 */
class Delete extends Action
{
    protected $fieldFactory;


    /**
     * Delete constructor.
     *
     * @param \Magento\Backend\App\Action\Context         $context
     * @param \Magento\Framework\View\Result\PageFactory  $pageFactory
     * @param \TransPerfect\GlobalLink\Helper\Data        $helper
     * @param \TransPerfect\GlobalLink\Helper\Ui\Logger   $logger
     * @param \TransPerfect\GlobalLink\Model\FieldFactory $fieldFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \TransPerfect\GlobalLink\Helper\Data $helper,
        \TransPerfect\GlobalLink\Helper\Ui\Logger $logger,
        \TransPerfect\GlobalLink\Model\FieldFactory $fieldFactory
    ) {
        parent::__construct($context, $pageFactory, $helper, $logger);
        $this->fieldFactory = $fieldFactory;
    }


    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            try {
                $field = $this->fieldFactory->create();
                $field->load($id);

                if ($field->getObjectType() != Data::PRODUCT_REVIEW_ID) {
                    throw new \Exception(__('Field ID ' . $id . ' is not a product review field'));
                }
                if (!$field->getId()) {
                    throw new \Exception(__('Field "' . $field->getFieldName() . '" is missing from the database and cannot be deleted.'));
                }
                if ($field->getUserSubmitted() != 1) {
                    throw new \Exception(__('Field "' . $field->getFieldName() . '" is not a user created field and cannot be deleted.'));
                }

                //delete the record
                $field->delete();
                $this->messageManager->addSuccessMessage(__('Field "' . $field->getFieldName() . '" deleted successfully'));
                if($this->logger->isDebugEnabled()) {
                    //UI action log
                    $this->logger->logAction(Data::PRODUCT_REVIEW_ID, Logger::CONFIG_DELETE_ACTION_TYPE, $this->getRequest()->getParams());
                }
            } catch (\Exception $e) {
                if($this->logger->isErrorEnabled()) {
                    $this->logger->logAction(Data::PRODUCT_REVIEW_ID, Logger::CONFIG_DELETE_ACTION_TYPE, $this->getRequest()->getParams(), Logger::CRITICAL, $e->getMessage());
                    $this->messageManager->addErrorMessage($e->getMessage());
                }
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/config_review/field');
        return $resultRedirect;
    }
}