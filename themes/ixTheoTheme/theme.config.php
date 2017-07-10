<?php
return [
    'extends' => 'ubTueTheme',
    'css' => [
        'compiled.css'
    ],
    'helpers' => [
        'factories' => [
            'record' => 'ixTheo\View\Helper\Root\Factory::getRecord',
        ],
    ],
];
