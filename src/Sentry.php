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

	/**
	 * sentry 日志监听
	 * @param  [type] $client_url [Sentry 项目配置中的DSN/客户端URL]
	 * @return ErrorHandler             [description]
	 */
	static public function listen($client_url) : ErrorHandler {
		// 实例化sentry raven client
		$client = new Client($client_url);
		$sentry = new ErrorHandler($client);
		$sentry->registerExceptionHandler();
		$sentry->registerErrorHandler();
		$sentry->registerShutdownFunction();
		return $sentry;
	}
}
