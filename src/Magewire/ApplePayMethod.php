<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Magewire;

use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
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
class ApplePayMethod extends Component
{
    public array $quoteInformations = [];

    public function __construct(
        private Session $checkoutSession,
        private CartRepositoryInterface $quoteRepository
    ) {
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function mount(): void
    {
        $quote = $this->checkoutSession->getQuote();

        $this->quoteInformations = [
            'country_id' => $quote->getBillingAddress()->getCountryId(),
            'currency_code' => $quote->getQuoteCurrencyCode(),
            'base_grand_total' => $quote->getGrandTotal(),
        ];
    }

    /**
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setPaymentData(array $value): void
    {
        $quote = $this->checkoutSession->getQuote();
        $payment = $quote->getPayment();
        $payment->setAdditionalInformation($value['additionalData']);
        $payment->setCcType($value['ccType']);
        $quote->setPayment($payment);
        $this->quoteRepository->save($quote);
    }
}
