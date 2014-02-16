<?php
namespace Netsyos\Cron\Repository\System;

use Netsyos\Common\Repository\EntityRepository;

class ExecutionRepository extends EntityRepository
{
    public function create($fields) {
        $fields['createTime'] = new \DateTime();
        return parent::create($fields);
    }
}