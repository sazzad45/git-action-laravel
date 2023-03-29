<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use App;
use Config;

class SetLocale
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
        $locale = 'en';

        if (Session::has('locale')) {
            $locale = Session::get('locale', Config::get('app.locale'));
        } else if($request->has('lang') && $request->lang != ""){
            if($request->lang == "en" || $request->lang == "ar" || $request->lang == "ku"){
             $locale = $request->lang;
            }
        } else {
            $locale = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
            if ($locale != 'ar' && $locale != 'ku') {
                $locale = 'en';
            }
        }
        App::setLocale($locale);
        return $next($request);
    }
}
