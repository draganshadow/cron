<?php
namespace Netsyos\Cron\Entity;

use Cron\CronExpression;
use Doctrine\ORM\Mapping as ORM;
use Netsyos\Common\Entity\AbstractEntity;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFilterFactory;

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
class Cron extends AbstractEntity implements InputFilterAwareInterface
{
    protected $inputFilter;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $key;

    /**
     * @ORM\Column(type="string");
     */
    protected $frequency;

    /**
     * @ORM\Column(type="string", nullable=true);
     */
    protected $service;

    /**
     * @ORM\Column(type="string");
     */
    protected $callback;

    /**
     * @ORM\Column(type="json_array", nullable=true);
     */
    protected $arguments;

    /**
     * @ORM\Column(type="boolean");
     */
    protected $active = false;

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
        $this->active = (isset($data['active'])) ? $data['active'] : null;
    }

    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory = new InputFilterFactory();
            $inputFilter->add($factory->createInput(array(
                'name' => 'frequency',
                'required' => true,
                'filters' => array( array('name' => 'StripTags'), array('name' => 'StringTrim'), ),
                'validators' => array( array('name' => 'Netsyos\Cron\Validator\CronExpressionValidator', ), ), )));
            $inputFilter->add($factory->createInput(array(
                'name' => 'active',
                'required' => true,
            )));
            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }

    /**
     * @param \Zend\InputFilter\InputFilterInterface $inputFilter
     * @return void|\Zend\InputFilter\InputFilterAwareInterface
     * @throws \Exception
     */
    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Filter are in the class");
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

    /**
     * @param mixed $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return mixed
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param mixed $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return mixed
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param mixed $frequency
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    }

    /**
     * @return mixed
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param mixed $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }


}
