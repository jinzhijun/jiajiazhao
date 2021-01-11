<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\AuthMlOpenidStoreRequest;
use App\Http\Requests\AuthPhoneStoreRequest;
use App\Http\Requests\AuthUserInfoRequest;
use App\Transformers\UserTransformer;
use App\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        
    }
    // 创建一个测试用户
    public function createTestUser()
    {
        $user = User::findOrFail(1);
        $token = \Auth::guard('api')->fromUser($user);
        return $this->respondWithToken($token,1,$user)->setStatusCode(201);
        return User::create([
            'ml_openid' => 1,
            'nickname' => 'nickName',
            'avatar' => 'avatarUrl',
            'sex' => 1,
            'parent_id' =>  null
        ]);
    }

    // 获取用户的openid
    public function mlOpenidStore(AuthMlOpenidStoreRequest $request)
    {

        $app = app('wechat.mini_program');

        $code = $request->code;
        Log::info($code);
        $sessionUser = $app->auth->session($code);
        Log::info($sessionUser);
        if (!empty($sessionUser['errcode'])) {
            throw new \Exception('获取用户的openid操作失败!');
        }
        DB::beginTransaction();
        try {
            $openid = $sessionUser['openid'];
            Log::info($openid);

            $session_key = $sessionUser['session_key'];
            $user = User::where('ml_openid', $openid)->first();
            Log::info($user);

            Cache::put($code, ['session_key' => $session_key, 'ml_openid' => $openid], 3000);
            if ($user) { // 手机好存在直接登陆
                Log::info(1);
                if($user->phone) {
                    Log::info(2);

                    $token = \Auth::guard('api')->fromUser($user);
                    return $this->respondWithToken($token, $openid, $user);
                }
                Log::info(3);

                return $this->oauthNo();
            }
            Log::info('创建用户', $this->createUser($sessionUser, $request));

            User::create($this->createUser($sessionUser, $request));

            DB::commit();
            return $this->oauthNo();
        } catch (\Exception $ex) {
            DB::rollback();
            throw new \Exception($ex); // 报错原因大多是因为taskFlowCollections表，name和user_id一致
        }
    }
    //  获取手机号
    public function phoneStore(AuthPhoneStoreRequest $request)
    {
        $session = Cache::get($request->code);// 解析的问题
        if(!$session) {
            Log::error('用户code：'.$request->code);
            throw new \Exception('code 和第一次的不一致'.$request->code);
        }
        $app = app('wechat.mini_program');
        $decryptedData = $app->encryptor->decryptData($session['session_key'], $request->iv, $request->encrypted_data);

        if (empty($decryptedData)) {
            throw new \Exception('解析号码失败!321');
        }

        $user = User::where('ml_openid',$session['ml_openid'])->firstOrFail();
        $phoneNumber = $decryptedData['phoneNumber'];
        $user->update(['phone'=>$phoneNumber]);

        $token = \Auth::guard('api')->fromUser($user);
        return $this->respondWithToken($token,$phoneNumber,$user)->setStatusCode(201);
    }

    // 获取用户信息
    public function userInfo(AuthUserInfoRequest $request)
    {
        $session = Cache::get($request->code);// 解析的问题
        if(!$session) {
            Log::error('用户code：'.$request->code);
            throw new \Exception('code 和第一次的不一致'.$request->code);
        }
        $user = User::where('ml_openid',$session['ml_openid'])->firstOrFail();
        $user->update([
            'avatar'=>$request->avatar,
            'nickname'=>$request->nickname,
            'city'=>$request->city,
            'sex'=>$request->sex,
        ]);
        $token = \Auth::guard('api')->fromUser($user);
        return $this->respondWithToken($token,$user->phone,$user)->setStatusCode(201);
    }
    public function refresh()
    {
        $token = auth('api')->refresh();
        $user = auth()->user();
        return $this->respondWithToken($token, $user->ml_openid,$user);
    }

    public function show()
    {
        return $this->response->item($this->user(),new UserTransformer());
    }
    protected function oauthNo()
    {

        return $this->response->array([
            'code'=> 200,
            'data'=>[],
            'msg'=>'未授权手机号码'
        ]);
    }

    protected function createUser($sessionUser,$request)
    {
        $user = new User();
        return [ // 不存在此用户添加
            'ml_openid' => $sessionUser['openid'],
            'nickname' => $request->nickName,
            'avatar' => $request->avatarUrl,
            'sex' => $request->sex,
            'parent_id' => $request->ref_code ? User::where('ref_code',$request->ref_code)->value('parent_id') : null,
            'ref_code' => $user->generateRefCode()
        ];
    }

    protected function respondWithToken($token,$mlOpenid,$user)
    {
        return $this->response->array([
            'code' => 200,
            'msg'=>'ok',
            'data' => [
                'ml_openid' => $mlOpenid,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'phone'=>$user->phone,
                'expires_in' => Auth::guard('api')->factory()->getTTL() * 1200
            ]
        ]);
    }
}
