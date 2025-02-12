<?php

namespace App\Http\Middleware;

use Closure;

class WxMsgTitle
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
        $app = app('wechat.mini_program');

        if (!request('title')) {
            throw new \Exception('请输入内容！');
        }
        $res = $app->content_security->checkText(request('title'));
        if ($res['errcode'] == 0) {
            return $next($request);
        }else {
            throw new \Exception('请输入健康积极向上的内容！');
        }
//        return $next($request);
    }
}
