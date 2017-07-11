<?php
return [
    'extends' => 'ubTueTheme',
    'css' => [
        'compiled.css'
    ],
    'helpers' => [
        'factories' => [
            'citation' => 'ixTheo\View\Helper\Root\Factory::getCitation',
            'piwik' => 'ixTheo\View\Helper\Root\Factory::getPiwik',
            'record' => 'ixTheo\View\Helper\Root\Factory::getRecord',
        ],
    ],
];
