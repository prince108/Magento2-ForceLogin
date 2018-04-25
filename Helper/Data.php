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

namespace Prince\Forcelogin\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;

/**
 * Class Data
 * @package Prince\Forcelogin\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * @var \Prince\Forcelogin\Model\Forcelogin
     */
    private $forceLoginModel;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $store;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Prince\Forcelogin\Model\Forcelogin $forceLoginModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Session $customerSession,
        Http $request,
        \Prince\Forcelogin\Model\Forcelogin $forceLoginModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->request = $request;
        $this->urlInterface = $context->getUrlBuilder();
        $this->scopeConfig = $context->getScopeConfig();
        $this->forceLoginModel = $forceLoginModel;
        $this->store = $storeManager;
        parent::__construct($context);
    }

    /**
     * Retrieve current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->urlInterface->getCurrentUrl();
    }

    /**
     * Retrieve current url after base url path
     *
     * @return string
     */
    public function getAfterBaseUrl()
    {
        $currentUrl = $this->urlInterface->getCurrentUrl();
        $baseUrl = $this->urlInterface->getBaseUrl();
        $url = rtrim(str_replace($baseUrl, "", $currentUrl), '/');
        return $url;
    }

    /**
     * Retrieve forcelogin collection
     *
     * @return collection
     */
    public function getCollection()
    {
        return $this->forceLoginModel->getCollection();
    }
    
    /**
     * Retrieve url collection
     *
     * @param string $url
     * @return int
     */
    public function getUrlCollection($url)
    {
        $collection = $this->getCollection()->addFieldToFilter('url', $url);
        $collection->addFieldToFilter('status', 1);
        $collection->addFieldToFilter(
            'customer_group',
            [
                ['null' => true],
                ['finset' => $this->getCurrentCustomer()]
            ]
        );
        $collection->addFieldToFilter(
            'storeview',
            [
                ['eq' => 0],
                ['finset' => $this->getCurrentStore()]
            ]
        );

        return $collection->getSize();
    }

    /**
     * Retrieve current customer group id
     *
     * @return int
     */
    public function getCurrentCustomer()
    {
        return $this->customerSession->getCustomer()->getGroupId();
    }

    /**
     * Retrieve current store Id
     *
     * @return int
     */
    public function getCurrentStore()
    {
        return $this->store->getStore()->getId();
    }

    /**
     * Retrieve config value
     *
     * @return string
     */
    public function getConfig()
    {
        return $this->scopeConfig->getValue(
            'forcelogin/general/urlcondition',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Retrieve config value
     *
     * @return string
     */
    public function getEnable()
    {
        return $this->scopeConfig->getValue(
            'forcelogin/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve config value
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->scopeConfig->getValue(
            'forcelogin/general/message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve config value
     *
     * @return boolean
     */
    public function checkCustomerlogin()
    {
        if ($this->customerSession->isLoggedIn()) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Retrieve config value
     *
     * @return boolean
     */
    public function getDefaultAction()
    {
        $currentAction = $this->request->getFullActionName();
        $defaultActions = [
            'customer_account_login',
            'customer_account_logoutSuccess',
            'customer_account_create',
            'customer_account_index',
            'customer_account_forgotpassword',
            'customer_account_forgotpasswordpost'
        ];
        $currentUrl = $this->getCurrentUrl();
        $loginPostUrl = $this->urlInterface->getUrl('customer/account/loginPost');
        if (!in_array($currentAction, $defaultActions) && ($currentUrl != $loginPostUrl)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check is current page is homepage
     *
     * @return bool
     */
    public function checkIsHomePage()
    {
        $currentAction = $this->request->getFullActionName();
        if($currentAction == "cms_index_index") {
            return true;
        }
    }
}