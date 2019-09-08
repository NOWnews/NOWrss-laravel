<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\InvalidRequestLog;

class IpMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ignore check ip if APP_ENV dev
        if (env('APP_ENV') === 'dev') {
            return $next($request);
        }

        $allowIps = collect();

        try {
            $allowIpsJson = Storage::disk('local')->get('data/allowIps.json');
            $allowIps = collect(json_decode($allowIpsJson));
        } catch (\Exception $e) {
            // do nothing
        }

        $xForwardedFor = $request->header('x-forwarded-for');

        if (empty($xForwardedFor)) {
            $ip = $request->ip();
        } else {
            $ips = is_array($xForwardedFor) ? $xForwardedFor : explode(', ', $xForwardedFor);
            $ip = $ips[0];
        }

        $isAllowed = $allowIps->search(function ($item) use ($ip) {
            return in_array($ip, $item->ips);
        });

        // redirect to nownews if request ip not allowed
        if ($isAllowed === false) {
            InvalidRequestLog::create(
                [
                    'ip' => $ip,
                    'created_at' => Carbon::now('Asia/Taipei'),
                ]
            );

            return redirect('https://www.nownews.com');
        }

        return $next($request);
    }
}
