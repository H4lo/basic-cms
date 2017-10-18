<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;
use App\ActionLog;


class ActionLogs
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (!$request->isMethod('get') || !empty($request->all())) {
            $actionLog = new ActionLog();
            $actionLog->user_id = ($request->user()) ? $request->user()->id : null;
            $actionLog->remote_address = $request->ip();
            $actionLog->route = Route::getCurrentRoute()->getActionName();
            $actionLog->method = $request->method();
            $actionLog->request = json_encode($request->except('_token', 'password', 'password_confirmation'));
            $actionLog->save();
        }

        return $response;
    }
}