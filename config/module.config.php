<?php
namespace Netsyos\Cron;

return array(
    'controllers' => array(
        'invokables' => array(
            'Netsyos\Cron\Controller\CronController' => 'Netsyos\Cron\Controller\CronController'
        ),
    ),
    'router' => array(
        'routes' => array(
            'execute' => array(
                'options' => array(
                    'route'    => 'execute <id>',
                    'defaults' => array(
                        'controller' => 'Netsyos\Cron\Controller\CronController',
                        'action'     => 'execute'
                    )
                )
            ),
            'cron' => array(
                'options' => array(
                    'route'    => 'cron',
                    'defaults' => array(
                        'controller' => 'Netsyos\Cron\Controller\CronController',
                        'action'     => 'cron'
                    )
                )
            ),
            'list' => array(
                'options' => array(
                    'route'    => 'list',
                    'defaults' => array(
                        'controller' => 'Netsyos\Cron\Controller\CronController',
                        'action'     => 'list'
                    )
                )
            )
        ),
    ),
    'doctrine' => array(
        'driver' => array(
            __NAMESPACE__ .'_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/' . rtrim(strtr(__NAMESPACE__, '\\', '/')) . '/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                )
            )
        ),
    ),
);
