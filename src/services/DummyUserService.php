<?php

namespace quatrecentquatre\dummydata\services;

use Craft;
use Yii;
use craft\base\Component;
use craft\elements\User;
use Exception;
use quatrecentquatre\dummydata\DummyData;

class DummyUserService extends Component
{

    public $settings;
    
    /**
     * @inheritdoc
     */
    public function init() :void
    {
        parent::init();

        $this->settings = DummyData::getInstance()->getSettings();
    }

    public function clean() 
    {
        $ignoredUsername = $this->settings->users_ignoredUsername ?? [];
        $ignoredDomains = $this->settings->users_ignoredDomains ?? [];
        $passwordDefault = $this->settings->users_defaultPassword ?? 'dummydata';
        $usernameDefault = $this->settings->users_usernameDefault ?? 'dummydata';
        $emailDomainDefault = $this->settings->users_emailDomainDefault ?? 'dummydata.dummy';

        $ignoreUsers = [];

        if (!empty($ignoredDomains)) {
            foreach ($ignoredDomains as $domain) {
                $users = User::find()
                            ->email('*@' . $domain['domain'])
                            ->ids();
                $ignoreUsers = array_merge($ignoreUsers, $users);
            }
        }

        if (!empty($ignoredUsername)) {
            foreach ($ignoredUsername as $username) {
                $users = User::find()
                            ->username($username['username'])
                            ->ids();
                $ignoreUsers = array_merge($ignoreUsers, $users);
            }
        }

        $hashPassword = Craft::$app->getSecurity()->hashPassword($passwordDefault);
        try {     
            $results = Yii::$app->db->createCommand("UPDATE users 
                                        SET username = CONCAT('" . $usernameDefault ."', id), 
                                            email = CONCAT('" . $usernameDefault ."+', id, '@" . $emailDomainDefault . "'),
                                            fullName = CONCAT('" . $usernameDefault ."', id),
                                            firstName = CONCAT('" . $usernameDefault ."', id),
                                            lastName = CONCAT('" . $usernameDefault ."', id),
                                            password = IF(password IS NOT NULL, '" . $hashPassword . "', NULL)
                                        WHERE id NOT IN ( '" . implode( "', '" , $ignoreUsers ) . "' )")
                                        ->execute();

            echo 'Users affected : ' . $results . "\n";
        } catch (Exception $e) {
            Craft::warning("Unable to clean users: {$e->getMessage()}", __METHOD__);
        }

    }
    
}
