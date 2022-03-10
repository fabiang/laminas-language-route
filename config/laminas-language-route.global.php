<?php

use Fabiang\LaminasLanguageRoute\Options\Service\LanguageRouteOptionsFactory;

// Do not edit below this line
// ===========================

return [
    LanguageRouteOptionsFactory::CONFIG_KEY => [
        /**
         * Array of languages allowed for language route. The key is the prefix
         * which is attached to the url (e.g. en), the value is the associated
         * locale  (e.g. 'en_US')
         */
        'languages' => [
            'en' => 'en_US',
            'de' => 'de_DE'
        ],
        /**
         * This route name will be used if no RouteMatch instance is provided to
         * the languageSwitch ViewHelper. This happens for example if a 404 error
         * occurs.
         * @var string
         */
        'homeRoute' => 'home'
    ],
];
