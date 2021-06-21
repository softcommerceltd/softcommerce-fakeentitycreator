<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\FakeEntityCreator\Model\Config\Backend\Order;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class CronFrequency used to save
 * cron job expression to database.
 */
class CronFrequency extends Value
{
    private const CRON_CONFIG_PATH = 'crontab/default/jobs/fakeentitycreator_order_queue/schedule/cron_expr';
    private const CRON_MODEL_PATH = 'crontab/default/jobs/fakeentitycreator_order_queue/run/model';
    private const CRON_VALUE   = 'groups/order_entity/fields/cron_frequency/value';

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * @var string
     */
    protected $runModelPath = '';

    /**
     * CronFrequency constructor.
     * @param ValueFactory $configValueFactory
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        ValueFactory $configValueFactory,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        string $runModelPath = '',
        array $data = []
    ) {
        $this->runModelPath = $runModelPath;
        $this->valueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function afterSave()
    {
        try {
            $this->valueFactory->create()
                ->load(self::CRON_CONFIG_PATH, 'path')
                ->setValue($this->getData(self::CRON_VALUE))
                ->setPath(self::CRON_CONFIG_PATH)
                ->save();
            $this->valueFactory->create()
                ->load(self::CRON_MODEL_PATH, 'path')
                ->setValue($this->runModelPath)
                ->setPath(self::CRON_MODEL_PATH)
                ->save();
        } catch (\Exception $e) {
            throw new LocalizedException(__('Could not save cron job. Reason: %1', $e->getMessage()));
        }

        return parent::afterSave();
    }
}
