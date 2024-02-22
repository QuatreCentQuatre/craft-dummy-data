<?php

namespace quatrecentquatre\dummydata;

use Craft;
use craft\web\View;
use craft\events\TemplateEvent;
use craft\base\Model;
use craft\base\Plugin;
use quatrecentquatre\dummydata\models\Settings;
use quatrecentquatre\dummydata\helpers\DummyDataHelpers;
use yii\base\Event;

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
    public $schemaVersion = '1.0.0';
    public $hasCpSettings = true;

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
                $e->template === 'settings/plugins/_settings' &&
                $e->variables['plugin'] === $this
            ) {
                // Add the tabs
                $e->variables['tabs'] = [
                    ['label' => 'Users', 'url' => '#settings-tab-users'],
                    ['label' => 'Custom Fields', 'url' => '#settings-tab-custom-fields'],
                ];
            }
        });

        $this->attachEventHandlers();
    }

    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate('dummy-data/_settings.twig', [
            'plugin' => $this,
            'settings' => $this->getSettings(),
            'tabs' => [
                ['label' => 'Users', 'url' => '#settings-tab-users'],
                ['label' => 'Custom Fields', 'url' => '#settings-tab-custom-fields'],
            ],
            'fields_list' => (new DummyDataHelpers)->getAllFieldsCraft(),
            'sections_list' => (new DummyDataHelpers)->getAllSectionsCraft(),
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
