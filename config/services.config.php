<?php
return array(
    'factories' => array(
        'netsyos-cron-log' => function ($sm) {
                $filename = 'cron.log';
                $log = new \Zend\Log\Logger();
                $writer = new \Zend\Log\Writer\Stream('./data/log/' . $filename);
                $log->addWriter($writer);
                return $log;
            },
        'netsyos-cron' =>  function($sm) {
                $logger = $sm->get('netsyos-cron-log');
                $service = new Netsyos\Cron\Service\CronService();
                $service->setLogger($logger);
                return $service;
            },
    ),
);
