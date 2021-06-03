<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;

class SyncMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->validApiKey($request)) {
            abort(401);
        }

        return $next($request);
    }

    protected function getApiKey(Request $request)
    {
        $name = env('API_PARAM', 'api_key');
        return $request->header('X-' . $name) ?? $request->get($name);
    }

    private function validApiKey(Request $request): bool
    {
        if (null == ($key = $this->getApiKey($request))) {
            return false;
        }

        try {
            $apiKey = ApiKey::find($key);
        } catch (\Exception $exception) {
            return false;
        }

        if (null == $apiKey || null == $apiKey->user) {
            return false;
        }

        auth()->login($apiKey->user);

        return true;
    }
}
