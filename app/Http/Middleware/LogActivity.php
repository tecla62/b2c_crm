<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogActivity
{
    protected array $skipRoutes = ['login', 'logout', 'password.*'];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (Auth::check() && $request->isMethod('post') || $request->isMethod('put') || $request->isMethod('delete')) {
            $routeName = $request->route()?->getName() ?? '';
            $skip = collect($this->skipRoutes)->contains(fn($r) => fnmatch($r, $routeName));

            if (!$skip) {
                ActivityLog::log(
                    strtoupper($request->method()),
                    $this->getModule($request),
                    "Accessed: {$request->path()}",
                    null,
                    ['url' => $request->fullUrl(), 'method' => $request->method()]
                );
            }
        }

        return $response;
    }

    private function getModule(Request $request): string
    {
        $segments = $request->segments();
        return ucfirst($segments[0] ?? 'General');
    }
}
