<?php

namespace App\Http\Middleware;

use Closure;

class IpMiddleware
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
	$xForwardedFor = $request->header('x-forwarded-for');
        if (empty($xForwardedFor)) {
            $ip = $request->ip();
        } else {
            $ips = is_array($xForwardedFor) ? $xForwardedFor : explode(', ', $xForwardedFor);
            $ip = $ips[0];
        }

	if ($this->isDeveloperIP($ip) || $ip == '61.216.80.97' || $ip == '61.216.80.98' || $ip == '61.216.80.99'
	 || $ip == '61.216.80.100' || $ip == '61.216.80.101' || $ip == '61.216.80.102' ||  $ip == '180.217.183.74') {
        // here insted checking single ip address we can do collection of ip 
        //address in constant file and check with in_array function
	    return $next($request);
        }
	else {
//return $next($request);
	    return redirect('https://www.nownews.com');
	}
    }
	
    public function isDeveloperIP($ip) {
	return ($this->is4everIp($ip)) || ($this->isRynoldIp($ip)) || ($this->isDavidIp($ip)) || ($this->isDivIp($ip));
    }

    public function is4everIp($ip) {
	return ($ip == '114.32.148.113');
    }

    public function isRynoldIp($ip) {
	return ($ip == '114.26.3.32');
    }

    public function isDavidIp($ip) {
	return ($ip == '114.45.15.235');
    }

    public function isDivIp($ip) {
        return ($ip == '223.137.37.40');
    }
}
