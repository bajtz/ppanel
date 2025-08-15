<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Pterodactyl\Models\FreeServer;
use Pterodactyl\Models\Server;
use Pterodactyl\Services\Servers\ServerCreationService;
use Pterodactyl\Services\Servers\SuspensionService;

class FreeServerController extends ClientApiController
{
    public function index(Request $request)
    {
        $free = FreeServer::where('user_id', $request->user()->id)->first();

        return $this->fractal->item($free)->transformWith(function (?FreeServer $server) {
            if (!$server) {
                return null;
            }

            return [
                'id' => $server->id,
                'server_id' => $server->server_id,
                'expires_at' => $server->expires_at,
                'remaining' => Carbon::now()->diffInSeconds($server->expires_at, false),
                'can_extend' => Carbon::now()->greaterThanOrEqualTo($server->expires_at->copy()->subSeconds(config('free-servers.extend_window'))),
            ];
        })->respond();
    }

    public function claim(Request $request, ServerCreationService $creationService)
    {
        if (FreeServer::where('user_id', $request->user()->id)->exists()) {
            return $this->respondWithError('Free server already claimed.');
        }

        $expires = Carbon::now()->addSeconds(config('free-servers.default_duration'));

        $data = config('free-servers.provision');
        $data['owner_id'] = $request->user()->id;
        $data['name'] = $request->user()->username . '-free';

        /** @var Server $server */
        $server = $creationService->handle($data);

        $free = FreeServer::create([
            'user_id' => $request->user()->id,
            'server_id' => $server->id,
            'expires_at' => $expires,
        ]);

        return $this->fractal->item($free)->transformWith(function (FreeServer $server) {
            return [
                'id' => $server->id,
                'server_id' => $server->server_id,
                'expires_at' => $server->expires_at,
                'remaining' => Carbon::now()->diffInSeconds($server->expires_at, false),
                'can_extend' => Carbon::now()->greaterThanOrEqualTo($server->expires_at->copy()->subSeconds(config('free-servers.extend_window'))),
            ];
        })->respond(201);
    }

    public function extend(Request $request, SuspensionService $suspensionService)
    {
        $free = FreeServer::where('user_id', $request->user()->id)->firstOrFail();

        $window = config('free-servers.extend_window');
        if ($free->expires_at->copy()->subSeconds($window)->isFuture()) {
            return $this->respondWithError('Extension window has not yet opened.');
        }

        $free->expires_at = $free->expires_at->copy()->addSeconds(config('free-servers.extend_duration'));
        $free->save();

        if ($free->server && $free->server->status === Server::STATUS_SUSPENDED) {
            $suspensionService->toggle($free->server, SuspensionService::ACTION_UNSUSPEND);
        }

        return $this->fractal->item($free)->transformWith(function (FreeServer $server) {
            return [
                'id' => $server->id,
                'server_id' => $server->server_id,
                'expires_at' => $server->expires_at,
                'remaining' => Carbon::now()->diffInSeconds($server->expires_at, false),
                'can_extend' => Carbon::now()->greaterThanOrEqualTo($server->expires_at->copy()->subSeconds(config('free-servers.extend_window'))),
            ];
        })->respond();
    }
}
