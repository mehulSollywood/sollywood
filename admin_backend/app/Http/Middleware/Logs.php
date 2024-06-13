<?php

namespace App\Http\Middleware;

use App\Utils\LogUtil;
use Closure;
use Illuminate\Http\Request;

class Logs
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

        LogUtil::$requestTime -= microtime(true);

        return $next($request);
    }

    public function terminate($request, $response)
    {
        //выключили отсчёт времени выполнения запроса
        LogUtil::$requestTime += microtime(true);

        // $responseData = json_decode($response->getContent(),true);
        $responseData = '';
        $status = $response->getStatusCode();
        $user = LogUtil::resolveUser();

        $resContext = isset($responseData['data']) ? (is_array($responseData['data']) ? $responseData['data'] : ['data' => $responseData['data']])
            : (is_array($responseData) ? $responseData : ['data' => $responseData]);

        LogUtil::setContext('request', $request->toArray());
        LogUtil::setContext('response', LogUtil::getConfig('info_level_log') == 'debug' ?
            $resContext : ['data' => '#DATA_HIDDEN#']);

        LogUtil::Log([
            $status == 200 ? 'SUCCESS' : ($status != 500 ? 'FAIL' : 'EXCEPTION'),
            $status,
            $request->ip(),
            $request->getMethod(),
            $request->getPathInfo(),
            $responseData['message'] ?? ($status == 401 ? 'User unauthorized' : (
            $status == 200 ? ($request->grant_type === 'password' ?
                'Successfully authorization' : 'Successfully update authorization') : 'Unsuccessfully authorization')),
            LogUtil::prepareCodes($responseData),
            round(LogUtil::$requestTime, 4),
            $user->id ?? 'null',
            'null',
            'null',
            $request->userAgent()
        ]);
    }
}
