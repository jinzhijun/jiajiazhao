<?php

namespace App\Http\Controllers\Api\V1;


use App\Shop;
use App\User;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MakeQrCodeController extends Controller
{

    public function makeShare(Request $request)
    {

        return $this->qrCode($request,$request->width);
    }

    public function qrCode($request,$width)
    {
        $config = [
            'app_id' => 'wx693aa465df66510b',
            'secret' => config('wechat.mini_program.default.secret'),

            // 下面为可选项
            // 指定 API 调用返回结果的类型：array(default)/collection/object/raw/自定义类名
            'response_type' => 'array',
        ];
        $app = Factory::miniProgram($config);
        try {
            $response = $app->app_code->get($request->path, [
                'width' => $width,
                'line_color' => $request->line_color
            ]);
            // $response 成功时为 EasyWeChat\Kernel\Http\StreamResponse 实例，失败为数组或你指定的 API 返回类型
            if ($response instanceof \EasyWeChat\Kernel\Http\StreamResponse) {

                $img =  date("YmdHis", time()) . '-' . uniqid() . ".png";
                $filename = $response->saveAs(storage_path('app/public'),  $img);
                // return $filename;
                $data['code'] = 200;
                $data['url'] =   $filename;

                // 如果 image 字段本身就已经是完整的 url 就直接返回
                if (Str::startsWith($filename, ['http://', 'https://'])) {
                    Log::info(5);
                    return [
                        'code'=>200,
                        'msg'=>'ok',
                        'date'=>$filename
                    ];
//                    return $data['url'];
                }
                Log::info(6);
                return [
                    'code'=>200,
                    'msg'=>'ok',
                    'date'=>\Storage::disk('public')->url($filename)
                ];

            } else {
                Log::info('生成失败');
                return [
                    'code'=>422,
                    'msg'=>'生成失败',
                    'date'=>[]
                ];
            }
        } catch (\Exception $e) {
            return [
                'code'=>422,
                'msg'=>$e->getMessage(),
                'date'=>[]
            ];
        }
    }
    // 
    public function shareFriend(Request $request)
    {
        $qrCodeImage = $this->qrCode($request,220)['date'];
        $config = array(
            'image'=>array(
                array(
                    'url'=>$qrCodeImage,//json_encode($shop->logo['store_logo']),       //图片资源路径
                    'left'=>196,
                    'top'=>-210,
                    'stream'=>0,             //图片资源是否是字符串图像流
                    'right'=>0,
                    'bottom'=>0,
                    'width'=>220,
                    'height'=>220,
                    'opacity'=>100
                ),
            ),
            'background'=>config('app.url').'/WechatIMG92.jpeg',
        );
        $filename = time().'.jpg';
        return [
            'msg'=>'ok',
            'code'=>200,
            'date'=>'https://api.jjz369.com/'.$this->createPoster($config,$filename)
        ];
    }

    public function makeHaiBao(Request $request)
    {
        $fontPath = config('app.fontPath');//'/System/Library/Fonts/Hiragino Sans GB.ttc';

        $qrCodeImage = $this->qrCode($request,350)['date'];
        // 店铺介绍
        $shop = Shop::where('id',$request->id)->firstOrFail();
        // 联系电话
        $phone = array(
            'text'=>User::where('id',$shop->user_id)->value('phone'),
            'left'=>346,
            'top'=>-130,
            'fontPath'=>$fontPath,//\Storage::disk('public')->url("Avenir.ttc"),//'qrcode/simhei.ttf',     //字体文件
            'fontSize'=>52,             //字号
            'fontColor'=>'0,0,0',       //字体颜色
            'angle'=>0,
        );
        // 店铺地址
        $shopArea = array(
            'text'=>$shop->detailed_address,
            'left'=>323,
            'top'=>-50,
            'fontPath'=>$fontPath,//\Storage::disk('public')->url("Avenir.ttc"),//'qrcode/simhei.ttf',     //字体文件
            'fontSize'=>30,             //字号
            'fontColor'=>'0,0,0',       //字体颜色
            'angle'=>0,
        );

        $textStr = str_replace('&nbsp;','',\request('content'));//"据说这样输出速度要快一些，原因在于可以接受多个参数，并直接按顺序输出，实际上逗号不是拼接字符串据说这样输出速度要快一些，原因在于可以接受多个参数，并直接按顺序输出，据说这样输出速度要快一些，原因在于可以接受多个参数，并直接按顺序输出，实际上逗号不是拼接字符串据说这样输出速度要快一些，原因在于可以接受多个参数，并直接按顺序输出，据说这样输出速度要快一些，原因在于可以接受多个参数，并直接按顺序输出，实际上逗号不是拼接字符串据说这样输出速度要快一些，原因在于可以接受多个参数，并直接按顺序输出，据说这样输出速度要快一些，原因在于可以接受多个参数，并直接按顺序输出，实际上逗号不是拼接字符串据说这样输出速度要快一些，原因在于可以接受多个参数，并直接按顺序输出，据说这样输出速度要快一些，原因在于可以接受多个参数，并直接按顺序输出，实际上逗号不是拼接字符串据说这样输出速度要快一些，原因在于可以接受多个参数，并直接按顺序输出，实际上逗号不是拼接字符串据说这样输出速度要快一些，原因在于echo可以接受多个参数，并直接按顺序输出，实际上逗号不是拼接字符串据说这样输出速度要快一些，原因在于echo可以接受多个参数，并直接按顺序输出，实际上逗号不是拼接字符串据说这样输出速度要快一些，原因在于echo可以接受多个参数，并直接按顺序输出，实际上逗号不是拼接字符串据说这样输出速度要快一些，原因在于echo可以接受多个参数，并直接按顺序输出，实际上逗号不是拼接字符串";

        $textLength = mb_strlen($textStr);// 字符串长度
        $ccvv  = 19;
//        return $textLength;
        $iFor =  ceil($textLength/$ccvv);
        $text1[] = $phone;
        $text1[] = $shopArea;
        $strlen = $shop->name;
        if (strlen($strlen) >45) {
            $strlen0 = mb_substr($strlen,0,8,"UTF-8");
            $left = 220;
            $top = 334;

//            $background = config('app.url').'/16.jpg';
            $strlen1 = mb_substr($strlen,8,8,"UTF-8");
            $left1 = 220;
            $top1 = 415;
        }elseif (strlen($strlen) >42) {
            $strlen0 = mb_substr($strlen,0,7,"UTF-8");
            $left = 260;
            $top = 334;

//            $background = config('app.url').'/15.jpg';
            $strlen1 = mb_substr($strlen,7,8,"UTF-8");
            $left1 = 220;
            $top1 = 415;
        }elseif (strlen($strlen) >39) {
            $strlen0 = mb_substr($strlen,0,7,"UTF-8");
            $left = 260;
            $top = 334;

//            $background = config('app.url').'/14.jpg';
            $strlen1 = mb_substr($strlen,7,7,"UTF-8");
            $left1 = 260;
            $top1 = 415;
        }elseif (strlen($strlen) >36) {
            $strlen0 = mb_substr($strlen,0,6,"UTF-8");
            $left = 300;
            $top = 334;

//            $background = config('app.url').'/13.jpg';
            $strlen1 = mb_substr($strlen,6,7,"UTF-8");
            $left1 = 260;
            $top1 = 415;
        }elseif (strlen($strlen) >33) {
            $strlen0 = mb_substr($strlen,0,6,"UTF-8");
            $left = 300;
            $top = 334;

//            $background = config('app.url').'/12.jpg';
            $strlen1 = mb_substr($strlen,6,6,"UTF-8");
            $left1 = 300;
            $top1 = 415;
        }elseif (strlen($strlen) >30) {
            $strlen0 = mb_substr($strlen,0,5,"UTF-8");
            $left = 340;
            $top = 334;

//            $background = config('app.url').'/11.jpg';
            $strlen1 = mb_substr($strlen,5,6,"UTF-8");
            $left1 = 300;
            $top1 = 415;
        }elseif (strlen($strlen) >27) {
            $strlen0 = mb_substr($strlen,0,5,"UTF-8");
            $left = 340;
            $top = 334;

//            $background = config('app.url').'/10.jpg';
            $strlen1 = mb_substr($strlen,5,5,"UTF-8");
            $left1 = 340;
            $top1 = 415;
        }elseif (strlen($strlen) >24) {
            $strlen0 = mb_substr($strlen,0,4,"UTF-8");
            $left = 380;
            $top = 334;

//            $background = config('app.url').'/9.jpg';
            $strlen1 = mb_substr($strlen,4,5,"UTF-8");
            $left1 = 340;
            $top1 = 415;
        }elseif (strlen($strlen) >21) {
            $strlen0 = $strlen;
            $left = 216;
            $top = 350;
//            $background = config('app.url').'/8.jpg';
        }elseif (strlen($strlen) >18) {
            $strlen0 = $strlen;

            $left = 255;
            $top = 350;
//            $background = config('app.url').'/7.jpg';
        }elseif (strlen($strlen) >15) {
            $strlen0 = $strlen;

            $left = 300;
            $top = 350;
//            $background = config('app.url').'/6.jpg';
        }elseif (strlen($strlen) >12) {
            $strlen0 = $strlen;

            $left = 343;
            $top = 350;
//            $background = config('app.url').'/5.jpg';
        }elseif (strlen($strlen) >9) {
            $strlen0 = $strlen;
            $left = 376;
            $top = 350;
//            $background = config('app.url').'/4.jpg';
        }elseif (strlen($strlen) >6) {
            $strlen0 = $strlen;

            $left = 420;
            $top = 350;
//            $background = config('app.url').'/3.jpg';
        }else {
            $strlen0 = $strlen;

            $left = 450;
            $top = 350;
//            $background = config('app.url').'/2.jpg';
        }
        $text1[] = array(
            'text'=>$strlen0,
            'left'=>$left,
            'top'=>$top,
            'fontPath'=>$fontPath,//\Storage::disk('public')->url("Avenir.ttc"),//'qrcode/simhei.ttf',     //字体文件
            'fontSize'=>60,             //字号
            'fontColor'=>'0,0,0',       //字体颜色
            'angle'=>0,
        );
        if (strlen($strlen) >24) {
            $text1[] = array(
                'text'=>$strlen1,
                'left'=>$left1,
                'top'=>$top1,
                'fontPath'=>$fontPath,//\Storage::disk('public')->url("Avenir.ttc"),//'qrcode/simhei.ttf',     //字体文件
                'fontSize'=>60,             //字号
                'fontColor'=>'0,0,0',       //字体颜色
                'angle'=>0,
            );
        }
        $iFor = $iFor>4?4:$iFor;
        for ($i=0;$i < $iFor;$i++) {
            $text1[] = $this->content(mb_substr($textStr, $i*$ccvv,$ccvv),$i*70,$fontPath);
        }
        $config = array(
//            'text'=>array(
//                $phone,
//                $shopArea,
//            ),
            'text'=>$text1,
            'image'=>array(
                array(
                    //         return \Storage::disk('public')->url($image);
                    'url'=>$qrCodeImage,//json_encode($shop->logo['store_logo']),       //图片资源路径
                    'left'=>280,
                    'top'=>-306,
                    'stream'=>0,             //图片资源是否是字符串图像流
                    'right'=>0,
                    'bottom'=>0,
                    'width'=>500,
                    'height'=>500,
                    'opacity'=>100
                ),
                array(
//                    'url'=>$shop->images?$this->logo($shop,$shop->images[0]):config('app.url')."/null.png",
                    'url'=>$this->logo($shop,$shop->logo['store_logo']),// "http://admin.jiajiazhao.dev/storage/3uGY8r8y0v12gpgAqjUP0DzMnAYp3j5GSq12HKL5.jpg",//str_replace("\/","/",json_encode($shop->logo['store_logo'])),//config('app.url')."/XuZBGE4VcDCcUqDbtzkzDfJ5wT9cEAl0SsHTBNWp.jpg",
//                    'url'=>"http://admin.jiajiazhao.dev/storage/3uGY8r8y0v12gpgAqjUP0DzMnAYp3j5GSq12HKL5.jpg",//str_replace("\/","/",json_encode($shop->logo['store_logo'])),//config('app.url')."/XuZBGE4VcDCcUqDbtzkzDfJ5wT9cEAl0SsHTBNWp.jpg",
//$this->logo($shop,$shop->logo['store_logo']),//
                    'left'=>60,
                    'top'=>450,
                    'right'=>0,
                    'stream'=>0,
                    'bottom'=>0,
                    'width'=>970,
                    'height'=>550,
                    'opacity'=>100
                ),
//                array(
//                    'url'=>$this->logo($shop,$shop->logo['store_logo']),//"http://admin.jiajiazhao.dev/storage/3uGY8r8y0v12gpgAqjUP0DzMnAYp3j5GSq12HKL5.jpg",//str_replace("\/","/",json_encode($shop->logo['store_logo'])),//config('app.url')."/XuZBGE4VcDCcUqDbtzkzDfJ5wT9cEAl0SsHTBNWp.jpg",
//                    'left'=>60,
//                    'top'=>1530,
//                    'right'=>0,
//                    'stream'=>0,
//                    'bottom'=>0,
//                    'width'=>970,
//                    'height'=>550,
//                    'opacity'=>100
//                ),
            ),
//            'background'=>$background,
            'background'=>config('app.url').'/WechatIMG94.jpeg',
        );
//        return $config;
        $filename = time().'.jpg';
//        return config('app.url').'/'.$this->createPoster($config);
        return [
            'msg'=>'ok',
            'code'=>200,
            'date'=>'https://api.jjz369.com/'.$this->createPoster($config,$filename)
//            'date'=>config('app.url').'/'.$this->createPoster($config,$filename)
        ];

    }

    public function logo($shop,$parm)
    {
        $store_logo = str_replace("http:\/\/admin.jjz369.com\/\/storage",'',json_encode($parm));
        // str_replace("\/","/",$store_logo)
        $store_logo = \Storage::disk('public')->url(str_replace("\/","/",$store_logo));
//        return $store_logo;
        $store_logo = str_replace('"','',$store_logo);
        return $store_logo;
    }

    public function content($text,$top,$fontPath)
    {
        return array(
            'text'=>$text,
            'left'=>122,
            'top'=>1100+$top,
            'fontPath'=>$fontPath,//\Storage::disk('public')->url("Avenir.ttc"),//'qrcode/simhei.ttf',     //字体文件
            'fontSize'=>33,             //字号
            'fontColor'=>'0,0,0',       //字体颜色
            'angle'=>0,
        );
    }
    /**
     * 生成宣传海报
     * @param array  参数,包括图片和文字
     * @param string  $filename 生成海报文件名,不传此参数则不生成文件,直接输出图片
     * @return [type] [description]
     */
    public function createPoster($config=array(),$filename=""){
        //如果要看报什么错，可以先注释调这个header
        if(empty($filename)) header("content-type: image/png");
        $imageDefault = array(
            'left'=>0,
            'top'=>0,
            'right'=>0,
            'bottom'=>0,
            'width'=>100,
            'height'=>100,
            'opacity'=>100
        );
        $textDefault = array(
            'text'=>'123',
            'left'=>0,
            'top'=>0,
            'fontSize'=>32,       //字号
            'fontColor'=>'255,255,255', //字体颜色
            'angle'=>0,
        );
        $background = $config['background'];//海报最底层得背景
        //背景方法
        $backgroundInfo = getimagesize($background);
        $backgroundFun = 'imagecreatefrom'.image_type_to_extension($backgroundInfo[2], false);
        $background = $backgroundFun($background);
        $backgroundWidth = imagesx($background);  //背景宽度
        $backgroundHeight = imagesy($background);  //背景高度
        $imageRes = imageCreatetruecolor($backgroundWidth,$backgroundHeight);
        $color = imagecolorallocate($imageRes, 0, 0, 0);
        imagefill($imageRes, 0, 0, $color);
        // imageColorTransparent($imageRes, $color);  //颜色透明
        imagecopyresampled($imageRes,$background,0,0,0,0,imagesx($background),imagesy($background),imagesx($background),imagesy($background));
        //处理了图片
        if(!empty($config['image'])){
            foreach ($config['image'] as $key => $val) {
                $val = array_merge($imageDefault,$val);
                $info = getimagesize($val['url']);
                $function = 'imagecreatefrom'.image_type_to_extension($info[2], false);
                if($val['stream']){   //如果传的是字符串图像流
                    $info = getimagesizefromstring($val['url']);
                    $function = 'imagecreatefromstring';
                }
                $res = $function($val['url']);
                $resWidth = $info[0];
                $resHeight = $info[1];
                //建立画板 ，缩放图片至指定尺寸
                $canvas=imagecreatetruecolor($val['width'], $val['height']);
                imagefill($canvas, 0, 0, $color);
                //关键函数，参数（目标资源，源，目标资源的开始坐标x,y, 源资源的开始坐标x,y,目标资源的宽高w,h,源资源的宽高w,h）
                imagecopyresampled($canvas, $res, 0, 0, 0, 0, $val['width'], $val['height'],$resWidth,$resHeight);
                $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']) - $val['width']:$val['left'];
                $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']) - $val['height']:$val['top'];
                //放置图像
                imagecopymerge($imageRes,$canvas, $val['left'],$val['top'],$val['right'],$val['bottom'],$val['width'],$val['height'],$val['opacity']);//左，上，右，下，宽度，高度，透明度
            }
        }
        //处理文字
        if(!empty($config['text'])){
            foreach ($config['text'] as $key => $val) {
                $val = array_merge($textDefault,$val);
                list($R,$G,$B) = explode(',', $val['fontColor']);
                $fontColor = imagecolorallocate($imageRes, $R, $G, $B);
                $val['left'] = $val['left']<0?$backgroundWidth- abs($val['left']):$val['left'];
                $val['top'] = $val['top']<0?$backgroundHeight- abs($val['top']):$val['top'];
                imagettftext($imageRes,$val['fontSize'],$val['angle'],$val['left'],$val['top'],$fontColor,$val['fontPath'],$val['text']);
            }
        }

        //生成图片
        if(!empty($filename)){
            $res = imagejpeg ($imageRes,$filename,90); //保存到本地
            imagedestroy($imageRes);
            if(!$res) return false;
            return $filename;
        }else{
            imagejpeg ($imageRes);     //在浏览器上显示
            imagedestroy($imageRes);
        }
    }


}
