# Rest API Development With Lumen (5.5)

## 1、安装
* 安装：https://lumen.laravel.com/docs/5.5 （看官方文档操作就行）
* 绑定 hosts `192.168.10.10 restapi.app`

## 2、Application Key and .env file
为应用随机生成 32 位字符串。在 `routes/web.php` 文件添加以下方法。

``` 
// routes/web.php
$router->get('appKey', function () {
    return str_random('32');
});
```
访问 http://restapi.app/appKey 即可生成随机 32 位字符串，把生成的字符串添加到 `.env` 文件中 。

`APP_KEY=ZMyYPgVQYPBkKPoCak9B2GYFlqI0GtjA`

