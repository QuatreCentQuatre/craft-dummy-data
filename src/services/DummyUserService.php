<?php

namespace quatrecentquatre\dummydata\services;

use Craft;
use Yii;
use Exception;
use craft\elements\User;
use craft\helpers\Console;
use quatrecentquatre\dummydata\services\DummyService;

class DummyUserService extends DummyService
{

    public function clean() 
    {
        $ignoredUsername = $this->settings->users_ignoredUsername ?? [];
        $ignoredDomains = $this->settings->users_ignoredDomains ?? [];
        $passwordDefault = $this->settings->users_defaultPassword ?? 'dummydata';
        $usernameDefault = $this->settings->users_usernameDefault ?? 'dummydata';
        $emailDomainDefault = $this->settings->users_emailDomainDefault ?? 'dummydata.dummy';

        $ignoreUsers = [];

        //Get a list of ids of ignored users with specific domain in their email
        if (!empty($ignoredDomains)) {
            foreach ($ignoredDomains as $domain) {
                $users = User::find()
                            ->email('*@' . $domain['domain'])
                            ->ids();
                $ignoreUsers = array_merge($ignoreUsers, $users);
            }
        }

        //Get a list of ids of ignored users from their username
        if (!empty($ignoredUsername)) {
            foreach ($ignoredUsername as $username) {
                $users = User::find()
                            ->username($username['username'])
                            ->ids();
                $ignoreUsers = array_merge($ignoreUsers, $users);
            }
        }

        //Generate a new default password for user
        $hashPassword = Craft::$app->getSecurity()->hashPassword($passwordDefault);


        try {     
            $results = Yii::$app->db->createCommand("UPDATE ".$this->tablePrefix."users 
                                                    SET username = CONCAT('" . $usernameDefault ."', id), 
                                                        email = CONCAT('" . $usernameDefault ."+', id, '@" . $emailDomainDefault . "'),
                                                        fullName = CONCAT('" . $usernameDefault ."', id),
                                                        firstName = CONCAT('" . $usernameDefault ."', id),
                                                        lastName = CONCAT('" . $usernameDefault ."', id),
                                                        password = IF(password IS NOT NULL, '" . $hashPassword . "', NULL)
                                                    WHERE id NOT IN ( '" . implode( "', '" , $ignoreUsers ) . "' )")
                                        ->execute();

            Console::stdout("Users affected : " . $results . "\n");
        } catch (Exception $e) {
            Craft::warning("Unable to clean users: {$e->getMessage()}", __METHOD__);
        }

    }
    
}
