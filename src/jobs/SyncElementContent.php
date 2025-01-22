<?php
/**
 * @link      https://neustadt.swiss
 * @copyright Copyright (c) Neustadt Agentur AG
 */

namespace neustadt\sitecopy\jobs;

use Craft;
use craft\base\Element;
use craft\queue\BaseJob;
use neustadt\sitecopy\SiteCopy;
use yii\web\ServerErrorHttpException;

/**
 * Class SyncElementContent
 *
 * @package neustadt\sitecopy\jobs
 */
class SyncElementContent extends BaseJob
{
    // Properties
    // =========================================================================

    /**
     * @var int The element ID where we want to perform the syncing on
     */
    public $elementId;

    /**
     * @var int the site ID where we want to copy the content from
     */
    public $sourceSiteId;

    /**
     * @var int[] The sites IDs where we want to overwrite the content
     */
    public $sites;

    /**
     * @var array
     */
    public $attributesToCopy;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function execute($queue): void
    {
        $elementsService = Craft::$app->getElements();

        if (empty($this->sites)) {
            return;
        }

        $sourceElement = $elementsService->getElementById($this->elementId, null, $this->sourceSiteId);

        if (!$sourceElement) {
            return;
        }

        $data = [];

        foreach ($this->attributesToCopy as $attribute) {
            if ($attribute == 'fields') {
                $tmp = SiteCopy::getInstance()->sitecopy->getSerializedFieldValues($sourceElement);

                if (empty($tmp)) {
                    continue;
                }
            } elseif ($attribute == 'variants') {
                if (!$sourceElement instanceof craft\commerce\elements\Product || !$sourceElement->getType()->hasVariants) {
                    continue;
                }

                $variantFields = [];

                foreach ($sourceElement->getVariants() as $variant) {
                    $variantFields[$variant->id] = $variant->getFieldValues();
                }

                $tmp = $variantFields;
            } else {
                $tmp = $sourceElement->{$attribute};
            }

            $data[$attribute] = $tmp;
        }

        $totalSites = count($this->sites);
        $currentSite = 0;
        $mutex = Craft::$app->getMutex();

        foreach ($this->sites as $siteId) {
            $this->setProgress($queue, $currentSite / $totalSites, Craft::t('app', '{step} of {total}', [
                'step'  => $currentSite + 1,
                'total' => $totalSites,
            ]));

            /** @var Element $siteElement */
            $siteElement = $elementsService->getElementById($sourceElement->id, get_class($sourceElement), $siteId);

            foreach ($data as $key => $item) {
                if ($key == 'fields') {
                    $siteElement->setFieldValues($item);
                } elseif ($key == 'variants') {
                    foreach ($item as $variantId => $value) {
                        $variant = craft\commerce\elements\Variant::find()->id($variantId)->siteId($siteId)->one();

                        if ($variant) {
                            $variant->setFieldValues($value);

                            $variant->setScenario(Element::SCENARIO_ESSENTIALS);
                            Craft::$app->getElements()->saveElement($variant);
                        }
                    }
                } else {
                    // this is not possible for custom fields as of craft 3.4.0, make sure they dont reach this
                    $siteElement->{$key} = $item;
                }
            }

            $lockKey = "element:$siteElement->canonicalId";
            if (!$mutex->acquire($lockKey, 15)) {
                throw new ServerErrorHttpException('Could not acquire a lock to save the element.');
            }

            $siteElement->setScenario(Element::SCENARIO_ESSENTIALS);

            try {
                $elementsService->saveElement($siteElement);
            } finally {
                $mutex->release($lockKey);
            }

            $currentSite++;
        }
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): ?string
    {
        return Craft::t('app', 'Syncing element contents');
    }
}
