<?php

namespace quatrecentquatre\dummydata;

use Craft;
use yii\base\Event;
use craft\web\View;
use craft\events\TemplateEvent;
use craft\base\Model;
use craft\base\Plugin;
use quatrecentquatre\dummydata\models\Settings;
use quatrecentquatre\dummydata\helpers\DummyDataHelpers;

/**
 * Dummy Data plugin
 *
 * @method static DummyData getInstance()
 * @method Settings getSettings()
 * @author QuatreCentQuatre <production@quatrecentquatre.com>
 * @copyright QuatreCentQuatre
 * @license https://craftcms.github.io/license/ Craft License
 */
class DummyData extends Plugin
{
    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [],
        ];
    }

    public function init(): void
    {
        parent::init();

        Event::on(View::class, View::EVENT_BEFORE_RENDER_TEMPLATE, function(TemplateEvent $e) {
            if (
                $e->template === 'settings/plugins/_settings.twig' &&
                $e->variables['plugin'] === $this
            ) {
                // Add the tabs
                $e->variables['tabs'] = [
                    ['label' => 'Users', 'url' => '#settings-tab-users'],
                    ['label' => 'Custom Fields', 'url' => '#settings-tab-custom-fields'],
                ];

            }
        });

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function() {
            $this->attachEventHandlers();
        });

        
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('dummy-data/_settings.twig', [
            'plugin' => $this,
            'fields_list' => (new DummyDataHelpers)->getAllFieldsCraft(),
            'sections_list' => (new DummyDataHelpers)->getAllSectionsCraft(),
            'settings' => $this->getSettings(),
        ]);
    }

    private function attachEventHandlers(): void
    {
        $request = Craft::$app->getRequest();
        if ($request->getIsConsoleRequest()) {
            $this->controllerNamespace = 'quatrecentquatre\dummydata\console\controllers';
        }
    }
}
