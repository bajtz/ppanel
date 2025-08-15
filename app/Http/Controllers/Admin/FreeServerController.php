<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Pterodactyl\Models\FreeServer;
use Pterodactyl\Services\Servers\ServerDeletionService;

class FreeServerController extends BaseController
{
    public function index()
    {
        $freeServers = FreeServer::with(['user'])->get();

        return view('admin.free.index', [
            'freeServers' => $freeServers,
        ]);
    }

    public function delete(FreeServer $freeServer, ServerDeletionService $deletionService): RedirectResponse
    {
        if ($freeServer->server) {
            $deletionService->withForce()->handle($freeServer->server);
        }

        $freeServer->delete();

        return redirect()->route('admin.free-servers.index')->with('success', 'Free server removed');
    }
}
