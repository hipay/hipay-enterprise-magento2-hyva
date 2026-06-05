<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Magewire;

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
        private CartRepositoryInterface $quoteRepository,
    ) {
    }

    /**
     * Force a Magewire refresh after checkout events.
     */
    public function refresh(): void
    {
        // Intentionally empty: used to trigger Magewire re-render
    }

    /**
     * Return quote totals required by the PayPal frontend component.
     *
     * @return array{currency_code: string, base_total: float}
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteInformations(): array
    {
        $quote = $this->checkoutSession->getQuote();

        return [
            'currency_code' => (string) $quote->getQuoteCurrencyCode(),
            'base_total' => (float) $quote->getGrandTotal(),
        ];
    }

    /**
     * Persist PayPal payment data on the active quote payment.
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
        $quote->setPayment($payment);
        $this->quoteRepository->save($quote);
    }

    /**
     * Validate the minimum shipping data required by the PayPal frontend flow.
     *
     * Return normalized address data for JS consumption.
     *
     * @return array{valid: bool, fields: string[], shipping: array<string, string>|null}
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function validateCustomerShippingInformation(): array
    {
        $quote = $this->checkoutSession->getQuote();

        // Virtual quotes do not require shipping data.
        if ($quote->getIsVirtual()) {
            return ['valid' => true, 'fields' => [], 'shipping' => null];
        }

        $addr = $quote->getShippingAddress();
        if (!$addr) {
            return $this->buildShippingValidationResult([], ['address', 'zipCode', 'city', 'country']);
        }

        $street = (array) $addr->getStreet();
        $street0 = trim((string) ($street[0] ?? ''));
        $street1 = trim((string) ($street[1] ?? ''));

        $shipping = [
            'firstname'      => (string) $addr->getFirstname(),
            'lastname'       => (string) $addr->getLastname(),
            'zipCode'        => (string) $addr->getPostcode(),
            'city'           => (string) $addr->getCity(),
            'country'        => (string) $addr->getCountryId(),
            'streetaddress'  => $street0,
            'streetaddress2' => $street1,
        ];

        return $this->buildShippingValidationResult(
            $shipping,
            $this->collectMissingShippingFields($shipping)
        );
    }

    /**
     * Collect missing frontend field identifiers from normalized shipping data.
     *
     * @param array $shipping
     * @return string[]
     */
    private function collectMissingShippingFields(array $shipping): array
    {
        $missing = [];

        if (trim((string) ($shipping['streetaddress'] ?? '')) === '') {
            $missing[] = 'address';
        }
        if (trim((string) ($shipping['zipCode'] ?? '')) === '') {
            $missing[] = 'zipCode';
        }
        if (trim((string) ($shipping['city'] ?? '')) === '') {
            $missing[] = 'city';
        }
        if (trim((string) ($shipping['country'] ?? '')) === '') {
            $missing[] = 'country';
        }

        return $missing;
    }

    /**
     * Build the shipping validation payload returned to the frontend component.
     *
     * @param array $shipping
     * @param array $missing
     * @return array
     */
    private function buildShippingValidationResult(array $shipping, array $missing): array
    {
        return [
            'valid' => $missing === [],
            'fields' => $missing,
            'shipping' => $shipping ?: null,
        ];
    }
}
