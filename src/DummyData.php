<?php

namespace quatrecentquatre\dummydata;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use quatrecentquatre\dummydata\models\Settings;

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
