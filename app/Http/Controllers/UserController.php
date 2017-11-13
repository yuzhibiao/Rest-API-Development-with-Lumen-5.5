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
