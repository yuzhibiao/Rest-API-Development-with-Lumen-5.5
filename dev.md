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

> 这里增加了一个 uid 的字段，是使用的替代数据库自增 ID 的用途。具体参考 https://philsturgeon.uk/http/2015/09/03/auto-incrementing-to-destruction/ ，采用第三方包 https://github.com/ramsey/uuid；

运行 `php artisan migrate`

## 5、创建 Model
默认没有 Models 文件夹，我们需要自己创建一个，同时创建 User.php 文件

```
<?php //app/Models/User.php
 
namespace App\Models;
 
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Database\Eloquent\SoftDeletes;
 
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, SoftDeletes;
 
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';
 
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'firstName',
        'lastName',
        'middleName',
        'email',
        'password',
        'address',
        'zipCode',
        'username',
        'city',
        'state',
        'country',
        'phone',
        'mobile',
        'type',
        'isActive'
    ];
 
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];
}
```

## 6、创建 Repositories
创建 Repositories 文件夹，为每个 Repository, 我们要创建一个接口和实现的接口。首先,我们要创建一个 BaseRepository 接口。所有其他接口扩展 BaseRepository 接口。

```
<?php //app/Repositories/Contracts/BaseRepository.php
 
namespace App\Repositories\Contracts;
 
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
 
interface BaseRepository
{
    /**
     * find a resource by id
     *
     * @param $id
     * @return Model|null
     */
    public function findOne($id);
 
    /**
     * find a resource by criteria
     *
     * @param array $criteria
     * @return Model|null
     */
    public function findOneBy(array $criteria);
 
    /**
     * Search All resources
     *
     * @param array $searchCriteria
     * @return Collection
     */
    public function findBy(array $searchCriteria = []);
 
    /**
     * Search All resources by any values of a key
     *
     * @param string $key
     * @param array $values
     * @return Collection
     */
    public function findIn($key, array $values);
 
    /**
     * save a resource
     *
     * @param array $data
     * @return Model
     */
    public function save(array $data);
 
    /**
     * update a resource
     *
     * @param Model $model
     * @param array $data
     * @return Model
     */
    public function update(Model $model, array $data);
 
    /**
     * delete a resource
     *
     * @param Model $model
     * @return mixed
     */
    public function delete(Model $model);
 
    /**
     * updated records by specific key and values
     *
     * @param string $key
     * @param array $values
     * @param array $data
     * @return Collection
     */
    public function updateIn($key, array $values, array $data);
}

```

我们已经创建了一个 BaseRepository 接口, 所有必要的方法与数据库进行交互。创建一个抽象类名 AbstractEloquentRepository 实现 BaseRepository 接口。

```
<?php //app/Repositories/AbstractEloquentRepository.php
 
namespace App\Repositories;
 
use App\Repositories\Contracts\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
 
abstract class AbstractEloquentRepository implements BaseRepository
{
    /**
     * Name of the Model with absolute namespace
     *
     * @var string
     */
    protected $modelName;
 
    /**
     * Instance that extends Illuminate\Database\Eloquent\Model
     *
     * @var Model
     */
    protected $model;
 
 
    public function __construct()
    {
        $this->setModel();
    }
 
    /**
     * Instantiate Model
     *
     * @throws \Exception
     */
    public function setModel()
    {
        //check if the class exists
        if (class_exists($this->modelName)) {
            $this->model = new $this->modelName;
 
            //check object is a instanceof Illuminate\Database\Eloquent\Model
            if (!$this->model instanceof Model) {
                throw new \Exception("{$this->modelName} must be an instance of Illuminate\Database\Eloquent\Model");
            }
 
        } else {
            throw new \Exception('No model name defined');
        }
    }
 
    /**
     * Get Model instance
     *
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }
 
    /**
     * @inheritdoc
     */
    public function findOne($id)
    {
        return $this->findOneBy(['uid' => $id]);
    }
 
    /**
     * @inheritdoc
     */
    public function findOneBy(array $criteria)
    {
        return $this->model->where($criteria)->first();
    }
 
    /**
     * @inheritdoc
     */
    public function findBy(array $searchCriteria = [])
    {
        $limit = !empty($searchCriteria['per_page']) ? (int)$searchCriteria['per_page'] : 15; // it's needed for pagination
 
        $queryBuilder = $this->model->where(function ($query) use ($searchCriteria) {
 
            $this->applySearchCriteriaInQueryBuilder($query, $searchCriteria);
        }
        );
 
        return $queryBuilder->paginate($limit);
    }
 
 
    /**
     * Apply condition on query builder based on search criteria
     *
     * @param Object $queryBuilder
     * @param array $searchCriteria
     * @return mixed
     */
    protected function applySearchCriteriaInQueryBuilder($queryBuilder, array $searchCriteria = [])
    {
 
        foreach ($searchCriteria as $key => $value) {
 
            //skip pagination related query params
            if (in_array($key, ['page', 'per_page'])) {
                continue;
            }
 
            //we can pass multiple params for a filter with commas
            $allValues = explode(',', $value);
 
            if (count($allValues) > 1) {
                $queryBuilder->whereIn($key, $allValues);
            } else {
                $operator = '=';
                $queryBuilder->where($key, $operator, $value);
            }
        }
 
        return $queryBuilder;
    }
 
    /**
     * @inheritdoc
     */
    public function save(array $data)
    {
        // generate uid
        $data['uid'] = Uuid::uuid4();
 
        return $this->model->create($data);
    }
 
    /**
     * @inheritdoc
     */
    public function update(Model $model, array $data)
    {
        $fillAbleProperties = $this->model->getFillable();
 
        foreach ($data as $key => $value) {
 
            // update only fillAble properties
            if (in_array($key, $fillAbleProperties)) {
                $model->$key = $value;
            }
        }
 
        // update the model
        $model->save();
 
        // get updated model from database
        $model = $this->findOne($model->uid);
 
        return $model;
    }
 
    /**
     * @inheritdoc
     */
    public function findIn($key, array $values)
    {
        return $this->model->whereIn($key, $values)->get();
    }
 
    /**
     * @inheritdoc
     */
    public function delete(Model $model)
    {
        return $model->delete();
    }
}
```

创建 UserRepository 、 EloquentUserRepository 实现用户仓库

```
<?php //app/Repositories/Contracts/UserRepository.php
 
namespace App\Repositories\Contracts;
 
interface UserRepository extends BaseRepository
{
}
```

```
<?php //app/Repositories/EloquentUserRepository.php
 
namespace App\Repositories;
 
use App\Repositories\Contracts\UserRepository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
 
class EloquentUserRepository extends AbstractEloquentRepository implements UserRepository
{
    /**
     * Model name
     *
     * @var string
     */
    protected $modelName = User::class;
 
 
    /*
     * @inheritdoc
     */
    public function save(array $data)
    {
        // update password
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
 
        $user = parent::save($data);
 
        return $user;
    }
}
```

## 7、Service Provider and Service Container

创建 RepositoriesServiceProvider

```
<?php //app/Providers/RepositoriesServiceProvider.php
 
namespace App\Providers;
 
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepository;
use App\Repositories\EloquentUserRepository;
 
class RepositoriesServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;
 
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
    }
 
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            UserRepository::class
        ];
    }
}
```

### 注册服务供应商

在 bootstrap/app.php 文件中添加

`$app->register(App\Providers\RepositoriesServiceProvider::class);`

同时开启 facade 和 eloquent

```
$app->withFacades();
$app->withEloquent();
```

## 8、更新 UserController

```
<?php //app/Http/Controllers/UserController.php
 
namespace App\Http\Controllers;
 
use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Http\Request;
 
class UserController extends Controller
{
    /**
     * Instance of UserRepository
     *
     * @var UserRepository
     */
    private $userRepository;
 
    /**
     * Assign the validatorName that will be used for validation
     *
     * @var string
     */
    protected $validatorName = 'User';
 
    /**
     * Constructor
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }
 
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $users = $this->userRepository->findBy($request->all());
 
        return response()->json(['data' => $users], 200);
    }
 
    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function show($id)
    {
        $user = $this->userRepository->findOne($id);
 
        if (!$user instanceof User) {
            return response()->json(['message' => "The user with id {$id} doesn't exist"], 404);
        }
 
        return response()->json(['data' => $user], 200);
    }
 
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function store(Request $request)
    {
        $user = $this->userRepository->save($request->all());
 
        if (!$user instanceof User) {
            return response()->json(['message' => "Error occurred on creating user"], 500);
        }
 
        return response()->json(['data' => $user], 201);
    }
 
    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = $this->userRepository->findOne($id);
 
        if (!$user instanceof User) {
            return response()->json(['message' => "The user with id {$id} doesn't exist"], 404);
        }
 
        $inputs = $request->all();
 
        $user = $this->userRepository->update($user, $inputs);
 
        return response()->json(['data' => $user], 200);
    }
 
    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function destroy($id)
    {
        $user = $this->userRepository->findOne($id);
 
        if (!$user instanceof User) {
            return response()->json(['message' => "The user with id {$id} doesn't exist"], 404);
        }
 
        $this->userRepository->delete($user);
 
        return response()->json(null, 204);
    }
}
```

## 9、验证

更新基础 Controller

```
<?php //app/Http/Controllers/Controller.php
 
namespace App\Http\Controllers;
 
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
 
class Controller extends BaseController
{
    /**
     * Validate HTTP request against the rules
     *
     * @param Request $request
     * @param array $rules
     * @return bool|array
     */
    protected function validateRequest(Request $request, array $rules)
    {
        // Perform Validation
        $validator = \Validator::make($request->all(), $rules);
 
        if ($validator->fails()) {
            $errorMessages = $validator->errors()->messages();
 
            // crete error message by using key and value
            foreach ($errorMessages as $key => $value) {
                $errorMessages[$key] = $value[0];
            }
 
            return $errorMessages;
        }
 
        return true;
    }
}
```

更新 UserController

```
<?php //app/Http/Controllers/UserController.php
 
 
    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function store(Request $request)
    {
        // Validation
        $validatorResponse = $this->validateRequest($request, $this->storeRequestValidationRules());
 
        // Send failed response if validation fails
        if ($validatorResponse !== true) {
            return $this->sendInvalidFieldResponse($validatorResponse);
        }
 
     /*Rest of the codes*/
    }
 
 
    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validation
        $validatorResponse = $this->validateRequest($request, $this->updateRequestValidationRules($request));
 
        // Send failed response if validation fails
        if ($validatorResponse !== true) {
            return $this->sendInvalidFieldResponse($validatorResponse);
        }
 
        /*Rest of the codes*/
    }
 
 
    /**
     * Store Request Validation Rules
     *
     * @return array
     */
    private function storeRequestValidationRules()
    {
        return [
            'email'                 => 'email|required|unique:users',
            'firstName'             => 'required|max:100',
            'middleName'            => 'max:50',
            'lastName'              => 'required|max:100',
            'username'              => 'max:50',
            'address'               => 'max:255',
            'zipCode'               => 'max:10',
            'phone'                 => 'max:20',
            'mobile'                => 'max:20',
            'city'                  => 'max:100',
            'state'                 => 'max:100',
            'country'               => 'max:100',
            'type'                  => '',
            'password'              => 'min:5'
        ];
    }
 
    /**
     * Update Request validation Rules
     *
     * @param Request $request
     * @return array
     */
    private function updateRequestValidationRules(Request $request)
    {
        $userId = $request->segment(2);
        return [
            'email'                 => 'email|unique:users,email,'. $userId,
            'firstName'             => 'max:100',
            'middleName'            => 'max:50',
            'lastName'              => 'max:100',
            'username'              => 'max:50',
            'address'               => 'max:255',
            'zipCode'               => 'max:10',
            'phone'                 => 'max:20',
            'mobile'                => 'max:20',
            'city'                  => 'max:100',
            'state'                 => 'max:100',
            'country'               => 'max:100',
            'type'                  => '',
            'password'              => 'min:5'
        ];
    }

```

### ResponseTrait

```
<?php //app/Http/Controllers/ResponseTrait.php
 
namespace App\Http\Controllers;
 
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
 
trait ResponseTrait
{
    /**
     * Status code of response
     *
     * @var int
     */
    protected $statusCode = 200;
 
    /**
     * Fractal manager instance
     *
     * @var Manager
     */
    protected $fractal;
 
    /**
     * Set fractal Manager instance
     *
     * @param Manager $fractal
     * @return void
     */
    public function setFractal(Manager $fractal)
    {
        $this->fractal = $fractal;
    }
 
    /**
     * Getter for statusCode
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }
 
    /**
     * Setter for statusCode
     *
     * @param int $statusCode Value to set
     *
     * @return self
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
 
        return $this;
    }
 
    /**
     * Send custom data response
     *
     * @param $status
     * @param $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendCustomResponse($status, $message)
    {
        return response()->json(['status' => $status, 'message' => $message], $status);
    }
 
    /**
     * Send this response when api user provide fields that doesn't exist in our application
     *
     * @param $errors
     * @return mixed
     */
    public function sendUnknownFieldResponse($errors)
    {
        return response()->json((['status' => 400, 'unknown_fields' => $errors]), 400);
    }
 
    /**
     * Send this response when api user provide filter that doesn't exist in our application
     *
     * @param $errors
     * @return mixed
     */
    public function sendInvalidFilterResponse($errors)
    {
        return response()->json((['status' => 400, 'invalid_filters' => $errors]), 400);
    }
 
    /**
     * Send this response when api user provide incorrect data type for the field
     *
     * @param $errors
     * @return mixed
     */
    public function sendInvalidFieldResponse($errors)
    {
        return response()->json((['status' => 400, 'invalid_fields' => $errors]), 400);
    }
 
    /**
     * Send this response when a api user try access a resource that they don't belong
     *
     * @return string
     */
    public function sendForbiddenResponse()
    {
        return response()->json(['status' => 403, 'message' => 'Forbidden'], 403);
    }
 
    /**
     * Send 404 not found response
     *
     * @param string $message
     * @return string
     */
    public function sendNotFoundResponse($message = '')
    {
        if ($message === '') {
            $message = 'The requested resource was not found';
        }
 
        return response()->json(['status' => 404, 'message' => $message], 404);
    }
 
    /**
     * Send empty data response
     *
     * @return string
     */
    public function sendEmptyDataResponse()
    {
        return response()->json(['data' => new \StdClass()]);
    }
 
    /**
     * Return collection response from the application
     *
     * @param array|LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection $collection
     * @param \Closure|TransformerAbstract $callback
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithCollection($collection, $callback)
    {
        $resource = new Collection($collection, $callback);
 
        //set empty data pagination
        if (empty($collection)) {
            $collection = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
            $resource = new Collection($collection, $callback);
        }
        $resource->setPaginator(new IlluminatePaginatorAdapter($collection));
 
        $rootScope = $this->fractal->createData($resource);
 
        return $this->respondWithArray($rootScope->toArray());
    }
 
    /**
     * Return single item response from the application
     *
     * @param Model $item
     * @param \Closure|TransformerAbstract $callback
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithItem($item, $callback)
    {
        $resource = new Item($item, $callback);
        $rootScope = $this->fractal->createData($resource);
 
        return $this->respondWithArray($rootScope->toArray());
    }
 
    /**
     * Return a json response from the application
     *
     * @param array $array
     * @param array $headers
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithArray(array $array, array $headers = [])
    {
        return response()->json($array, $this->statusCode, $headers);
    }
}
```

## 10、用 Fractal 格式化 Response
安装 `composer require league/fractal`

创建 Transformer

```
<?php //app/Transformers/UserTransformer.php
 
namespace App\Transformers;
 
use App\Models\User;
use League\Fractal\TransformerAbstract;
 
class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        $formattedUser = [
            'uid'                   => $user->uid,
            'firstName'             => $user->firstName,
            'lastName'              => $user->lastName,
            'middleName'            => $user->middleName,
            'username'              => $user->username,
            'email'                 => $user->email,
            'address'               => $user->address,
            'zipCode'               => $user->zipCode,
            'city'                  => $user->city,
            'state'                 => $user->state,
            'country'               => $user->country,
            'phone'                 => $user->phone,
            'mobile'                => $user->mobile,
            'type'                  => $user->type,
            'createdAt'             => (string) $user->created_at,
            'updatedAt'             => (string) $user->updated_at
        ];
 
        return $formattedUser;
    }
}
```

更新 UserController 加入 Transformer

```
<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\Contracts\UserRepository;
use Illuminate\Http\Request;
use App\Transformers\UserTransformer;

class UserController extends Controller
{
    /**
     * Instance of UserRepository
     *
     * @var UserRepository
     */
    private $userRepository;

    /**
     * Instanceof UserTransformer
     *
     * @var UserTransformer
     */
    private $userTransformer;

    /**
     * Constructor
     *
     * @param UserRepository $userRepository
     * @param UserTransformer $userTransformer
     */
    public function __construct(UserRepository $userRepository, UserTransformer $userTransformer)
    {
        $this->userRepository = $userRepository;
        $this->userTransformer = $userTransformer;
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $users = $this->userRepository->findBy($request->all());
        return $this->respondWithCollection($users, $this->userTransformer);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function show($id)
    {
        $user = $this->userRepository->findOne($id);
        if (!$user instanceof User) {
            return $this->sendNotFoundResponse("The user with id {$id} doesn't exist");
        }
        // Authorization
        $this->authorize('show', $user);
        return $this->respondWithItem($user, $this->userTransformer);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function store(Request $request)
    {
        // Validation
        $validatorResponse = $this->validateRequest($request, $this->storeRequestValidationRules($request));
        // Send failed response if validation fails
        if ($validatorResponse !== true) {
            return $this->sendInvalidFieldResponse($validatorResponse);
        }
        $user = $this->userRepository->save($request->all());
        if (!$user instanceof User) {
            return $this->sendCustomResponse(500, 'Error occurred on creating User');
        }
        return $this->setStatusCode(201)->respondWithItem($user, $this->userTransformer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Validation
        $validatorResponse = $this->validateRequest($request, $this->updateRequestValidationRules($request));
        // Send failed response if validation fails
        if ($validatorResponse !== true) {
            return $this->sendInvalidFieldResponse($validatorResponse);
        }
        $user = $this->userRepository->findOne($id);
        if (!$user instanceof User) {
            return $this->sendNotFoundResponse("The user with id {$id} doesn't exist");
        }
        // Authorization
        $this->authorize('update', $user);
        $user = $this->userRepository->update($user, $request->all());
        return $this->respondWithItem($user, $this->userTransformer);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function destroy($id)
    {
        $user = $this->userRepository->findOne($id);
        if (!$user instanceof User) {
            return $this->sendNotFoundResponse("The user with id {$id} doesn't exist");
        }
        // Authorization
        $this->authorize('destroy', $user);
        $this->userRepository->delete($user);
        return response()->json(null, 204);
    }

    /**
     * Store Request Validation Rules
     *
     * @param Request $request
     * @return array
     */
    private function storeRequestValidationRules(Request $request)
    {
        $rules = [
            'email'                 => 'email|required|unique:users',
            'firstName'             => 'required|max:100',
            'middleName'            => 'max:50',
            'lastName'              => 'required|max:100',
            'username'              => 'max:50',
            'address'               => 'max:255',
            'zipCode'               => 'max:10',
            'phone'                 => 'max:20',
            'mobile'                => 'max:20',
            'city'                  => 'max:100',
            'state'                 => 'max:100',
            'country'               => 'max:100',
            'password'              => 'min:5'
        ];
        $requestUser = $request->user();
        // Only admin user can set admin role.
        if ($requestUser instanceof User && $requestUser->role === User::ADMIN_ROLE) {
            $rules['role'] = 'in:BASIC_USER,ADMIN_USER';
        } else {
            $rules['role'] = 'in:BASIC_USER';
        }
        return $rules;
    }
    
    /**
     * Update Request validation Rules
     *
     * @param Request $request
     * @return array
     */
    private function updateRequestValidationRules(Request $request)
    {
        $userId = $request->segment(2);
        $rules = [
            'email'                 => 'email|unique:users,email,'. $userId,
            'firstName'             => 'max:100',
            'middleName'            => 'max:50',
            'lastName'              => 'max:100',
            'username'              => 'max:50',
            'address'               => 'max:255',
            'zipCode'               => 'max:10',
            'phone'                 => 'max:20',
            'mobile'                => 'max:20',
            'city'                  => 'max:100',
            'state'                 => 'max:100',
            'country'               => 'max:100',
            'password'              => 'min:5'
        ];
        $requestUser = $request->user();
        // Only admin user can update admin role.
        if ($requestUser instanceof User && $requestUser->role === User::ADMIN_ROLE) {
            $rules['role'] = 'in:BASIC_USER,ADMIN_USER';
        } else {
            $rules['role'] = 'in:BASIC_USER';
        }
        return $rules;
    }
}

```

## 11、使用 Fake Data 测试
创建 UserFactor

```
<?php // database/factories/UserFactory.php

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'uid'                   => str_random(32),
        'firstName'             => $faker->firstName,
        'lastName'              => $faker->lastName,
        'email'                 => $faker->email,
        'middleName'            => $faker->lastName,
        'password'              => \Illuminate\Support\Facades\Hash::make('test-password'),
        'address'               => $faker->address,
        'zipCode'               => $faker->postcode,
        'username'              => $faker->userName,
        'city'                  => $faker->city,
        'state'                 => $faker->state,
        'country'               => 'CHN',
        'phone'                 => $faker->phoneNumber,
        'mobile'                => $faker->phoneNumber,
        'type'                  => 'USER',
        'isActive'              => rand(0, 1)
    ];
});
```

使用 make:seeder 创建 SeedClass
`php artisan make:seeder UsersTableSeeder`

修改 UsersTableSeeder.php 中的 up 方法

```
// database/seeds/UsersTableSeeder.php
 
public function run()
{
    factory(App\Models\User::class, 5)->create();
}
```

DatabaseSeeder 中 up 方法
```
	public function run()
    {
        // $this->call('UsersTableSeeder');
        $this->call('UsersTableSeeder');
    }
```

运行 `php artisan db:seed`

### 12、身份验证
Lumen 不集成 Passport 组件，我们需要加载第三方包 https://github.com/dusterio/lumen-passport；

composer 安装包
`composer require dusterio/lumen-passport`

安装完成，做初始化配置 bootstrap/app.php

```
// Enable Facades
$app->withFacades();

// Enable Eloquent
$app->withEloquent();

// Enable auth middleware (shipped with Lumen)
$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class,
]);

// Finally register two service providers - original one and Lumen adapter
$app->register(Laravel\Passport\PassportServiceProvider::class);
$app->register(Dusterio\LumenPassport\PassportServiceProvider::class);
```

做初始化操作

```
# Create new tables for Passport
php artisan migrate
 
# Install encryption keys and other necessary stuff for Passport
php artisan passport:install
```

### OAuth2 配置
Lumen 默认没有 config 文件夹，创建文件夹以及 auth.php 配置文件

```
<?php //config/auth.php

return [ 'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],
 
    'guards' => [
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
    ],
 
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => \App\Models\User::class
        ]
    ]
];
```

添加 HasApiTokens trait 到 User Model.

```
use Laravel\Passport\HasApiTokens;
class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, SoftDeletes, HasApiTokens;
 
    /* rest of the model */
}
```

修改访问权限

```
// app/Providers/AuthServiceProvider.php
 
    Passport::tokensCan([
        'admin' => 'Admin user scope',
        'basic' => 'Basic user scope',
    ]);

```

路由增加中间件

```
//routes/web.php
 
$router->group(['middleware' => 'auth:api'], function () use ($router) {
    $router->post('users', 'UserController@store');
    $router->get('users', 'UserController@index');
    $router->get('users/{id}', 'UserController@show');
    $router->put('users/{id}', 'UserController@update');
    $router->delete('users/{id}', 'UserController@destroy');
});
```






