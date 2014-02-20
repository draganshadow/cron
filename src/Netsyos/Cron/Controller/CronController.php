<?php
namespace Netsyos\Cron\Controller;

use Netsyos\Common\Controller\AbstractController;

class CronController extends AbstractController
{
    public function executeAction()
    {
        $request = $this->getRequest();
        $id = $request->getParam('id', false);
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (get_class($request) != 'Zend\Console\Request'){
            throw new \RuntimeException('You can only use this action from a console!');
        }
        if (!$id) {
            throw new \RuntimeException('You must specify an execution id');
        }
        $result = $this->getServiceLocator()->get('netsyos-cron')->execute($id);
        return $result;
    }

    public function cronAction()
    {
        $request = $this->getRequest();
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (get_class($request) != 'Zend\Console\Request'){
            throw new \RuntimeException('You can only use this action from a console!');
        }
        $result = $this->getServiceLocator()->get('netsyos-cron')->cron();
        return $result;
    }

    public function listAction()
    {
        $request = $this->getRequest();
        // Make sure that we are running in a console and the user has not tricked our
        // application into running this action from a public web server.
        if (get_class($request) != 'Zend\Console\Request'){
            throw new \RuntimeException('You can only use this action from a console!');
        }
        $crons = $this->getRepository('Cron')->findAll();
        foreach ($crons as $cron) {
            $result = $cron->frequency . ' ' . $cron->key . PHP_EOL;
        }
        return $result;
    }
}
