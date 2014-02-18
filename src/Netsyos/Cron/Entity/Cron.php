<?php
namespace Netsyos\Cron\Entity;

use Cron\CronExpression;
use Doctrine\ORM\Mapping as ORM;
use Netsyos\Common\Entity\AbstractEntity;

/**
 * Task Entity.
 *
 * @ORM\Entity(repositoryClass="Netsyos\Cron\Repository\CronRepository")
 * @ORM\Table(name="netsyos_cron_cron")
 * @property int $id
 * @property string $key
 * @property string $reference
 * @property string $frequency
 */
class Cron extends AbstractEntity
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", unique=true)
     */
    protected $key;

    /**
     * @ORM\Column(type="string");
     */
    protected $frequency;

    /**
     * @ORM\Column(type="string");
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
     * Populate from an array.
     *
     * @param array $data
     */
    public function exchangeArray(array $data) {
        $this->id = (isset($data['id'])) ? $data['id'] : null;
        $this->key = (isset($data['key'])) ? $data['key'] : null;
        $this->frequency = (isset($data['frequency'])) ? $data['frequency'] : null;
        $this->service = (isset($data['service'])) ? $data['service'] : null;
        $this->callback = (isset($data['callback'])) ? $data['callback'] : null;
        $this->arguments = (isset($data['arguments'])) ? $data['arguments'] : null;
    }

    /**
     * @param string $currentTime
     * @param int $nth
     * @param bool $allowCurrentDate
     * @return \DateTime
     */
    public function getNextExecutionDate($currentTime = 'now', $nth = 0, $allowCurrentDate = false) {
        $cronExpression = CronExpression::factory($this->frequency);
        return $cronExpression->getNextRunDate($currentTime, $nth, $allowCurrentDate);
    }
}
