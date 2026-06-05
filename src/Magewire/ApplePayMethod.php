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
    private const LISTENERS = [
        'coupon_code_applied' => 'refresh',
        'coupon_code_revoked' => 'refresh',
        'billing_address_activated' => 'refresh',
        'billing_address_submitted' => 'refresh',
        'billing_as_shipping_address_updated' => 'refresh',
        'shipping_address_activated' => 'refresh',
        'shipping_address_submitted' => 'refresh',
        'guest_shipping_address_submitted' => 'refresh',
        'guest_shipping_address_saved' => 'refresh',
    ];

    /**
     * @var array<string, string>
     */
    protected $listeners = self::LISTENERS;

    /**
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        private Session $checkoutSession,
        private CartRepositoryInterface $quoteRepository
    ) {
    }

    /**
     * Force a Magewire refresh so the component re-renders with up-to-date quote data.
     */
    public function refresh(): void
    {
        // Intentionally empty: used to trigger Magewire re-render
    }

    /**
     * Persist Apple Pay payment data on the active quote payment.
     *
     * @param array $value
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function setPaymentData(array $value): void
    {
        $quote = $this->checkoutSession->getQuote();
        $payment = $quote->getPayment();
        $payment->setAdditionalInformation($value['additionalData'] ?? null);
        $payment->setCcType((string) ($value['ccType'] ?? ''));
        $quote->setPayment($payment);
        $this->quoteRepository->save($quote);
    }

    /**
     * Return quote data required by the Apple Pay frontend initializer.
     *
     * @return array{country_id: string, currency_code: string, base_grand_total: float}
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteInformations(): array
    {
        $quote = $this->checkoutSession->getQuote();
        $billingAddress = $quote->getBillingAddress();

        return [
            'country_id' => $billingAddress ? (string) $billingAddress->getCountryId() : '',
            'currency_code' => (string) $quote->getQuoteCurrencyCode(),
            'base_grand_total' => (float) $quote->getGrandTotal(),
        ];
    }
}
