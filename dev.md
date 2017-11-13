# Rest API Development With Lumen (5.5)

## 1、安装
* 安装 Lumen ：https://lumen.laravel.com/docs/5.5 （看官方文档操作就行）
* 绑定 hosts `192.168.10.10 restapi.app`

## 2、Application Key and .env file
为应用随机生成 32 位字符串，在 `routes/web.php` 文件添加以下方法。

``` 
// routes/web.php
$router->get('appKey', function () {
    return str_random('32');
});
```

访问 http://restapi.app/appKey 即可生成随机 32 位字符串，把生成的字符串添加到 `.env` 文件中 。

`APP_KEY=ZMyYPgVQYPBkKPoCak9B2GYFlqI0GtjA`

## 3、创建第一个资源
创建第一个用户资源：

```
// routes/web.php
$router->get('users', 'UserController@index');
$router->post('users', 'UserController@store');
$router->get('users/{id}', 'UserController@show');
$router->put('users/{id}', 'UserController@update');
$router->delete('users/{id}', 'UserController@destroy');
```

创建对应 UserController 

```
<?php //app/Http/Controllers/UserController.php
 
 
namespace App\Http\Controllers;
 
 
use Illuminate\Http\Request;
 
 
class UserController extends Controller
{
    public function index(Request $request)
    {
    }
 
    public function store(Request $request)
    {
    }
 
    public function update(Request $request, $id)
    {
    }
 
    public function show($id)
    {
    }
 
    public function destroy($id)
    {
    }
}
```

## 4、创建数据库和 Migration
创建数据库 restapi ，同时配置 .env 文件

`DB_DATABASE=restapi`

使用 make:migration 命令创建 migration 文件

`php artisan make:migration create_users_table --create=users`

修改 up 方法

```
	public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uid', 36)->unique();
            $table->string('firstName', '100')->nullable();
            $table->string('lastName', '100')->nullable();
            $table->string('middleName', '50')->nullable();
            $table->string('username', '50')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('address')->nullable();
            $table->string('zipCode')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('city', '100')->nullable();
            $table->string('state', '100')->nullable();
            $table->string('country', '100')->nullable();
            $table->string('type')->nullable();
            $table->tinyInteger('isActive');
            $table->timestamps();
            $table->softDeletes();
        });
    }

```

> 这里增加了一个 uid 的字段，是使用的替代数据自增 ID 的用途。具体参考 https://philsturgeon.uk/http/2015/09/03/auto-incrementing-to-destruction/ ，采用第三方包 https://github.com/ramsey/uuid；

运行 `php artisan migrate`

## 5、创建 Model
默认没有 Models 文件夹，我们需要自己创建一个





