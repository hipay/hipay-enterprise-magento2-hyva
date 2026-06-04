<?php

declare(strict_types=1);

namespace HiPay\FullserviceHyvaCheckout\Magewire;

use HiPay\FullserviceMagento\Model\Method\Providers\GenericConfigProvider;
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
class CardMethod extends Component
{
    /**
     * Customer identity data used to prefill HiPay hosted fields.
     *
     * @var array{firstName?: string, lastName?: string}
     */
    public array $customerInformation = [];

    /**
     * Saved customer cards exposed to the Hyva payment templates.
     *
     * @var array<int|string, array<string, mixed>>
     */
    public array $customerCards = [];

    /**
     * @var string
     */
    public string $selectedCustomerCard = '';

    /**
     * @var bool
     */
    public bool $customerCardIsSelected = false;

    /**
     * @param CartRepositoryInterface $quoteRepository
     * @param GenericConfigProvider $genericConfigProvider
     * @param Session $checkoutSession
     */
    public function __construct(
        private CartRepositoryInterface $quoteRepository,
        private GenericConfigProvider $genericConfigProvider,
        private Session $checkoutSession
    ) {
    }

    /**
     * Initialize customer data and saved cards for hosted and hosted-fields methods.
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function mount(): void
    {
        $quote = $this->checkoutSession->getQuote();
        $billingAddress = $quote->getBillingAddress();
        $paymentConfig = $this->genericConfigProvider->getConfig()['payment'] ?? [];
        $this->customerCards = $paymentConfig['hiPayFullservice']['customerCards'] ?? [];

        if ($billingAddress) {
            $this->customerInformation = [
                'firstName' => (string) $billingAddress->getFirstname(),
                'lastName' => (string) $billingAddress->getLastname(),
            ];
        }

        if ($this->customerCards) {
            $this->selectedCustomerCard = '0';
            $this->customerCardIsSelected = true;
        }
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
        $payment->setAdditionalInformation($value['additionalData'] ?? null);
        $quote->setPayment($payment);
        $this->quoteRepository->save($quote);
    }
}
