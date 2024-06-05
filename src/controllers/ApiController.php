<?php

namespace goldinteractive\sitecopy\controllers;

use Craft;
use craft\elements\Entry;
use craft\enums\PropagationMethod;
use craft\events\ElementEvent;
use craft\web\Controller;
use goldinteractive\sitecopy\SiteCopy;
use yii\web\Response;

/**
 * Api controller
 */
class ApiController extends Controller
{
//    public $defaultAction = 'index';
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;


    public function actionBulkCopyOverlay(): Response
    {
        $request = Craft::$app->getRequest();
        $ids = $request->getParam('ids', []);
        $elementType = $request->getParam('elementType');
        $siteId = $request->getParam('siteId');
        $sourceKey = $request->getParam('sourceKey');

        $elements = $elementType::find()->id($ids)->siteId($siteId)->all();
        $supportedSites = [];

        foreach ($elements as $element) {
            if ($element instanceof Entry && $element->section && !$element->section->getHasMultiSiteEntries()) {
                return $this->asJson([
                    'html'    => '',
                    'success' => false,
                ]);
            }

            $supportedSites = $element->getSupportedSites();
            break;
        }

        $html = Craft::$app->view->renderTemplate(
            'site-copy-x/_cp/bulkCopyOverlay',
            [
                'siteId'          => $siteId,
                'supportedSites'  => $supportedSites,
                'siteCopyEnabled' => 1,
                'selectedSites'   => [],
                'currentSite'     => Craft::$app->getSites()->getCurrentSite(),
            ]
        );

        return $this->asJson([
            'html'    => $html,
            'success' => true,
        ]);
    }

    public function actionBulkCopy(): Response
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $ids = $request->getParam('ids', []);
        $elementType = $request->getParam('elementType');
        $siteId = $request->getParam('siteId');
        $sourceKey = $request->getParam('sourceKey');

        $sitecopySettings = $request->getBodyParam('sitecopy', []);

        try {
            $elements = $elementType::find()->id($ids)->siteId($siteId)->all();

            foreach ($elements as $element) {
                $event = new ElementEvent([
                    'element' => $element,
                    'isNew'   => false,
                ]);

                SiteCopy::getInstance()->sitecopy->syncElementContent($event, $sitecopySettings);
            }

            return $this->asJson(['success' => true]);
        } catch (\Exception $e) {
            Craft::error('Error in bulk copy: ' . $e->getMessage(), __METHOD__);
            return $this->asJson(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
