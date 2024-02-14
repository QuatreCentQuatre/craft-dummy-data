<?php

namespace quatrecentquatre\dummydata\models;

use Craft;
use craft\base\Model;

/**
 * Dummy Data settings
 */
class Settings extends Model
{
    public $clean_users = false;
    public $users_defaultPassword = 'dummydata';
    public $users_ignoredUsername = [];
    public $users_ignoredDomains = [];
    public $users_usernameDefault = 'dummydata';
    public $users_emailDomainDefault = 'dummydata.dummy';
    public $custom_fields = [];
    public $section_title = [];
    public $custom_tables = [];

    public function defineRules(): array
    {
        return [
            [['clean_users'], 'number'],
            [['users_defaultPassword', 'users_usernameDefault', 'users_emailDomainDefault'], 'string'],
        ];
    }
}
