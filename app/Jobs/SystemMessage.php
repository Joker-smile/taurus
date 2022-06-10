<?php

namespace App\Jobs;

use App\Repositories\SystemMessageRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SystemMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $system_message;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\SystemMessage $system_message
     */
    public function __construct(\App\Models\SystemMessage $system_message)
    {
        $this->system_message = $system_message;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $system_repository = app(SystemMessageRepository::class);
        $system_message = $this->system_message;
        $system_repository->push($system_message->refresh());
    }
}
