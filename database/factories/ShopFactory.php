<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model\Shop;
use Faker\Generator as Faker;


$factory->define(Shop::class, function (Faker $faker) {
    return [
        'two_abbr0'=>rand(1,5),
        'two_abbr1'=>rand(1,5),
        'two_abbr2'=>rand(1,5),
        'name'=>$faker->name,
        'area'=>$faker->address,
        'detailed_address'=>$faker->address,
        'contact_phone'=>$faker->phoneNumber,
        'wechat'=>$faker->phoneNumber,
        'logo'=>json_encode([
            'store_logo'=>'https://img95.699pic.com/photo/50059/6665.jpg_wh860.jpg',
            'business_license'=>'https://img95.699pic.com/photo/50059/6665.jpg_wh860.jpg',
            'with_iD_card'=>'https://img95.699pic.com/photo/50059/6665.jpg_wh860.jpg',
            'professional_qualification'=>'https://img95.699pic.com/photo/50059/6665.jpg_wh860.jpg'
        ]),
        'service_price'=>'https://img95.699pic.com/photo/50059/6665.jpg_wh860.jpg',
        'merchant_introduction'=>$faker->text,
        'is_top'=>$faker->boolean,
        'lng'=>$faker->randomFloat(),
        'lat'=>$faker->randomFloat(),
        'user_id'=>1,
        'no'=>Shop::findAvailableNo(),
        'amount' => 0.01,
        'is_accept'=>1,
        'type'=>array_rand(["one"=>1, "two"=>1], 1),
        'top_amount'=>0.01,

    ];
});
