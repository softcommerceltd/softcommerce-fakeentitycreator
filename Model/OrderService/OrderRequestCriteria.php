<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Model\OrderService;

use Faker;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use SoftCommerce\Core\Framework\DataStorage;
use SoftCommerce\FakeEntityCreator\Model\ConfigInterface;

/**
 * @inheritDoc
 */
class OrderRequestCriteria extends DataStorage implements OrderRequestCriteriaInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Faker\Factory
     */
    private $fakerFactory;

    /**
     * @var Faker\Generator|null
     */
    private $faker;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var AdapterInterface|null
     */
    private $resourceConnection;

    /**
     * @var array
     */
    private $requiredStates;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * OrderRequestCriteria constructor.
     * @param ConfigInterface $config
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Faker\Factory $fakerFactory
     * @param ResourceConnection $resourceConnection
     * @param ScopeConfigInterface $scopeConfig
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        ConfigInterface $config,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        Faker\Factory $fakerFactory,
        ResourceConnection $resourceConnection,
        ScopeConfigInterface $scopeConfig,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->config = $config;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->fakerFactory = $fakerFactory;
        $this->resource = $resourceConnection;
        $this->scopeConfig = $scopeConfig;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->storeManager = $storeManager;
        parent::__construct($data);
    }

    /**
     * @inheritDoc
     */
    public function getStore(): StoreInterface
    {
        if (!$store = $this->getData(self::STORE)) {
            $store = $this->storeManager->getStore($this->getStoreCode());
        }
        return $store;
    }

    /**
     * @inheritDoc
     */
    public function setStore(StoreInterface $store)
    {
        $this->setData($store, self::STORE);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStoreCode(): string
    {
        if (!$this->getData(self::STORE_CODE)) {
            $this->setStoreCode($this->getRandomStore());
        }
        return $this->getData(self::STORE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setStoreCode(string $storeCode)
    {
        $this->setData($storeCode, self::STORE_CODE);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCustomer(): CustomerInterface
    {
        if (!$this->getData(self::CUSTOMER)) {
            try {
                $customer = $this->customerRepository->get($this->getEmail(), $this->getStore()->getWebsiteId());
            } catch (\Exception $e) {
                $customer = $this->customerFactory
                    ->create()
                    ->setWebsiteId($this->getStore()->getWebsiteId())
                    ->setStoreId($this->getStore()->getId())
                    ->setFirstname($this->faker()->firstName)
                    ->setLastname($this->faker()->lastName)
                    ->setMiddlename($this->faker()->boolean(15) ? $this->faker()->name : '')
                    ->setPrefix($this->faker()->title)
                    ->setEmail($this->getEmail());
                $customer = $this->customerRepository->save($customer);
            }
            $this->setCustomer($customer);
        }

        return $this->getData(self::CUSTOMER);
    }

    /**
     * @inheritDoc
     */
    public function setCustomer(CustomerInterface $customer)
    {
        $this->setData($customer, self::CUSTOMER);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getEmail(): ?string
    {
        if (!$this->getData(self::EMAIL)) {
            if ($this->faker()->boolean(25)) {
                $this->setEmail($this->faker()->email);
            } else {
                $connection = $this->getConnection();
                $select = $connection->select()
                    ->from($connection->getTableName('customer_entity'), 'email')
                    ->where('website_id', $this->getStore()->getWebsiteId())
                    ->order(new \Zend_Db_Expr('RAND()'))
                    ->limit(1);
                $this->setEmail($connection->fetchOne($select) ?: $this->faker()->email);
            }
        }

        return $this->getData(self::EMAIL);
    }

    /**
     * @inheritDoc
     */
    public function setEmail(string $email)
    {
        $this->setData($email, self::EMAIL);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLocaleCode(bool $iso3166 = false): ?string
    {
        if (!$this->getData(self::LOCALE_CODE)) {
            $this->setLocaleCode($this->getRandomLocale());
        }
        return false !== $iso3166
            ? substr($this->getData(self::LOCALE_CODE), -2)
            : $this->getData(self::LOCALE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setLocaleCode(string $localeCode)
    {
        $this->setData($localeCode, self::LOCALE_CODE);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCurrencyCode(): ?string
    {
        if (!$this->getData(self::CURRENCY_CODE)) {
            $this->setCurrencyCode($this->getRandomCurrency());
        }
        return $this->getData(self::CURRENCY_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setCurrencyCode(string $currencyCode)
    {
        $this->setData($currencyCode, self::CURRENCY_CODE);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethod(): ?string
    {
        if (!$this->getData(self::PAYMENT_METHOD)) {
            $this->setPaymentMethod($this->getRandomPaymentMethod());
        }
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentMethod(string $paymentMethod)
    {
        $this->setData($paymentMethod, self::PAYMENT_METHOD);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getShippingMethod(): ?string
    {
        if (!$this->getData(self::SHIPPING_METHOD)) {
            $this->setShippingMethod($this->getRandomShippingMethod());
        }
        return $this->getData(self::SHIPPING_METHOD);
    }

    /**
     * @inheritDoc
     */
    public function setShippingMethod(string $shippingMethod)
    {
        $this->setData($shippingMethod, self::SHIPPING_METHOD);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCartQty(): int
    {
        return (int) $this->getData(self::CART_QTY) ?: rand(1, 2);
    }

    /**
     * @inheritDoc
     */
    public function setCartQty(int $cartQty)
    {
        $this->setData($cartQty, self::CART_QTY);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getItemSku(): array
    {
        if (!$this->getData(self::ITEM_SKU)) {
            $this->setItemSku($this->getRandomItemSku());
        }
        return $this->getData(self::ITEM_SKU) ?: [];
    }

    /**
     * @inheritDoc
     */
    public function setItemSku(array $itemSku)
    {
        $this->setData($itemSku, self::ITEM_SKU);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getItemQty(): int
    {
        return (int) $this->getData(self::ITEM_QTY) ?: 1;
    }

    /**
     * @inheritDoc
     */
    public function setItemQty(int $itemQty)
    {
        $this->setData($itemQty, self::ITEM_QTY);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAddress(string $typeId = self::BILLING_ADDRESS): array
    {
        if (!$this->getData($typeId)) {
            $this->setAddress($this->getRandomAddress($typeId), $typeId);
        }
        return $this->getData($typeId) ?: [];
    }

    /**
     * @inheritDoc
     */
    public function setAddress(array $address, string $typeId)
    {
        $this->setData($address, $typeId);
        return $this;
    }

    /**
     * @return string
     */
    private function getRandomStore(): string
    {
        $stores = $this->storeManager->getStores();
        $result = [];
        foreach ($stores as $store) {
            $result[] = $store->getCode();
        }
        return $result[array_rand($result, 1)];
    }

    /**
     * @return string
     */
    private function getRandomLocale(): string
    {
        if (!$locale = $this->config->getLocaleOptions()) {
            $locale = [
                'en_GB',
                'en_US',
                'de_DE'
            ];
        }
        return $locale[array_rand($locale, 1)];
    }

    /**
     * @return string
     */
    private function getRandomCurrency()
    {
        if (!$currency = explode(
            ',',
            $this->scopeConfig->getValue(
                self::XML_PATH_CURRENCY_LIST,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreCode()
            ) ?: ''
        )) {
            $currency = [
                'GBP',
                'EUR',
                'USD'
            ];
        }
        return $currency[array_rand($currency, 1)];
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getRandomItemSku(): array
    {
        $connection = $this->getConnection();
        if (!$stockItemTableName = $this->stockIndexTableNameResolver->execute($this->getStockId())) {
            throw new LocalizedException(__('Could not resolve stock item table name.'));
        }

        $select = $connection->select()
            ->from($stockItemTableName, 'sku')
            ->where('quantity >= ?', $this->getItemQty())
            ->order(new \Zend_Db_Expr('RAND()'))
            ->limit($this->getCartQty());
        return $connection->fetchCol($select);
    }

    /**
     * @return string
     */
    private function getRandomPaymentMethod(): string
    {
        if (!$methods = $this->config->getPaymentMethodList()) {
            $methods = [
                'cashondelivery',
                'banktransfer',
                'checkmo'
            ];
        }
        return $methods[array_rand($methods, 1)];
    }

    /**
     * @return string
     */
    private function getRandomShippingMethod(): string
    {
        if (!$methods = $this->config->getShippingMethodList()) {
            $methods = [
                'freeshipping_freeshipping',
                'flatrate_flatrate'
            ];
        }
        return $methods[array_rand($methods, 1)];
    }

    /**
     * @param string $typeId
     * @return array
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws InputMismatchException
     */
    private function getRandomAddress(string $typeId = self::BILLING_ADDRESS): array
    {
        if ($typeId === self::SHIPPING_ADDRESS
            && $this->faker()->boolean(33)
            && $address = $this->getAddress()
        ) {
            return $address;
        }

        $region = $this->getRegion();
        $regionId = $region ? $this->getRegionId($region) : 0;
        $isBillingFlag = $typeId === self::BILLING_ADDRESS;
        return [
            'prefix' => $isBillingFlag ? $this->getCustomer()->getPrefix() : $this->faker()->title,
            'firstname' => $isBillingFlag ? $this->getCustomer()->getFirstname() : $this->faker()->firstName,
            'lastname'  => $isBillingFlag ? $this->getCustomer()->getLastname(): $this->faker()->lastName,
            'company' => $this->faker()->company,
            'street' => $this->faker()->streetAddress,
            'city' => $this->faker()->city,
            'country_id' => $this->getLocaleCode(true),
            'region' => $region,
            'region_id' => $regionId,
            'postcode' => $this->faker()->postcode,
            'telephone' => $this->faker()->phoneNumber,
            'fax' => $this->faker()->e164PhoneNumber,
            'save_in_address_book' => 1
        ];
    }

    /**
     * @return string|null
     */
    private function getRegion(): ?string
    {
        if (!in_array($this->getLocaleCode(true), $this->getRequiredCountryStates())) {
            return null;
        }
        return $this->faker()->state;
    }

    /**
     * @param string $region
     * @return int
     */
    private function getRegionId(string $region): int
    {
        if (!in_array($this->getLocaleCode(true), $this->getRequiredCountryStates()) || $region === '') {
            return 0;
        }

        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName('directory_country_region'), 'region_id')
            ->where('default_name = ?', $region);
        return (int) $connection->fetchOne($select);
    }

    /**
     * @return array
     */
    private function getRequiredCountryStates(): array
    {
        if (null === $this->requiredStates) {
            $this->requiredStates = explode(
                ',',
                $this->scopeConfig->getValue(
                    self::XML_PATH_STATES_REQUIRED,
                    ScopeInterface::SCOPE_STORE
                )
            );
        }
        return $this->requiredStates;
    }

    /**
     * @return int|null
     * @throws NoSuchEntityException
     */
    private function getStockId(): ?int
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName('inventory_stock_sales_channel'), 'stock_id')
            ->where('type = ?', SalesChannelInterface::TYPE_WEBSITE)
            ->where('code = ?', $this->getStore()->getWebsite()->getCode());
        return (int) $connection->fetchOne($select) ?: null;
    }

    /**
     * @return Faker\Generator
     */
    private function faker(): Faker\Generator
    {
        if (null === $this->faker) {
            $this->faker = $this->fakerFactory->create($this->getLocaleCode());
        }
        return $this->faker;
    }

    /**
     * @return AdapterInterface
     */
    private function getConnection(): AdapterInterface
    {
        if (null === $this->resourceConnection) {
            $this->resourceConnection = $this->resource->getConnection();
        }
        return $this->resourceConnection;
    }
}
