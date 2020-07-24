<?php
/**
 * File AuthController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */
namespace App\Http\Controllers;

use App\Laravue\JsonResponse;
use App\Laravue\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use GuzzleHttp\Client;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class AuthController
 *
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $this->validate($request, [
            'phone' => 'required',
            'code' => 'required'
        ]);
        $phone = $request->input('phone');
        $code = $request->input('code');
        $key = "253_verification_code_" . $phone;
        // $credentials = $request->only('email', 'password');
        // if ($token = $this->guard()->attempt($credentials)) {
        //     return response()->json(new UserResource(Auth::user()), Response::HTTP_OK)->header('Authorization', $token);
        // }
        $user = User::query()->where('phone', $phone)->first();
        $cacheCode = Redis::get($key);
        // dd($user, $code, $cacheCode);
        if ($user && $code == $cacheCode){
            $token = JWTAuth::fromUser($user);
            Redis::del($key);
            return response()->json(new UserResource($user), Response::HTTP_OK)->header('Authorization', $token);
        }
        return response()->json(new JsonResponse([], 'login_error'), Response::HTTP_UNAUTHORIZED);
    }

    public function sendCode(Request $request)
    {
        $code = mt_rand(10000, 99999);
        $phone = $request->input('phone');
        $exist =  User::query()->where('phone', $phone)->first();
        if (!$exist) {
            return response()->json(['status'=> false, 'error'=> 'please submit correct phone']);
        }
        $params = [
            "code"  => $code,
            "phone" => '86' . $phone
        ];
        $key = "253_verification_code_" . $phone;
        Redis::setex($key, 600, $code);
        $client = new Client();
        $res = $client->get("http://gocn.luckfun.vip/253code", [
            'query' => $params
        ]);
        $content = $res->getBody()->getContents();
        $data = json_decode($content, true);
        
        if ($data && isset($data['result']) && $data['result'] === true) {
            $result = ["status" => true, "message_id" => $data['message_id']];
        }else {
            $result = ["status" => false, "error" => $content['errorMsg']];
        }
        return response()->json($result);
    }
    
    public function logout()
    {
        $this->guard()->logout();
        return response()->json((new JsonResponse())->success([]), Response::HTTP_OK);
    }

    public function user()
    {
        return new UserResource(Auth::user());
    }

    public function update(Request $request){
        /** @var User $user */
        $user = Auth::user();
        if ($user->isAdmin()) {
            return response()->json(['error' => 'Admin can not be modified'], 403);
        }

        $validator = Validator::make($request->all(), $this->getValidationRules(false));
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $email = $request->get('email');
            $found = User::where('email', $email)->first();
            if ($found && $found->id !== $user->id) {
                return response()->json(['error' => 'Email has been taken'], 403);
            }

            $user->name = $request->get('name');
            $user->email = $email;
            if(!empty($request->input('password'))){
                $user['password'] = Hash::make($request->input('password'));
            }
            $user->save();
            return new UserResource($user);
        }
    }

    /**
     * @param bool $isNew
     * @return array
     */
    private function getValidationRules($isNew = true)
    {
        return [
            'name' => 'required',
            'email' => $isNew ? 'required|email|unique:users' : 'required|email',
            'roles' => [
                'required',
                'array'
            ],
        ];
    }

    /**
     * @return mixed
     */
    private function guard()
    {
        return Auth::guard();
    }
}
