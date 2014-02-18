<?php
namespace Netsyos\Cron\Validator;

use Cron\CronExpression;
use Zend\Validator\AbstractValidator;

class CronExpressionValidator extends AbstractValidator
{
    const CRON = 'cron';

    protected $messageTemplates = array(
        self::CRON => "'%value%' is not a cron expression (mm hh jj MMM JJJ)"
    );

    public function isValid($value)
    {
        $this->setValue($value);
        try {
            CronExpression::factory($value);
        } catch (\Exception $e) {
            $this->error(self::CRON);
            return false;
        }
        return true;
    }
}