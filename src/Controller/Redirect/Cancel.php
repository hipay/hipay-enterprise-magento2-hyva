<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Controller\Redirect;

use HiPay\FullserviceMagento\Controller\Fullservice;

/**
 * HiPay Fullservice Magento - Hyvä Checkout
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @copyright Copyright (c) 2024 - HiPay
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 */
class Cancel extends Fullservice
{
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;

    /**
     * @var \Magento\Sales\Api\OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @param \Magento\Framework\App\Action\Context            $context
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Checkout\Model\Session                  $checkoutSession
     * @param \Magento\Framework\Session\Generic               $hipaySession
     * @param \Psr\Log\LoggerInterface                         $logger
     * @param \HiPay\FullserviceMagento\Model\Gateway\Factory  $gatewayManagerFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Sales\Model\OrderFactory                $orderFactory
     * @param \Magento\Sales\Api\OrderManagementInterface      $orderManagement
     * @param \Magento\Checkout\Model\Cart                     $cart
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\Generic $hipaySession,
        \Psr\Log\LoggerInterface $logger,
        \HiPay\FullserviceMagento\Model\Gateway\Factory $gatewayManagerFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Api\OrderManagementInterface $orderManagement,
        \Magento\Checkout\Model\Cart $cart
    ) {
        $this->orderFactory = $orderFactory;
        $this->orderManagement = $orderManagement;
        $this->cart = $cart;
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $hipaySession,
            $logger,
            $gatewayManagerFactory,
            $resultJsonFactory
        );
    }

    /**
     * Save the cart once after re-adding all items instead of once per item.
     *
     * @return                                       void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $lastOrderId = $this->_getCheckoutSession()->getLastOrderId();
        if ($lastOrderId) {
            /**
             * @var $order  \Magento\Sales\Model\Order
             */
            $order = $this->orderFactory->create();
            $order->load($lastOrderId);
            if ($order && (bool)$order->getPayment()->getMethodInstance()->getConfigData('re_add_to_cart')) {
                $items = $order->getAllVisibleItems();
                try {
                    foreach ($items as $item) {
                        $this->cart->addOrderItem($item);
                    }
                    // single save: one customer-data invalidation instead of one per item
                    $this->cart->save();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    if ($this->_getCheckoutSession()->getUseNotice(true)) {
                        $this->messageManager->addNotice($e->getMessage());
                    } else {
                        $this->messageManager->addError($e->getMessage());
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addException(
                        $e,
                        __('We can\'t add this item to your shopping cart right now.')
                    );
                }
            }
            $this->orderManagement->cancel($lastOrderId);
            $this->messageManager->addNoticeMessage(
                __('Your order #%1 was canceled.', $order->getIncrementId())
            );
        }

        $this->_redirect('checkout/cart');
    }
}
