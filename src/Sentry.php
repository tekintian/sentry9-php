<?php

namespace Sentry9;

/**
 * Sentry日志管理系统 入口
 *
 * 使用方法: 在 php项目的入口文件尾部增加   \Sentry9\Sentry::listen($client_url);
 *
 * @Author: tekintian
 * @Date:   2024年12月11日09:09:14
 * @Last Modified 2024年12月11日09:09:22
 */

error_reporting(E_ALL ^ E_NOTICE);

/**
 * Sentry日志管理系统入口类
 */
class Sentry {

	/** @var Client */
	private static $client;

	/**
	 * 实例化sentry 日志监听 同时返回 Client客户端实例
	 * @param  [type] $client_url [Sentry 项目配置中的DSN/客户端URL]
	 * @return Client             [Sentry客户端实例]
	 */
	static public function listen($client_url) : Client {
		if(is_null(self::$client)){
			// 实例化sentry raven client
			self::$client = new Client($client_url);

			// 异常处理对象实例化
			$handler = new ErrorHandler(self::$client);
			// 注册异常处理
			$handler->registerExceptionHandler();
			$handler->registerErrorHandler();
			$handler->registerShutdownFunction();
		}
		
		return self::$client;
	}
}
