<?php
namespace TueLib\Module\Configuration;

$config = array(
    'controllers' => array(
        'invokables' => array(
            'proxy' => 'TueLib\Controller\ProxyController',
            'ajax' => 'TueLib\Controller\AjaxController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'proxy-load' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/Proxy/Load',
                    'defaults' => array(
                        'controller' => 'Proxy',
                        'action'     => 'Load',
                    )
                )
            )
        ),
        'proxy-load' => array(
            'type' => 'Zend\Mvc\Router\Http\Literal',
            'options' => array(
                'route'    => '/Ajax/Feedback',
                'defaults' => array(
                    'controller' => 'Ajax',
                    'action'     => 'Feedback',
                )
            )
        )
    )
);

return $config;
