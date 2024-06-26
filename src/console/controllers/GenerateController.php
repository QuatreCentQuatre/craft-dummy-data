<?php
/**
 * Dummy Data plugin for Craft CMS 3.x
 *
 * @link      https://quatrecentquatre.com/
 * @copyright Copyright (c) 2024 QuatreCentQuatre
 * @license   https://quatrecentquatre.com/license
 */

namespace quatrecentquatre\dummydata\console\controllers;

use Craft;
use Throwable;
use yii\console\Controller;
use craft\helpers\Console;
use quatrecentquatre\dummydata\DummyData;
use quatrecentquatre\dummydata\services\DummyUserService;
use quatrecentquatre\dummydata\services\DummyCustomFieldService;
use quatrecentquatre\dummydata\services\DummyCustomTableService;

/**
 * Dummy data command
 *
 * @author    QuatreCentQuatre
 * @package   DummyData
 * @since     3.0.0
 */
class GenerateController extends Controller
{
    // Public Properties
    // =========================================================================

     /**
     * @var bool Run the script without prompt
     */
    public $interactive = 1;

    /**
     * @var bool If interative mode is off, backup the database if value = 1
     */
    public $backupdb = 0;

    /**
     * @var bool If interative mode is off, dont clear caches if value = 0
     */
    public $clearcache = 1;

    /**
     * DummyData plugin settings values
     */
    public $settings;

    // Public Methods
    // =========================================================================

    /**
     * @param string $actionID
     *
     * @return array|string[]
     */
    public function options($actionID): array
    {
        return [
            'interactive',
            'backupdb',
            'clearcache',
        ];
    }

    /**
     * Clean database content
     * php craft dummy-data/generate/index 
     */
    public function actionIndex()
    {
        $environment = Craft::$app->getConfig()->env;

        if ($environment === 'production') {
            Console::stdout("Can't run this script in production.\n", Console::FG_RED);
            return;
        }

        $this->settings = DummyData::getInstance()->getSettings();

        //Backup the database
        if((!$this->interactive && $this->backupdb) || 
            ($this->interactive && $this->confirm("Do you want to create a backup of the database before executing the command?"))
        ) {
            $this->backupDb();
        }

        //Validation before overwriting data
        if($this->interactive && !$this->confirm("Are you sure you want to overwrite your data?")){
            Console::stdout("Script ended. No changes have been made.\n", Console::FG_RED);
            return;
        }

        if ($this->settings->clean_users) {
            (new DummyUserService)->clean();
        }

        (new DummyCustomFieldService)->clean();

        (new DummyCustomTableService)->clean();

        //Clear Application cache if needed
        if((!$this->interactive && $this->clearcache) || 
            ($this->interactive && $this->confirm("Do you want to clear the application cache?"))
        ) {
            Craft::$app->elements->invalidateAllCaches();
            Console::stdout("Clearing all caches.\n", Console::FG_GREEN);
        }

        Console::stdout("Script ended.\n");
    }

    private function backupDb()
    {
        try {
            $backupPath = Craft::$app->getDb()->backup();
            Console::stdout("Your backup is located at : " . $backupPath . "\n");
        } catch (Throwable $e) {
            Craft::error('Error backing up the database: ' . $e->getMessage(), __METHOD__);
            
            Console::stdout("An error occurred while backing up the database.\n", Console::FG_RED);
            exit();
        }
    }
    
}
