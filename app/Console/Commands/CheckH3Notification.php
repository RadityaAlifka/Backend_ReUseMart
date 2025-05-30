<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\NotificationController;

class CheckH3Notification extends Command
{
    protected $signature = 'notification:check-h3';
    protected $description = 'Check and send H-3 notifications for penitipan';

    public function handle()
    {
        $controller = new NotificationController();
        $result = $controller->sendH3Notification();
        
        $this->info('H-3 notification check completed');
        return 0;
    }
} 