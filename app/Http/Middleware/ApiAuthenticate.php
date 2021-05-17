<?php

namespace App\Http\Middleware;

use App\Helper\TempApiKey;
use App\Models\ApiKey;
use App\Models\Project;
use Closure;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class ApiAuthenticate
{

    private $uuid = null;
    private $env = null;
    private $headerOnly = true;
    private $project = null;

    public static $apiKey;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $this->env = env('APP_ENV', 'prod');
        if(!($this->checkApiKey($request))) {
            return response()->json('Authenticate error', 400);
        }

        if(!$this->tempApiKey()) {
            if (!$this->checkProjects($request)) {
                return response()->json('Authenticate error. Project not authorized', 400);
            }
        }

        return $next($request, $this->project);
    }

    private function tempApiKey() {
        return TempApiKey::check($this->uuid);
    }

    /**
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    private function checkApiKey($request)
    {
       $api_param = env('API_PARAM', 'api_key');
       $apiKey = $request->header($api_param, null);
       if($apiKey === null && $this->env !== 'prod' || !$this->headerOnly) {
           $apiKey = $request->get($api_param, null);
       }

       if(null == $apiKey) {
           return false;
       }

       try {
           $this->uuid = Uuid::fromString($apiKey)->toString();
       } catch (\Exception $exception) {
           return false;
       }

       return $this->uuid !== null;
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    private function checkProjects($request):bool
    {
        $project = $request->header('project', null);
        if($project === null && $this->env !== 'prod'|| !$this->headerOnly) {
            $project = $request->get('project', null);
        }

        $key = ApiKey::find($this->uuid);
        if(null == $key) {
            return false;
        }

        self::$apiKey = $key;

        if($key->general_use) {
            return true;
        }

        $key->load(['projects' => function ($projects) use($project) {
            $projects->where('project_id', $project);
        }]);

        return 0 != $key->projects->count();
    }
}
