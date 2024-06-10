<?php
/**
 * @link      https://www.goldinteractive.ch
 * @copyright Copyright (c) 2018 Gold Interactive
 */

namespace goldinteractive\sitecopy;

use Craft;
use craft\base\Element;
use craft\base\Plugin;
use craft\commerce\elements\Product;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use craft\events\DefineHtmlEvent;
use craft\events\ElementEvent;
use craft\events\RegisterElementActionsEvent;
use craft\services\Elements;
use craft\web\twig\variables\CraftVariable;
use goldinteractive\sitecopy\elements\actions\BulkCopy;
use goldinteractive\sitecopy\models\SettingsModel;
use yii\base\Event;

/**
 * @author    Gold Interactive
 * @package   Site Copy X
 * @since     0.2.0
 *
 */
class SiteCopy extends Plugin
{
    public string $schemaVersion = '1.0.2';
    public bool $hasCpSettings = true;

    public function init()
    {
        parent::init();

        $this->setComponents(
            [
                'sitecopy' => services\SiteCopy::class,
            ]
        );

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            Event::on(
                CraftVariable::class,
                CraftVariable::EVENT_INIT,
                function (Event $event) {
                    $variable = $event->sender;
                    $variable->set('sitecopy', services\SiteCopy::class);
                }
            );

            if (Craft::$app->getIsMultiSite() && Craft::$app->getSites()->getTotalEditableSites() > 1) {
                Event::on(
                    Element::class,
                    Element::EVENT_DEFINE_SIDEBAR_HTML,
                    function (DefineHtmlEvent $event) {
                        $element = $event->sender;

                        if (in_array(get_class($element), [Entry::class, Asset::class, 'craft\commerce\elements\Product', Category::class])) {
                            $event->html .= $this->addSitecopyWidget($event->sender);
                        }
                    }
                );

                Event::on(
                    Asset::class,
                    Element::EVENT_REGISTER_ACTIONS,
                    function (RegisterElementActionsEvent $event) {
                        $event->actions[] = new BulkCopy();
                    });

                Event::on(
                    Product::class,
                    Element::EVENT_REGISTER_ACTIONS,
                    function (RegisterElementActionsEvent $event) {
                        if (strpos($event->source, 'productType:') !== false) {
                            $event->actions[] = new BulkCopy();
                        }
                    });

                Event::on(
                    Category::class,
                    Element::EVENT_REGISTER_ACTIONS,
                    function (RegisterElementActionsEvent $event) {
                        $event->actions[] = new BulkCopy();
                    });

                Event::on(
                    Entry::class,
                    Element::EVENT_REGISTER_ACTIONS,
                    function (RegisterElementActionsEvent $event) {
                        // every id can be in another section on overview, for the moment we only activate the feature if a specific section is selected
                        if (strpos($event->source, 'section:') !== false) {
                            try {
                                $sectionUid = explode('section:', $event->source)[1];

                                $section = Craft::$app->getEntries()->getSectionByUid($sectionUid);

                                if ($section && $section->getHasMultiSiteEntries()) {
                                    $event->actions[] = BulkCopy::class;
                                }
                            } catch (\Exception $e) {
                                Craft::error($e->getMessage());
                            }
                        }
                    }
                );


                Craft::$app->view->hook(
                    'cp.globals.edit.content',
                    function (array &$context) {
                        /** @var $element GlobalSet */
                        $element = $context['globalSet'];

                        return $this->addSitecopyWidget($element);
                    }
                );

                Craft::$app->view->hook(
                    'cp.commerce.product.edit.details',
                    function (array &$context) {
                        return $this->addSitecopyWidget($context['product']);
                    }
                );

                Event::on(
                    Elements::class,
                    Elements::EVENT_AFTER_SAVE_ELEMENT,
                    function (ElementEvent $event) {
                        $this->sitecopy->syncElementContent($event, Craft::$app->request->post('sitecopy', []));
                    }
                );
            }
        }
    }

    /**
     * @return string|void
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \yii\base\Exception
     */
    private function addSitecopyWidget(Entry|craft\commerce\elements\Product|Asset|GlobalSet|Category $element)
    {
        $isNew = $element->id === null;
        $sites = $element->getSupportedSites();

        if ($isNew || count($sites) < 2) {
            return;
        }

        $scas = $this->sitecopy->handleSiteCopyActiveState($element);

        $siteCopyEnabled = $scas['siteCopyEnabled'];
        $selectedSites = $scas['selectedSites'];

        $currentSite = $element->siteId ?? null;

        return Craft::$app->view->renderTemplate(
            'site-copy-x/_cp/elementsEdit',
            [
                'siteId'          => $element->siteId,
                'supportedSites'  => $sites,
                'siteCopyEnabled' => $siteCopyEnabled,
                'selectedSites'   => $selectedSites,
                'currentSite'     => $currentSite,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new SettingsModel();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('site-copy-x/_cp/settings', [
            'settings'                    => $this->getSettings(),
            'criteriaFieldOptionsEntries' => services\SiteCopy::getCriteriaFieldsEntries(),
            'criteriaFieldOptionsGlobals' => services\SiteCopy::getCriteriaFieldsGlobals(),
            'criteriaFieldOptionsAssets'  => services\SiteCopy::getCriteriaFieldsAssets(),
            'criteriaOperatorOptions'     => services\SiteCopy::getOperators(),
        ]);
    }
}
