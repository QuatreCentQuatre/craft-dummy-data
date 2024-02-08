# Dummy Data

Dummy Data is a CraftCms plugin used to anonymize sensible data in the CraftCms database with dummy data.

## Requirements

This plugin requires : 
- Craft CMS 3.5.0 or later. (Not compatible for Craft 5 yet.)
- PHP 8.0.2 or later.
- Mysql database

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “Dummy Data”. Then press “Install”.

#### With Composer

Open your terminal and run the following commands:

```bash
# tell Composer to load the plugin
composer require quatrecentquatre/craft-dummy-data

# tell Craft to install the plugin
./craft plugin/install craft-dummy-data
```

## How it works

Create a file to configure your project : config/dummy-data.php

```
<?php 

return [
    'clean_users' => 1,
    'users_defaultPassword' => 'dummydata',
    'users_ignoredUsername' => ['admin'],
    'users_ignoredDomains' => [],
    'users_usernameDefault' => 'dummy-data',
    'users_emailDomainDefault' => 'dummydata.dummy',
    'custom_fields' => [
        ['type' => 'text', 'handle' => 'craft_field_handle'],
    ],
    'custom_tables' => [
        [
            'name' => 'newsletter_users',
            'custom_fields' => [
                ['type' => 'email', 'handle' => 'email'],
            ],
        ]
    ]
];
```

### User

In the plugin configuration, you can activate the option to anonymize the user data. You can choose a defaut password, username and domain name to generate the content.

In the configuration file, you can also list an array of username or domain to ignore.

### Custom Fields

Create an array of the fields that needed to be anonymize with the type of content that the data should be replace with.

The script will replace all the rows in the database that have data in it. 

The handle key is the handle of your field in your Craft control panel. (The script will get the field prefix/suffix if needed from the database.)

### Custom Tables

You can also specify multiple custom tables and select each column that needs to be anonymized.

### Data Types

#### List of string data types supported

- address
- city
- date
- email
- firstName
- ip
- lastName
- latitude
- longitude
- name
- phoneNumber
- postcode
- secondaryAddress
- stateAbbr
- streetName
- streetAddress
- text
- userName
- url
- userAgent

For more informations about what kind of data each type return, you can look up the Faker PHP documentation. [Faker PHP](https://fakerphp.github.io/formatters/)


#### Files

- compressed (.zip)
- excel (.xlsx)
- image (.jpg)
- pdf (.pdf)
- txt (.txt)
- word (.docx)

#### Custom value

It is possible to add custom value to a specific field. The type will be custom and an extra "value" key will be add to the array.

```
'custom_fields' => [
    ['type' => 'custom', 'handle' => 'craft_field_handle', 'value' => 'Custom value'],
],
```

### Run the script

Run the following command in your terminal.

```
php craft dummy-data/generate
```

### Roadmap

- svg logo 4c4
- Add action in CMS to launch script
- Add complex mode. Modify each entries individually with different data with queue system

Develop by [QuatreCentQuatre](https://www.quatrecentquatre.com)
