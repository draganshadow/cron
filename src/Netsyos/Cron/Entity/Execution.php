<?php
namespace Netsyos\Cron\Entity;

use Doctrine\ORM\Mapping as ORM;
use Netsyos\Common\Entity\AbstractEntity;

/**
 * Task Entity.
 *
 * @ORM\Entity(repositoryClass="Netsyos\Cron\Repository\ExecutionRepository")
 * @ORM\Table(name="netsyos_cron_execution")
 * @property int $id
 * @property string $key
 * @property string $status
 * @property \DateTime $createTime
 * @property \DateTime $scheduleTime
 */
class Execution extends AbstractEntity
{
    const STATUS_PLANNED    = 'planned';
    const STATUS_FORCED     = 'forced';
    const STATUS_CANCELLED  = 'cancelled';
    const STATUS_SKIPPED    = 'skipped';
    const STATUS_RUNNING    = 'running';
    const STATUS_DONE       = 'done';
    const STATUS_ERROR      = 'error';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $key;

    /**
     * @ORM\Column(type="string", nullable=true);
     */
    protected $service;

    /**
     * @ORM\Column(type="string");
     */
    protected $callback;

    /**
     * @ORM\Column(type="json_array");
     */
    protected $arguments;

    /**
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $errorMsg;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $stackTrace;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createTime;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $scheduleTime;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $executeTime;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $finishTime;

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function exchangeArray(array $data) {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->key = (isset($data['key'])) ? $data['key'] : null;
        $this->service = (isset($data['service'])) ? $data['service'] : null;
        $this->callback = (isset($data['callback'])) ? $data['callback'] : null;
        $this->arguments = (isset($data['arguments'])) ? $data['arguments'] : null;
        $this->status = (isset($data['status'])) ? $data['status'] : null;
        $this->createTime = (isset($data['createTime'])) ? $data['createTime'] : null;
        $this->scheduleTime = (isset($data['scheduleTime'])) ? $data['scheduleTime'] : null;
        $this->executeTime = (isset($data['executeTime'])) ? $data['executeTime'] : null;
        $this->finishTime = (isset($data['finishTime'])) ? $data['finishTime'] : null;
    }
}
