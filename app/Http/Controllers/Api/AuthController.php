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
use App\Http\Resources\ChannelResource;
use App\Http\Resources\UserResource;
use App\Laravue\JsonResponse;
use App\Laravue\Models\Permission;
use App\Laravue\Models\Role;
use App\Laravue\Models\User;
use App\Models\Channel;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
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

    public function makeToken(Request $request){
        $api_token = ApiToken::Make(
            $request->input('bundle_id'),
            date('Y-m-d', strtotime($request->input('expired_at', '2100-11-11')))
        );
        return ['api_token' => $api_token['access_token']];
    }

    public function destroy($id){
        ApiToken::query()->where(['id' => $id]) ->delete();
        return response()->json(null, 204);
    }
}
