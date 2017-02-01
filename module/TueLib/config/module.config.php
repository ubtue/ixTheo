<?php
namespace TueLib\Module\Configuration;

$config = array(
    'controllers' => array(
        'invokables' => array(
            'proxy' => 'TueLib\Controller\ProxyController',
            'pdaproxy' => 'TueLib\Controller\PDAProxyController'
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
            ),
            'pdaproxy-load' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/PDAProxy/Load',
                    'defaults' => array(
                        'controller' => 'PDAProxy',
                        'action'     => 'Load',
                    )
                )
            )
        )
    )
);

return $config;
