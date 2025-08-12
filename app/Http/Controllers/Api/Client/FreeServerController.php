<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Pterodactyl\Models\FreeServer;

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
                'expires_at' => $server->expires_at,
            ];
        })->respond();
    }

    public function claim(Request $request)
    {
        if (FreeServer::where('user_id', $request->user()->id)->exists()) {
            return $this->respondWithError('Free server already claimed.');
        }

        // @todo Actually provision a server for the user.
        $expires = Carbon::now()->addSeconds(config('free-servers.default_duration'));

        $free = FreeServer::create([
            'user_id' => $request->user()->id,
            'expires_at' => $expires,
        ]);

        return $this->fractal->item($free)->transformWith(function (FreeServer $server) {
            return [
                'id' => $server->id,
                'expires_at' => $server->expires_at,
            ];
        })->respond(201);
    }

    public function extend(Request $request)
    {
        $free = FreeServer::where('user_id', $request->user()->id)->firstOrFail();

        $window = config('free-servers.extend_window');
        if ($free->expires_at->copy()->subSeconds($window)->isFuture()) {
            return $this->respondWithError('Extension window has not yet opened.');
        }

        $free->expires_at = $free->expires_at->copy()->addSeconds(config('free-servers.extend_duration'));
        $free->save();

        return $this->fractal->item($free)->transformWith(function (FreeServer $server) {
            return [
                'id' => $server->id,
                'expires_at' => $server->expires_at,
            ];
        })->respond();
    }
}
