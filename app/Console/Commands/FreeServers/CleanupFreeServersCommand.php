<?php

namespace Pterodactyl\Console\Commands\FreeServers;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Pterodactyl\Models\FreeServer;
use Pterodactyl\Models\Server;
use Pterodactyl\Services\Servers\ServerDeletionService;
use Pterodactyl\Services\Servers\SuspensionService;

class CleanupFreeServersCommand extends Command
{
    protected $signature = 'free-servers:cleanup';
    protected $description = 'Suspend or delete expired free servers';

    public function __construct(
        private SuspensionService $suspensionService,
        private ServerDeletionService $deletionService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $now = Carbon::now();

        FreeServer::with('server')->chunkById(100, function ($records) use ($now) {
            foreach ($records as $record) {
                if (!$record->server) {
                    continue;
                }

                if ($now->greaterThan($record->expires_at->copy()->addDay())) {
                    $this->deletionService->withForce()->handle($record->server);
                    $record->delete();
                    continue;
                }

                if ($now->greaterThan($record->expires_at) && $record->server->status !== Server::STATUS_SUSPENDED) {
                    $this->suspensionService->toggle($record->server, SuspensionService::ACTION_SUSPEND);
                }
            }
        });

        return self::SUCCESS;
    }
}
