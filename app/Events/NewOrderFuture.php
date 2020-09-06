<?php

namespace App\Events;

use App\Ut_order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class NewOrderFuture implements ShouldQueue
{
    use SerializesModels;

    public $order;

    /**
     * The name of the connection the job should be sent to.
     *
     * @var string|null
     */
    public $connection = 'sqs';

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'listeners';

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 10;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Ut_order $order)
    {
        sleep(10);
        $this->order = $order;
    }
}
