<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\NotificationController;

class CheckHariHNotification extends Command
{
    protected $signature = 'notification:check-hari-h';
    protected $description = 'Check and send Hari H notifications for penitipan';

    public function handle()
    {
        $controller = new NotificationController();
        $result = $controller->sendHariHNotification();
        
        $this->info('Hari H notification check completed');
        return 0;
    }
} 