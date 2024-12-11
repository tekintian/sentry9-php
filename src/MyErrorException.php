<?php

namespace Sentry9;

class MyErrorException extends \ErrorException
{
    // 自定义属性 解决PHP8 不支持动态增加属性的问题
    public $event_id;
	
}
