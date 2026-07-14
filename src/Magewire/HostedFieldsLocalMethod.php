<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Magewire;

use HiPay\FullserviceMagento\Model\Method\MultibancoHostedFields;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magewirephp\Magewire\Component;

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
class HostedFieldsLocalMethod extends Component
{
    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param Session $checkoutSession
     */
    public function __construct(
        private CartRepositoryInterface $quoteRepository,
        private Session $checkoutSession
    ) {
    }

    /**
     * Persist payment data on the active quote payment.
     *
     * @param array $value
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setPaymentData(array $value): void
    {
        $quote = $this->checkoutSession->getQuote();
        $payment = $quote->getPayment();
        $additionalData = $value['additionalData'] ?? [];
        $payment->setAdditionalInformation($additionalData);

        if (!empty($additionalData['cc_type'])
            && $payment->getMethod() === MultibancoHostedFields::HIPAY_METHOD_CODE
        ) {
            $payment->setCcType($additionalData['cc_type']);
        }

        $quote->setPayment($payment);
        $this->quoteRepository->save($quote);
    }
}
