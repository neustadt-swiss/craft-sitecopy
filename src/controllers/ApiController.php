<?php

namespace teamnovu\sitecopy\controllers;

use Craft;
use craft\elements\Entry;
use craft\events\ElementEvent;
use craft\web\Controller;
use teamnovu\sitecopy\SiteCopy;
use yii\web\Response;

class ApiController extends Controller
{
    protected array|int|bool $allowAnonymous = self::ALLOW_ANONYMOUS_NEVER;

    public function actionBulkCopyOverlay(): Response
    {
        $request = Craft::$app->getRequest();
        $ids = $request->getParam('ids', []);
        $elementType = $request->getParam('elementType');
        $siteId = $request->getParam('siteId');

        $elements = $elementType::find()->id($ids)->siteId($siteId)->all();
        $supportedSites = [];

        foreach ($elements as $element) {
            if ($element instanceof Entry && $element->section && !$element->section->getHasMultiSiteEntries()) {
                return $this->asJson([
                    'html'    => '',
                    'success' => false,
                ]);
            }

            $supportedSites = Craft::$app->getSites()->getAllSites();
            break;
        }

        $elementTypeClass = substr($elementType, strrpos($elementType, '\\') + 1);

        $html = Craft::$app->view->renderTemplate(
            'site-copy-x/_cp/bulkCopyOverlay',
            [
                'siteId'          => $siteId,
                'supportedSites'  => $supportedSites,
                'elementCount'    => count($elements),
                'elementType'     => $elementTypeClass,
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

    public function actionCheckSiteAvailability(): Response
    {
        $request = Craft::$app->getRequest();
        $elementId = $request->getParam('elementId');
        $elementType = $request->getParam('elementType');
        $sourceSiteId = $request->getParam('sourceSiteId');

        $element = Craft::$app->elements->getElementById($elementId, null, $sourceSiteId);

        if (!$element) {
            return $this->asJson([
                'success' => false,
                'error'   => 'Element not found',
            ]);
        }

        $allSites = Craft::$app->getSites()->getAllSites();
        $user = Craft::$app->getUser()->getIdentity();
        $availableSites = [];
        $unavailableSites = [];

        foreach ($allSites as $site) {
            if (!$user->can('editsite:' . $site->uid)) {
                continue;
            }

            $siteElement = Craft::$app->elements->getElementById(
                $element->id,
                null,
                $site->id
            );

            if ($siteElement) {
                $availableSites[] = [
                    'id'   => $site->id,
                    'name' => $site->name,
                ];
            } else {
                $unavailableSites[] = [
                    'id'   => $site->id,
                    'name' => $site->name,
                ];
            }
        }

        return $this->asJson([
            'success'          => true,
            'availableSites'   => $availableSites,
            'unavailableSites' => $unavailableSites,
        ]);
    }

    public function actionBulkCopy(): Response
    {
        $this->requirePostRequest();
        $request = Craft::$app->getRequest();
        $ids = $request->getParam('ids', []);
        $elementType = $request->getParam('elementType');
        $siteId = $request->getParam('siteId');

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
