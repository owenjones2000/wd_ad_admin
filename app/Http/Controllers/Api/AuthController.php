<?php
/**
 * File UserController.php
 *
 * @author Tuan Duong <bacduong@gmail.com>
 * @package Laravue
 * @version 1.0
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Advertise\AccountApiToken;
use App\Models\Advertise\ApiToken;
use Illuminate\Http\Request;
use Validator;

/**
 * Class UserController
 *
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    public function tokenList(Request $request){
        return ApiToken::query()->where(['bundle_id' => $request->input('bundle_id')])->paginate();
    }

    public function accountTokenList(Request $request){
        return AccountApiToken::query()->where(['user_id' => $request->input('user_id')])->paginate();
    }

    public function makeToken(Request $request){
        $api_token = ApiToken::Make(
            $request->input('bundle_id'),
            date('Y-m-d', strtotime($request->input('expired_at', '2100-11-11')))
        );
        return ['api_token' => $api_token['access_token']];
    }

    public function makeAccountToken(Request $request){
        $expireAt = $request->input('expired_at');
        $api_token = AccountApiToken::Make(
            $request->input('user_id'),
            $expireAt
        );
        return ['api_token' => $api_token['access_token']];
    }

    public function destroy($id){
        ApiToken::query()->where(['id' => $id]) ->delete();
        return response()->json(null, 204);
    }
    public function delAccountToken($id){
        AccountApiToken::query()->where(['id' => $id]) ->delete();
        return response()->json(null, 204);
    }
    
}
