# Sentry日志管理系统 Sentry8.x sentry9.x SDK for php8.x


Sentry 是一个开源的错误报告平台, 可以帮助开发者快速定位线上问题, 并提供详细的错误信息, 帮助开发者快速定位问题.

Sentry9.x 官方已经于2021年12月15日停止维护, sentry9.x的官方的SDK最高只支持 php7.4, 现在的PHP8直接不支持, 由于目前PHP8已经是主流,  故有了这个可以再Sentry9.x/8.x中使用的 sentry SDK.


**本SDK特点,支持php8.x , 简洁安全, 不依赖第三方类库!**


Sentry  PHP 可视化日志管理系统,支持 php5.3以上版本, 支持 php7.x php8.x, 可以集成到市面上大部分的PHP框架中,也可直接使用.

## 为何还要使用Sentry8/9.x? 
 因为这个版本的内存占用非常小, 低配置的机器也可以部署sentry8.x,9.x使用, docker方式部署后总计占用内存情况
sentry8.x  3个服务总计占用内存不到500M,
sentry9.x  3个服务总计占用内存不到700M


## PHP集成 sentry 通用方法:

1. 项目跟目录执行命令  composer require tekintian/sentry9-php

~~~php
#  采用autoload.php自动载入方式加载 Sentry 
require_once  __DIR__ . 'vendor/autoload.php';

~~~

2. 在你的入口文件中增加

~~~php
//$client_url 为你的sentry服务端分配的客户端URL
require __DIR__ . '/vendor/autoload.php';
// 注意这里的位置需要再异常代码之前加载
$sentry = \Sentry9\Sentry::listen('https://abc4e0db14bd4d599290b783ce9ddebe@sentry.tekin.cn/2');

try {
    // 这里是你的业务代码
    // 比如:
    $a = 1 / 0;
} catch (Exception $e) {
    // 记录异常信息
    $sentry->captureException($e);
}

// 手动记录异常信息
$sentry->captureMessage("Hello message from Sentry client!");
~~~


## thinkphp5/ tp6 sentry9配置步骤
tp8默认使用的就是composer来管理第三方包, 所以直接使用 composer 来安装 sentry9 即可.

1. 安装 sentry9 包
~~~shell
# 安装 sentry9 包
composer require "tekintian/sentry9-php"

~~~

2. 修改tp8配置文件,增加SENTRY配置项目

在配置文件 config.php 中增加 sentry9 配置项目

~~~php
return [
	// 其他配置信息 .......
	//SENTRY Raven Log日志可视化日志管理系统
	'sentry9' => [
		// 是否启用 TRUE 启用; FALSE  禁用
		'is_enable' => TRUE,
		// Sentry 项目配置中的客户端URL 
		// sentry8
		//'client_url' => 'http://xxxxxxxxxxxxxxxxxxxxxxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxx@sentry.tekin.cn/1',
		// sentry9
		'client_url' => 'http://xxxxxxxxxxxxxxxxxxxxxxxxxx@sentry.tekin.cn/1',
	],
]
~~~


3. 在 index.php 入口文件的最后增加一下代码

~~~php

if (config('sentry9.is_enable')) {
	// 使用tp助手函数从 thinkphp5的配置文件中获取 sentry raven url地址
	$client_url = config('sentry9.client_url');

	// sentry日志监听开启, 如果不需要配置开关,可以直接将下面这段放到你想要的地方即可.
	$sentry = \Sentry9\Sentry::listen($client_url);
	// 这样你就可以在你的项目中使用了.  也可以直接使用这里的全局变量 $sentry->handleException($exception);   来记录日志. 详情见demo.php  文件.
}

~~~

## thinkphp8 tp8 sentry9配置步骤
tp8的默认使用的就是composer来管理第三方包, 所以直接使用 composer 来安装 sentry9 即可.
同时tp8和tp5的配置方式不太一样, 这里我们直接使用自定义异常类来处理Sentry的异常.

1. 安装 sentry9 包
~~~shell
# 安装 sentry9 包
composer require "tekintian/sentry9-php"
~~~
2. 创建自定义异常类
位置 app\common\exception 目录下创建 Sentry.php 文件
~~~php
<?php
namespace app\common\exception;

use think\exception\Handle;
use think\exception\HttpException;
use think\exception\ValidateException;
use think\Response;
use Throwable;
use Sentry9\Client;

class Sentry extends Handle
{
    /**
     * @var Client
     */
    private $sentry;
    /**
     * 获取Sentry异常处理实例
     *
     * @return Client
     */
    public function getSentry(): Client {
        if (is_null($this->sentry)) {
            // 初始化Sentry
            $dsn = config('app.sentry_dsn');
            $this->sentry = \Sentry9\Sentry::listen($dsn);
        }
        return $this->sentry;
    }
    /**
     * 记录异常信息（包括日志或者其它方式记录）
     *
     * @access public
     * @param Throwable $exception
     * @return void
     */
    public function render($request, Throwable $e): Response
    {
        // 获取Sentry异常处理实例
        $sentry = $this->getSentry();
         // 记录异常信息  放在这里就是记录所有的异常信息, 如果放在 if里面的话就可以只记录指定类型的异常信息
        $sentry->captureException($e);

        // 参数验证错误
        if ($e instanceof ValidateException) {
             // 记录错误日志
            return json($e->getError(), 422);
        }

        // 请求异常
        if ($e instanceof HttpException && $request->isAjax()) {
           

            return response($e->getMessage(), $e->getStatusCode());
        }

        // 其他错误交给系统处理
        return parent::render($request, $e);
    }

}
~~~

3. 在app/provider.php中加载自定义异常类
就是将 'think\exception\Handle'  => '\\app\\common\\exception\\Sentry', 添加到Provider中 
这里就会自动覆盖tp8的异常处理类.
~~~php
<?php
use app\ExceptionHandle;
use app\Request;

// 容器Provider定义文件
return [
    'think\Request'          => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
     // 绑定自定义异常处理handle类 app\common\exception
    'think\exception\Handle'  => '\\app\\common\\exception\\Sentry',
];
~~~


4. 修改tp8配置文件 config/app.php,增加SENTRY配置项目
在config/app.php文件的最后增加sentry9配置项目
~~~php
	// sentry DSN配置
    'sentry_dsn'   => env('SENTRY_DSN', 'https://xxxxxxx@sentry.tekin.cn/2'),
~~~

5. 在项目根目录的.env文件中增加SENTRY_DSN 配置
~~~
SENTRY_DSN = https://xxxxxxx@sentry.tekin.cn/2
~~~

到此我们就完成了thinkphp8中的sentry9的配置, 是不是很简单.


## sentry9服务端docker部署

一步一步的运行即可成功部署sentry9
$PWD 为你当前的目录

如果要部署sentry8, 将镜像 sentry:9  替换为  sentry:8 即可


~~~sh
# 部署redis, postgress 如果要指定网络,可以使用  --net=xxx
#Start a Redis container
$ docker run -d --name --ip=172.18.0.17 redis7 redis
#Start a Postgres container
$ docker run -d --name postgresql12 --ip=172.18.0.18 -e POSTGRES_PASSWORD=AbzKGihGWsdfdJkTF789 -e POSTGRES_USER=sentry9 postgres

# 生成secret Generate a new secret key to be shared by all sentry containers. This value will then be used as the SENTRY_SECRET_KEY environment variable.
$ docker run --rm sentry:9 config generate-secret-key
i1prn4iu%0%my)%8pxhilk4jmlo4lq)%)%0&yg%e2btmq)36c-


#If this is a new database, you'll need to run upgrade
# 创建初始化数据库  
$ docker run -it --rm  \
	-e SENTRY_SECRET_KEY='i1prn4iu%0%my)%8pxhilk4jmlo4lq)%)%0&yg%e2btmq)36c-' \
	-e SENTRY_REDIS_HOST='172.18.0.17' -e SENTRY_REDIS_PORT='6379' -e SENTRY_REDIS_DB='9' \
	-e SENTRY_REDIS_PASSWORD='Z3U9B5345kydB4DmUdhabcIjklaa89' \
	-e SENTRY_POSTGRES_HOST='172.18.0.18' -e SENTRY_POSTGRES_PORT='5432' \
	-e SENTRY_DB_NAME='sentry9' -e SENTRY_DB_USER='sentry9' -e SENTRY_DB_PASSWORD='AbzKGihGWsdfdJkTF789' \
	sentry:9 upgrade

#Note: the -it is important as the initial upgrade will prompt to create an initial user and will fail without it
#Now start up Sentry server
# 拷贝容器中的/etc/sentry到运行目录的etc目录
docker run -it --rm  -v $PWD/sentry9:/diy sentry:9 sh -c 'mv /etc/sentry /diy/etc'


$ docker run -itd --name sentry9  \
	-e SENTRY_SECRET_KEY='i1prn4iu%0%my)%8pxhilk4jmlo4lq)%)%0&yg%e2btmq)36c-' \
	-e SENTRY_REDIS_HOST='172.18.0.17' -e SENTRY_REDIS_PORT='6379' -e SENTRY_REDIS_DB='9' \
	-e SENTRY_REDIS_PASSWORD='Z3U9B5345kydB4DmUdhabcIjklaa89' \
	-e SENTRY_POSTGRES_HOST='172.18.0.18' -e SENTRY_POSTGRES_PORT='5432' \
	-e SENTRY_DB_NAME='sentry9' -e SENTRY_DB_USER='sentry9' -e SENTRY_DB_PASSWORD='AbzKGihGWsdfdJkTF789' \
	-v $PWD/sentry9/diy:/diy \
	-v $PWD/sentry9/etc:/etc/sentry \
	-v $PWD/sentry9/files:/var/lib/sentry/files \
	sentry:9

#The default config needs a celery beat and celery workers, start as many workers as you need (each with a unique name)

$ docker run -itd --name sentry9-cron  \
	-e SENTRY_SECRET_KEY='i1prn4iu%0%my)%8pxhilk4jmlo4lq)%)%0&yg%e2btmq)36c-' \
	-e SENTRY_REDIS_HOST='172.18.0.17' -e SENTRY_REDIS_PORT='6379' -e SENTRY_REDIS_DB='9' \
	-e SENTRY_REDIS_PASSWORD='Z3U9B5345kydB4DmUdhabcIjklaa89' \
	-e SENTRY_POSTGRES_HOST='172.18.0.18' -e SENTRY_POSTGRES_PORT='5432' \
	-e SENTRY_DB_NAME='sentry9' -e SENTRY_DB_USER='sentry9' -e SENTRY_DB_PASSWORD='AbzKGihGWsdfdJkTF789' \
	-v $PWD/sentry9/diy:/diy \
	-v $PWD/sentry9/etc:/etc/sentry \
	-v $PWD/sentry9/files:/var/lib/sentry/files \
	sentry:9 run cron

$ docker run -itd --name sentry9-worker-1  \
	-e SENTRY_SECRET_KEY='i1prn4iu%0%my)%8pxhilk4jmlo4lq)%)%0&yg%e2btmq)36c-' \
	-e SENTRY_REDIS_HOST='172.18.0.17' -e SENTRY_REDIS_PORT='6379' -e SENTRY_REDIS_DB='9' \
	-e SENTRY_REDIS_PASSWORD='Z3U9B5345kydB4DmUdhabcIjklaa89' \
	-e SENTRY_POSTGRES_HOST='172.18.0.18' -e SENTRY_POSTGRES_PORT='5432' \
	-e SENTRY_DB_NAME='sentry9' -e SENTRY_DB_USER='sentry9' -e SENTRY_DB_PASSWORD='AbzKGihGWsdfdJkTF789' \
	-v $PWD/sentry9/diy:/diy \
	-v $PWD/sentry9/etc:/etc/sentry \
	-v $PWD/sentry9/files:/var/lib/sentry/files \
	sentry:9 run worker


~~~





## 联系我

TekinTian@gmail.com

