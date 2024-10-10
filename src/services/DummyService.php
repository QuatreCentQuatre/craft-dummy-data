<?php

namespace quatrecentquatre\dummydata\services;

use Craft;
use craft\base\Component;
use quatrecentquatre\dummydata\DummyData;

class DummyService extends Component
{

    public $settings;

    public $tablePrefix;
    
    /**
     * @inheritdoc
     */
    public function init() :void
    {
        parent::init();

        $this->settings = DummyData::getInstance()->getSettings();

        $db = Craft::$app->getDb();
        $this->tablePrefix = $db->tablePrefix;

    }

}
