<?php
namespace Netsyos\Cron\Service;

use Cron\CronExpression;
use Doctrine\ORM\Query;
use Netsyos\Common\Service\AbstractService;
use Netsyos\Cron\Entity\Execution;
use Netsyos\Cron\Entity\Cron;

class CronService extends AbstractService
{
    public $logFile = '/data/log/cron.log';

    public function cron() {
        $this->processExecutions();
    }

    public function execute($id) {
        $params = array(
            'id' => $id,
            'status' => array(Execution::STATUS_PLANNED, Execution::STATUS_FORCED)
        );

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from('Netsyos\Cron\Entity\Execution', 'e')
            ->where('e.status IN (:status)')
            ->andWhere('e.id = :id')
            ->orderBy('e.scheduleTime', 'DESC');

        $executions = $qb->setParameters($params)->getQuery()->getResult();

        if (count($executions)) {
            $execution = $executions[0];
            $execution->status = Execution::STATUS_RUNNING;
            $execution->executeTime = new \DateTime();
            $this->getEntityManager()->persist($execution);
            $this->getEntityManager()->flush();
            try {
                if (count($execution->service) && count($execution->callback)) {
                    $service = $this->getServiceLocator()->get($execution->service);
                    $method = $execution->callback;
                    if(is_callable(array($service, $method))){
                        $execution->stackTrace = json_encode(call_user_func(array($service, $method), $execution->arguments));
                    }
                } elseif (count($execution->callback)) {
                    if(is_callable($execution->callback)){
                        $execution->stackTrace = json_encode(call_user_func($execution->callback, $execution->arguments));
                    }
                }
                $execution->status = Execution::STATUS_DONE;
            } catch (\Exception $e) {
                $execution->errorMsg = $e->getMessage();
                $execution->stackTrace = $e->getTrace();
                $execution->status = Execution::STATUS_ERROR;
            }
            $execution->finishTime = new \DateTime();
            $this->getEntityManager()->persist($execution);
            $this->getEntityManager()->flush();
        }
        return $id;
    }

    public function addCron($key, $frequency, $service, $callback, $arguments) {
        $fields = array();
        CronExpression::factory($frequency);
        $fields['key'] = $key;
        $fields['frequency'] = $frequency;
        $fields['service'] = $service;
        $fields['callback'] = $callback;
        $fields['arguments'] = $arguments;
        $cron = $this->getRepository('Cron')->create($fields);
        $this->getEntityManager()->flush();
    }

    /**
     * @param $scheduleTime
     * @param $key
     * @param $service
     * @param $callback
     * @param $arguments
     *
     * @return \Netsyos\Cron\Entity\Execution
     */
    public function addExecution($scheduleTime, $key, $service, $callback, $arguments, $status = Execution::STATUS_PLANNED) {
        $fields = array();
        $fields['key'] = $key;
        $fields['scheduleTime'] = $scheduleTime;
        $fields['service'] = $service;
        $fields['callback'] = $callback;
        $fields['arguments'] = $arguments;
        $fields['status'] = $status;
        $execution = $this->getRepository('Execution')->create($fields);
        $this->getEntityManager()->flush();
        return $execution;
    }

    public function addForcedExecution($cron, $date)
    {
        $this->addExecution($date, $cron->key, $cron->service, $cron->callback, $cron->arguments, Execution::STATUS_FORCED);
    }

    public function addUniqueForcedExecution($cron, $date)
    {
        $now = new \DateTime();
        $params = array(
            'key' => $cron->key,
            'status' => Execution::STATUS_FORCED
        );
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from('Netsyos\Cron\Entity\Execution', 'e')
            ->where('e.status = :status')
            ->andWhere('e.key = :key');

        $executions = $qb->setParameters($params)->getQuery()->getResult();

        if (count($executions) == 0) {
            $this->addExecution($date, $cron->key, $cron->service, $cron->callback, $cron->arguments, Execution::STATUS_FORCED);
        }
    }

    /**
     * @param \Netsyos\Cron\Entity\Cron $cron
     */
    public function generateCronExecutions(Cron $cron, $startDate = 'now', $endDate = 'next'){
        $endDate = $endDate !== 'next' ? $endDate : $cron->getNextExecutionDate($startDate);
        $scheduleTime = $cron->getNextExecutionDate($startDate);
        $i = 1;
        while ($endDate >= $scheduleTime) {
            $this->addExecution($scheduleTime, $cron->key, $cron->service, $cron->callback, $cron->arguments);
            $scheduleTime = $cron->getNextExecutionDate($startDate, $i);
        }
    }

    public function processExecutions() {
        $crons = $this->getRepository('Cron')->findAll();
        $this->getEntityManager()->clear();
        foreach ($crons as $cron) {
            $executed = $this->handlePlannedTaskes($cron);
            $this->handleForcedTaskes($cron, $executed);
        }
    }

    protected function handlePlannedTaskes($cron)
    {
        $now = new \DateTime();
        $executed = false;
        $params = array(
            'key' => $cron->key,
            'status' => Execution::STATUS_PLANNED
        );
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from('Netsyos\Cron\Entity\Execution', 'e')
            ->where('e.status = :status')
            ->andWhere('e.key = :key')
            ->orderBy('e.scheduleTime', 'DESC');
        $executions = $qb->setParameters($params)->getQuery()->getResult();
        if (count($executions)) {
            foreach ($executions as $execution) {
                if ($cron->active) {
                    $ce = CronExpression::factory($cron->frequency);
                    if ($execution->scheduleTime <= $now) {
                        if (!$executed) {
                            if ($ce->isDue($execution->scheduleTime)) {
                                system($this->getExecuteCommand($execution->id));
                                $this->generateCronExecutions($cron);
                                $executed = true;
                            } else {
                                $execution->status = Execution::STATUS_CANCELLED;
                                $execution->stackTrace = 'frequency changed';
                                $this->getEntityManager()->persist($execution);
                                $this->generateCronExecutions($cron);
                            }
                        } else {
                            $execution->status = Execution::STATUS_SKIPPED;
                            $this->getEntityManager()->persist($execution);
                        }
                    } elseif ($execution->scheduleTime > $ce->getNextRunDate()) {
                        $execution->status = Execution::STATUS_CANCELLED;
                        $execution->stackTrace = 'frequency changed';
                        $this->getEntityManager()->persist($execution);
                        $this->generateCronExecutions($cron);
                    }
                } else {
                    $execution->status = Execution::STATUS_CANCELLED;
                    $execution->stackTrace = 'cron disabled';
                    $this->getEntityManager()->persist($execution);
                }
            }
        } elseif ($cron->active) {
            $this->generateCronExecutions($cron);
        }
        $this->getEntityManager()->flush();

        return $executed;
    }

    protected function handleForcedTaskes($cron, $executed = false)
    {
        $now = new \DateTime();
        $params = array(
            'key' => $cron->key,
            'scheduleTime' => $now,
            'status' => Execution::STATUS_FORCED
        );
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from('Netsyos\Cron\Entity\Execution', 'e')
            ->where('e.status = :status')
            ->andWhere('e.key = :key')
            ->andWhere('e.scheduleTime <= :scheduleTime')
            ->orderBy('e.scheduleTime', 'DESC');

        $executions = $qb->setParameters($params)->getQuery()->getResult();
        if (count($executions)) {
            foreach ($executions as $execution) {
                if (!$executed) {
                    system($this->getExecuteCommand($execution->id));
                    $executed = true;
                } else {
                    $execution->status = Execution::STATUS_SKIPPED;
                    $this->getEntityManager()->persist($execution);
                }
            }
        }
        $this->getEntityManager()->flush();
    }

    public function getPath() {
        $config = $this->getServiceLocator()->get('config');
        $path = array_key_exists('netsyos-cron', $config) ? $config['netsyos-cron']['indexPath'] : realpath(__DIR__ . '/../../../../../../../');
        return $path;
    }

    public function getIndexPath() {
        $path = $this->getPath() . '/public/index.php';
        return $path;
    }

    public function getExecuteCommand($id) {
        return '((php ' . $this->getIndexPath() . ' execute ' . $id . ') >>' . $this->getPath() . $this->logFile . ' 2>&1) &';
    }
}