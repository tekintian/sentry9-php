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

~~~


## thinkphp8 sentry9配置步骤
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
		//'client_url' => 'http://xxxxxxxxxxxxxxxxxxxxxxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxx@sentry.yunnan.ws/1',
		// sentry9
		'client_url' => 'http://xxxxxxxxxxxxxxxxxxxxxxxxxx@sentry.yunnan.ws/1',
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

