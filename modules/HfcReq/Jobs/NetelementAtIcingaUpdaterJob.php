<?php

namespace Modules\HfcReq\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NetelementAtIcingaUpdaterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $netelementId = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($netelementId)
    {
        $this->netelementId = $netelementId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Artisan::call('nms:icinga-netelement', ['netelement' => $this->netelementId]);
    }
}
