<?php

/**
 * MagePrince
 * Copyright (C) 2018 Mageprince
 *
 * NOTICE OF LICENSE
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html
 *
 * @category MagePrince
 * @package Prince_Forcelogin
 * @copyright Copyright (c) 2018 MagePrince
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MagePrince
 */

namespace Prince\Forcelogin\Controller\Adminhtml\Forcelogin;

class Delete extends \Prince\Forcelogin\Controller\Adminhtml\Forcelogin
{
    /**
     * @var \Prince\Forcelogin\Model\Forcelogin
     */
    private $forceLoginModel;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;
    
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Prince\Forcelogin\Model\Forcelogin $forceLoginModel
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Prince\Forcelogin\Model\Forcelogin $forceLoginModel,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->forceLoginModel = $forceLoginModel;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * {@inheritdoc}
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Prince_Forcelogin::forcelogin_delete');
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('forcelogin_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->forceLoginModel;
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('You deleted the URL.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['forcelogin_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a URL to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
