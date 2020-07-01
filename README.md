# swoft-jaeger-plugins

使用步骤

#### 安装依赖扩展

- composer require opentracing/opentracing:1.0.0-beta5 -vvv
- composer require jukylin/jaeger-php:v2.1.3 -vvv
- composer require hyperf/guzzle:^2.0

------


#### 初始化GIT子模块

- git submodule add https://github.com/wzzjjboy/swoft-jaeger-plugins.git plugins
- git submodule init
- git submodule update 
- 或者git submodule update --init --recursive
- 子模块使数码更新需要进程目录 git pull

------

#### 初始化git  autload
- 修改composer.json添加代码

  ```php
  "autoload": {      
      "psr-4": {
          "Plugins\\": "plugins/"
      }
  }
  ```

- composer dump-autoload

------

#### 配置中间件

修改app/bean.php添加中间件

```php
'httpDispatcher'    => [
        'middlewares'      => [
			//...
            \Plugins\Middleware\JaegerMiddleware::class,
			//...
        ],
        //...
    ],
```



#### 配置jaeger

```php
//配置jager
JAEGER_HTTP_PORT=80
JAEGER_RATE=1 #采样概率 1:100% 0.1：10%
JAEGER_MODE=1 #模式
JAEGER_SERVER_HOST=127.0.0.1:6831 #jaeger服务端地址
JAEGER_PNAME=jaeger-demo #应用名称
JAEGER_OPEN=true #是否开启jaeger
```
------

#### 使用方法

- 默认已经跟踪了redis,mysql的操作

- 如果要跟踪http，则需要调用替换http客户端
