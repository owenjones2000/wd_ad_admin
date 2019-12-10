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
use App\Laravue\Models\Role;
use App\Laravue\Models\User;
use App\Models\Advertise\ApiToken;
use App\Models\Advertise\Channel;
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
class ChannelController extends Controller
{
    const ITEM_PER_PAGE = 15;

    /**
     * Display a listing of the channel resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|ResourceCollection
     */
    public function list(Request $request)
    {
        $searchParams = $request->all();
        $channelQuery = Channel::query();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $keyword = Arr::get($searchParams, 'keyword', '');

        if (!empty($keyword)) {
            $channelQuery->where('name', 'LIKE', '%' . $keyword . '%');
        }

        return ChannelResource::collection($channelQuery->paginate($limit));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            array_merge(
                $this->getValidationRules(),
                [
                    'password' => ['required', 'min:6'],
                    'confirmPassword' => 'same:password',
                ]
            )
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        } else {
            $params = $request->all();
            $user = User::create([
                'name' => $params['name'],
                'email' => $params['email'],
                'password' => Hash::make($params['password']),
            ]);
            $role = Role::findByName($params['role']);
            $user->syncRoles($role);

            return new UserResource($user);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return ChannelResource|\Illuminate\Http\JsonResponse
     */
    public function show(Channel $channel)
    {
        return new ChannelResource($channel);
    }

    /**
     * save the resource in storage.
     *
     * @param Request $request
     * @param $id
     * @return ChannelResource|\Illuminate\Http\JsonResponse
     */
    public function save(Request $request, $id = null)
    {
        $validator = Validator::make($request->all(), $this->getValidationRules());
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 403);
        }
        if (empty($id)) {
            $channel = new Channel();
        } else {
            $channel = Channel::query()->where([
                'id' => $id
            ])->firstOrFail();
        }
        $channel->fill($request->all());
        if(empty($channel['name_hash'])){
            $channel['name_hash'] = md5($channel['bundle_id'].$channel['name'].$channel['platform']);
        }
        $channel->saveOrFail();

        return new ChannelResource($channel);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if ($user->isAdmin()) {
            response()->json(['error' => 'Ehhh! Can not delete admin user'], 403);
        }

        try {
            $user->delete();
        } catch (\Exception $ex) {
            response()->json(['error' => $ex->getMessage()], 403);
        }

        return response()->json(null, 204);
    }

    public function tokenList($id){
        $channel = Channel::findOrFail($id);
        return ApiToken::query()->where(['bundle_id' => $channel['bundle_id']])->get();
    }

    public function makeToken($id){
        $channel = Channel::findOrFail($id);
        $api_token = ApiToken::Make($channel['bundle_id']);
        return ['api_token' => $api_token['access_token']];
    }

    /**
     * @param bool $isNew
     * @return array
     */
    private function getValidationRules()
    {
        return [
            'name' => 'required',
        ];
    }
}
