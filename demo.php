<?php

/**
 * @Author: tekin
 * @Date:   2024-12-10 19:19:45
 * @Last Modified by:   tekin
 * @Last Modified time: 2024-12-10 22:13:33
 */
require __DIR__ . '/vendor/autoload.php';
// 注意这里的位置需要再异常代码之前加载  这里返回的对象就是Sentry的客户端对象 详见 src/Client.php
$sentry = \Sentry9\Sentry::listen('https://abc4e0db14bd4d599290b783ce9ddebe@sentry.tekin.cn/2');


try {
    // 异常代码
    // thisFunctionThrows();
    throw new \Exception('foo baraaaaaa');

} catch (\Exception $exception) {
    // try catch后手动处理异常  这个地方如果不手动处理, 异常会被php默认处理, 导致sentry无法捕获异常
    $sentry->captureException($exception);
}

// Division by zero 0除法异常 这个异常会在运行时被抛出, 然后会自动被Sentry捕获
$v1 = 1/0;




