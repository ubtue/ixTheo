<?php
return [
    'extends' => 'ubTueTheme',
    'css' => [
        'compiled.css'
    ],
    'helpers' => [
        'factories' => [
            'piwik' => 'VuFind\View\Helper\Root\Factory::getPiwik',
            'record' => 'ixTheo\View\Helper\Root\Factory::getRecord',
        ],
    ],
];
