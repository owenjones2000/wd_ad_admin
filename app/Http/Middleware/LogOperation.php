<?php

namespace App\Http\Middleware;

use App\Helper\Helper as HelperHelper;
use App\Laravue\Models\OperationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LogOperation
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        if ($this->shouldLogOperation($request)) {
            $user = Auth::user();
            
            $log = [
                'user_id' => $user ? $user->id : 0,
                'name' => $user ? $user->name : '',
                'path'    => substr($request->path(), 0, 255),
                'method'  => $request->method(),
                'ip'      => isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $request->getClientIp(),
                'input'   => $this->formatInput($request->input()),
            ];
            Log::info($log);
            try {
                $res = OperationLog::create($log);
                Log::info($res);
            } catch (\Exception $exception) {
                Log::info($exception);
            }
        }

        return $next($request);
    }

    /**
     * @param array $input
     *
     * @return string
     */
    protected function formatInput(array $input)
    {
        foreach ((array) config('operationlog.secret_fields') as $field) {
            if ($field && ! empty($input[$field])) {
                $input[$field] = Str::limit($input[$field], 3, '******');
            }
        }

        return json_encode($input);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected function shouldLogOperation(Request $request)
    {
        return config('operationlog.enable')
            && ! $this->inExceptArray($request)
            && $this->inAllowedMethods($request->method());
    }

    /**
     * Whether requests using this method are allowed to be logged.
     *
     * @param string $method
     *
     * @return bool
     */
    protected function inAllowedMethods($method)
    {
        $allowedMethods = collect(config('operationlog.allowed_methods'))->filter();

        if ($allowedMethods->isEmpty()) {
            return true;
        }

        return $allowedMethods->map(function ($method) {
            return strtoupper($method);
        })->contains($method);
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function inExceptArray($request)
    {
        // if ($request->routeIs('dcat.api.value')) {
        //     return true;
        // }

        foreach (config('operationlog.except') as $except) {
            // $except = admin_base_path($except);

            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if (HelperHelper::matchRequestPath($except)) {
                return true;
            }
        }

        return false;
    }
}
