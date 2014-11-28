<?php

return array(
        // this is used in contact page

        'siteUrl' => 'https://bugkick.com',
        'adminEmail' => 'notifications@bugkick.com',
        'passwordSalt' => require($paramsDir . 'salt.php'),
        'bcryptWorkFactor' => 10,
        'profileImageUrl' => 'images/profile_img/',
        'profileImageThumbUrl' => 'images/profile_img/thumb/',
        'companyLogoUrl' => 'images/company_logo/',
        'companyLogoThumbUrl' => 'images/company_logo/thumb/',
        // leave empty to use php mail(), or 'ses' - to use Amazon SES
        //'emailService' => 'ses','sqs'
        'emailService' => 'ses',
        'amazon' => require($paramsDir . 'amazon.php'),
        //	Payments via stripe.com
        'stripe' => require($currDir . 'stripe.php'),
        //	Payment plans
        'plans' => require($currDir . 'plans.php'),
        'facebook' => require($currDir . 'facebook.php'),
        'github' => require($paramsDir . 'github.php'),
        'ssl' => require($paramsDir . 'ssl.php'),
        //number of labels to be shown on bug index page
        'label_number_shown' => 3,
        'node' => require($currDir . 'node.php'),
        //These status presets will be created for newly registered companies
        'statuses' => array(
            array(
                'label' => 'Testing',
                'status_color' => '#D97925',
                'is_visible_by_default' => '1',
            ),
            array(
                'label' => 'New',
                'status_color' => '',
                'is_visible_by_default' => '1',
            ),
            array(
                'label' => 'Resolved',
                'status_color' => '#181F24',
                'is_visible_by_default' => '1',
            ),
            array(
                'label' => 'In Progress',
                'status_color' => '#025159',
                'is_visible_by_default' => '1',
            ),
            array(
                'label' => 'On Hold',
                'status_color' => '#6E7273',
                'is_visible_by_default' => '0',
            ),
        ),
        //These label presets will be created for newly registered companies
        'labels' => array(
            'Feature' => '#359ce4',
            'Bug' => '#d5ce45',
            'Enhancement' => '#74dc42',
            'Proposal' => '#b435cf',
            'Design' => '#49ddab',
        ),
        //default preset of label colors
        'labelDefaultColors' => array(
            '#2D9BE7',
            '#2ECBE7',
            '#3FDEAA',
            '#70DE33',
            '#B1DE2F',
            '#D5D035',
            '#E0B730',
            '#E0822C',
            '#C83528',
            '#DF2C51',
            '#DD2B9D',
            '#B42AD0',
        ),
        //if set to false - we send both email and node notification,
        //else node message will be shown if user is online, email will be send if not.
        'skipEmailIfNodeReceived' => false,
        'bugkickApiSettings' => require($currDir . 'bugkick-api-settings.php'),
        //Files Storage: one of these options - 's3', 'local'
        'storageType' => require($paramsDir . 'storage-type.php'),
        //projects number available for free companies
        'projects_number_for_free' => 3,
        //MixPanel events tracking
        'mixpanel' => require($paramsDir . 'mixpanel.php'),
        //Box.net settings
        'box' => require($paramsDir . 'box.php'),
    ),