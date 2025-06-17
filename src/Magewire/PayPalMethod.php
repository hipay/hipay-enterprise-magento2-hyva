<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Magewire;

use HiPay\FullserviceMagento\Model\Method\Providers\GenericConfigProvider;
use HiPay\FullserviceMagento\Model\PaypalConfigProvider;
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
class PayPalMethod extends Component
{
    public array $customerInformation = [];

    public function __construct(
        private Session $checkoutSession,
        private CartRepositoryInterface $quoteRepository,
    ) { }

    public function getQuoteInformations(): array
    {
        $quote = $this->checkoutSession->getQuote();
        return [
            'currency_code' => $quote->getQuoteCurrencyCode(),
            'base_total' => $quote->getGrandTotal(),
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
        $payment->setAdditionalInformation($value['additionalData'] ?? null);
        $quote->setPayment($payment);
        $this->quoteRepository->save($quote);
    }
}
