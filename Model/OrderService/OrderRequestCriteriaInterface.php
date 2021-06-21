<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Model\OrderService;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;

/**
 * Interface OrderRequestCriteriaInterface used to build
 * order request criteria data.
 */
interface OrderRequestCriteriaInterface
{
    public const STORE = 'store';
    public const STORE_CODE = 'store_code';
    public const CUSTOMER = 'customer';
    public const EMAIL = 'email';
    public const LOCALE_CODE = 'locale_code';
    public const CURRENCY_CODE = 'currency_code';
    public const PAYMENT_METHOD = 'payment_method';
    public const SHIPPING_METHOD = 'shipping_method';
    public const CART_QTY = 'cart_qty';
    public const ITEM_SKU = 'item_sku';
    public const ITEM_QTY = 'item_qty';
    public const BILLING_ADDRESS = 'billing_address';
    public const SHIPPING_ADDRESS = 'shipping_address';

    public const XML_PATH_CURRENCY_LIST = 'currency/options/allow';
    public const XML_PATH_STATES_REQUIRED = 'general/region/state_required';

    /**
     * @return StoreInterface|Store
     * @throws NoSuchEntityException
     */
    public function getStore(): StoreInterface;

    /**
     * @param StoreInterface $store
     * @return $this
     */
    public function setStore(StoreInterface $store);

    /**
     * @return string
     */
    public function getStoreCode(): string;

    /**
     * @param string $storeCode
     * @return $this
     */
    public function setStoreCode(string $storeCode);

    /**
     * @return CustomerInterface
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws LocalizedException
     * @throws InputMismatchException
     */
    public function getCustomer(): CustomerInterface;

    /**
     * @param CustomerInterface $customer
     * @return $this
     */
    public function setCustomer(CustomerInterface $customer);

    /**
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email);

    /**
     * @param bool $iso3166
     * @return string|null
     */
    public function getLocaleCode(bool $iso3166 = false): ?string;

    /**
     * @param string $localeCode
     * @return $this
     */
    public function setLocaleCode(string $localeCode);

    /**
     * @return string|null
     */
    public function getCurrencyCode(): ?string;

    /**
     * @param string $currencyCode
     * @return $this
     */
    public function setCurrencyCode(string $currencyCode);

    /**
     * @return string|null
     */
    public function getPaymentMethod(): ?string;

    /**
     * @param string $paymentMethod
     * @return $this
     */
    public function setPaymentMethod(string $paymentMethod);

    /**
     * @return string|null
     */
    public function getShippingMethod(): ?string;

    /**
     * @param string $shippingMethod
     * @return $this
     */
    public function setShippingMethod(string $shippingMethod);

    /**
     * @return int
     */
    public function getCartQty(): int;

    /**
     * @param int $cartQty
     * @return $this
     */
    public function setCartQty(int $cartQty);

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getItemSku(): array;

    /**
     * @param array $itemSku
     * @return $this
     */
    public function setItemSku(array $itemSku);

    /**
     * @return int
     */
    public function getItemQty(): int;

    /**
     * @param int $itemQty
     * @return $this
     */
    public function setItemQty(int $itemQty);

    /**
     * @param string $typeId
     * @return array
     * @throws InputException
     * @throws InputMismatchException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getAddress(string $typeId = self::BILLING_ADDRESS): array;

    /**
     * @param array $address
     * @param string $typeId
     * @return $this
     */
    public function setAddress(array $address, string $typeId);
}
