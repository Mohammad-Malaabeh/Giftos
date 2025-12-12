<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class AcceptCookiesJob implements ShouldQueue
{
    use Queueable;

    protected ?int $userId;
    protected string $ipAddress;
    protected string $userAgent;

    /**
     * Create a new job instance.
     */
    public function __construct(?int $userId, string $ipAddress, string $userAgent)
    {
        $this->userId = $userId;
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Example: store consent in database table
        DB::table('cookie_consents')->insert([
            'user_id' => $this->userId,
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'created_at' => now(),
        ]);
    }
}
