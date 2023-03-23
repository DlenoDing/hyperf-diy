<?php

declare(strict_types=1);

namespace App\AsyncQueue\Job;

use Dleno\CommonCore\Base\AsyncQueue\BaseJob;

/**
 * TestJob
 * Class TestJob
 * @package App\AsyncQueue\Job
 */
class TestJob extends BaseJob
{
    protected $queue = 'test';

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function handle()
    {

    }
}