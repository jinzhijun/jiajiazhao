<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\PaymentOrderRequest;
use App\Model\PaymentOrder;
use App\User;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayController extends Controller
{
    /**
     * @var \EasyWeChat\Payment\Application $app
     **/
    protected $app = null;
    // 提现到零钱 1付款成功,2待付款,3付款失败
    public function index()
    {
        $query = auth('api')->user()->pays();
        if($status = \request('status')) {
            $query = $query->where('status',$status);
        }
        $res = $query->orderBy('id','desc')->paginate();
        return $this->responseStyle('ok',200,$res);
    }
    //小程序配置
//    protected $config = [
//        // 必要配置
//        'app_id' => 'wx693aa465df66510b',
//        'mch_id'             => '',
//        'key'                => '',   // API 密钥
//        //         $fontPath = config('app.fontPath');//'/System/Library/Fonts/Hiragino Sans GB.ttc';
//        // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
//        'cert_path'          => config('app.cert'),
//        //'path/to/your/cert.pem', // XXX: 绝对路径！！！！
////        'cert_path'          => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
////        'key_path'           => 'path/to/your/key',      // XXX: 绝对路径！！！！
//        'key_path'           => config('app.key')      // XXX: 绝对路径！！！！
//
////        'notify_url'         => 'https://xxxxxx/api/order_pay_url',     // 你也可以在下单时单独设置来想覆盖它
//    ];
    // 提现到零钱 1付款成功,2待付款,3付款失败
    public function store(PaymentOrderRequest $request)
    {
        $user = auth('api')->user();
        $amount = $request->amount;
        DB::beginTransaction();
        try {
            if (bccomp($user->balance, $amount, 3) == -1) {
                return [
                    'msg'=>'余额不足',
                    'code'=>422,
                    'date'=>[]
                ];
                return $this->responseStyle('余额不足', 422, []);
            }
            $payOrder = new PaymentOrder();
            $payOrder->fill([
                'user_id' => $user->id,
                'order_number' => $this->getordernumber(),
                'amount' => $amount,
                'status' => 2,
            ]);

            $payOrder->save();

            User::where('id', $user->id)->decrement('balance', $amount);
            Log::info(123);

            DB::commit();
            return [
                'msg'=>'ok',
                'code'=>200,
                'date'=>$payOrder
            ];

        } catch (\Exception $ex) {
            DB::rollback();
            \Log::error('提现出错', ['error' => $ex]);
            return [
                'msg'=>'提现出错',
                'code'=>422,
                'date'=>$ex
            ];
        }
    }
    /**
     * 付款到微信
     *  string $amount,
    string $openid,
    string $user_id,
    string $desc = '提现',
    bool $checkUserName = false,
    string $userName = ""
     */
    public function payment(

    ){
        $order = $this->attemptCreatePaymentOrder(55, 2 , 1);
//        $order = $this->attemptCreatePaymentOrder(\request('user_id'), \request('amount') , 1);
        Log::info($order);
        $this->app = Factory::payment([
            // 必要配置
            'app_id' => env('WECHAT_PAYMENT_APPID'),
            'mch_id'             => env('WECHAT_PAYMENT_MCH_ID'),
            'key'                => env('WECHAT_PAYMENT_KEY'),   // API 密钥
            //         $fontPath = config('app.fontPath');//'/System/Library/Fonts/Hiragino Sans GB.ttc';
            // 如需使用敏感接口（如退款、发送红包等）需要配置 API 证书路径(登录商户平台下载 API 证书)
            'cert_path'          => "/www/wwwroot/jiajiazhao3/public//apiclient_cert.pem",
            //'path/to/your/cert.pem', // XXX: 绝对路径！！！！
//        'cert_path'          => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
//        'key_path'           => 'path/to/your/key',      // XXX: 绝对路径！！！！
            'key_path'           => "/www/wwwroot/jiajiazhao3/public//apiclient_key.pem"      // XXX: 绝对路径！！！！

//        'notify_url'         => 'https://xxxxxx/api/order_pay_url',     // 你也可以在下单时单独设置来想覆盖它
        ]);
//        return $this->app;
        $balanceData = [
            'partner_trade_no' => $order->order_number,
            'openid' => "oHIUO5BDkECawMJtgbbVmIzHyXMY",
            'amount' => 200,//0.01 * 100,
            'desc' => '提现',
            're_user_name' => ""
        ];
        $balanceData['check_name'] = false ? 'FORCE_CHECK' : 'NO_CHECK';
        $result = $this->app->transfer->toBalance($balanceData);
        \Log::info('付款到微信返回:' . json_encode($result, JSON_UNESCAPED_UNICODE));
        if ($result['return_code'] != 'SUCCESS' || $result['result_code'] != 'SUCCESS') {
            $msg = data_get($result, 'err_code_des');
            \Log::error('付款失败:' . $msg);
            $order->status = 2;
            $order->intro = $msg;
            $order->save();

            throw new \Exception($result, $order);
        }

        $order->payment_no = data_get($result, 'payment_no');
        $order->status = 1;
        $order->save();

        return true;
    }
    /**
     * 创建付款订单
     *
     * @param string $user_id
     * @param string $amount
     *
     * @return \Modules\Pay\Entities\PaymentOrder
     */
    protected function attemptCreatePaymentOrder(
         $user_id,
         $amount,
         $type
    ){
        $payOrder = new PaymentOrder();
        $payOrder->fill([
            'user_id' => $user_id,
            'order_number' => $this->getordernumber(),
            'amount' => $amount,
//            'type' => $type,
            'status' => 2,
        ]);

        $payOrder->save();

        return $payOrder;
    }
    //订单号
    private function getordernumber()
    {
        $num = time();
        $num = (date('YmdHis', $num)) . rand(1000, 9999);
        return $num;
    }
}
