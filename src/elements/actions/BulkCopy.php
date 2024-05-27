<?php

namespace goldinteractive\sitecopy\elements\actions;

use Craft;
use craft\base\ElementAction;

/**
 * Bulk Copy element action
 */
class BulkCopy extends ElementAction
{
    public static function displayName(): string
    {
        return Craft::t('site-copy-x', 'Bulk Copy');
    }

    public function getTriggerHtml(): ?string
    {
        Craft::$app->getView()->registerJsWithVars(fn($type) => <<<JS
        (() => {
            const openSitecopyModal = (elementIndex, htmlContent) => {
                const \$html = $('<div class="modal">' + htmlContent + '</div>');
                const modal = new Garnish.Modal(\$html);
                modal.show();

                \$html.find('.cancel').on('click', () => {
                    modal.hide();
                });

                \$html.find('.submit').on('click', (e) => {
                    e.preventDefault();
                    submitSitecopyForm(elementIndex, \$html.find('form')[0], modal);
                });
            };

            const submitSitecopyForm = (elementIndex, form, modal) => {
                const formData = new FormData(form);
                formData.append('ids', elementIndex.getSelectedElementIds());
                formData.append('elementType', elementIndex.elementType);
                formData.append('siteId', elementIndex.siteId);
                formData.append('sourceKey', elementIndex.sourceKey);

                Craft.sendActionRequest('POST', 'site-copy-x/api/bulk-copy', { data: formData })
                    .then((res) => {
                        if (res.data.success) {
                            Craft.elementIndex.updateElements();
                            modal.hide();
                        } else {
                            console.error('Failed to copy: ', res);
                        }
                    })
                    .catch((err) => {
                        console.error('Error during form submission: ', err);
                    });
            };

            new Craft.ElementActionTrigger({
                type: $type,
                bulk: true,
                validateSelection: (selectedItems, elementIndex) => {
                  return true;
                },
                activate: (selectedItems, elementIndex) => {
                    elementIndex.setIndexBusy();
                    const ids = elementIndex.getSelectedElementIds();

                    Craft.sendActionRequest('POST', 'site-copy-x/api/bulk-copy-overlay', {
                        data: {
                            ids: ids,
                            elementType: elementIndex.elementType,
                            siteId: elementIndex.siteId,
                            sourceKey: elementIndex.sourceKey
                        }
                    }).then((res) => {
                        if (res.data.success) {
                            if (res.data.html) {
                                openSitecopyModal(elementIndex, res.data.html);
                            } else {
                                Craft.elementIndex.updateElements();
                            }
                        }
                    }).catch((err) => {
                        console.error('Error during overlay request: ', err);
                    }).finally(() => {
                        elementIndex.setIndexAvailable();
                    });
                }
            });
            })();
        JS, [static::class]);

        return null;
    }
}
