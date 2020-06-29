<?php


namespace Plugins\HttpClient;


class HttpEvent
{
    /**
     * 请求前事件
     */
    const EVENT_REQUEST_BEFORE = 'http.request.before';

    /**
     * 请求后事件
     */
    const EVENT_REQUEST_AFTER = 'http.request.after';
}
