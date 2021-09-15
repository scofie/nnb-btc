<?php

/**
 * Created by PhpStorm.
 * User: swl
 * Date: 2018/7/3
 * Time: 10:23
 */
namespace App;

use App\Currency;
use App\Logic\WalletLogic;
use Illuminate\Database\Eloquent\Model;

class UsersWallet extends Model
{

    protected $table = 'users_wallet';

    public $timestamps = false;

    /* const CREATED_AT = 'create_time'; */
    const CURRENCY_DEFAULT = "YMT";

    protected $hidden = [
        'private'
    ];

    protected $appends = [
        'currency_name',
        'currency_type',
        'is_legal',
        'is_lever',
        'is_match',
        'is_micro',
        'cny_price',
        'pb_price',
        'usdt_price'
    ];

    public function getCreateTimeAttribute()
    {
        $value = $this->attributes['create_time'];
        return $value ? date('Y-m-d H:i:s', $value) : '';
    }

    public function getCurrencyTypeAttribute()
    {
        return $this->hasOne('App\Currency', 'id', 'currency')->value('type');
    }

    // public function getExrateAttribute()
    // {
    // // $value = $this->attributes['create_time'];
    // return $ExRate = Setting::getValueByKey('ExRate',6.5);;
    // }
    public function getCurrencyNameAttribute()
    {
        return $this->currencyCoin()->value('name');
    }

    public function getIsLegalAttribute()
    {
        return $this->currencyCoin()->value('is_legal');
    }

    public function getIsLeverAttribute()
    {
        return $this->currencyCoin()->value('is_lever');
    }

    public function getIsMatchAttribute()
    {
        return $this->currencyCoin()->value('is_match');
    }

    public function getIsMicroAttribute()
    {
        return $this->currencyCoin()->value('is_micro');
    }

    public function currencyCoin()
    {
        return $this->belongsTo(Currency::class, 'currency', 'id');
    }



    public static function makeWallet($user_id)
    {
        $currency = Currency::all();
        // $address_url = '/v3/wallet/address';
        // $project_name = config('app.name');
        // $http_client = app('LbxChainServer');
        // $response = $http_client->post($address_url, [
        //     'form_params' => [
        //         'userid' => $user_id,
        //         'projectname' => $project_name
        //     ]
        // ]);
        // $result = json_decode($response->getBody()->getContents());
        // if ($result->code != 0) {
        //     return false;
        // }
        // $address = $result->data;
        
        foreach ($currency as $key => $value) {
            $res = self::where([
                 'currency' => $value->id,
                'user_id' => $user_id
                ])->first();
            if(!$res){
                self::insert([
                     'currency' => $value->id,
                'user_id' => $user_id,
                'address' => null,
                'create_time' => time()
                    ]);
            }
          
        }
        return true;
    }

    public static function getAddress(UsersWallet $wallet){
        $saveFlag = false;
        if($wallet->currency == 3){//usdt两个地址
            if(!$wallet->address){
                //去获取地址 不接erc20
                $wallet->address = WalletLogic::getAddress("USDT_ERC20");
                $saveFlag = true;

            }
            if(!$wallet->address_2){
                $wallet->address_2 = WalletLogic::getAddress('USDT_OMNI');
                $saveFlag = true;

            }
            $return = ['omni'=>$wallet->address_2,'erc20' => $wallet->address];
        }else{
            if(!$wallet->address){
                $currency = Currency::find($wallet->currency);
                $wallet->address = WalletLogic::getAddress($currency->name);
                $saveFlag = true;
            }
            $return = $wallet->address;
        }
        if($saveFlag){
            $wallet->save();
        }
        return $return;
    }


    public static function getUsdtWallet($userId){
       return  self::where("user_id", $userId)
            ->where("currency", 3) //usdt
            ->first();
    }

    public static function getDF1Wallet($userId){
        return  self::where("user_id", $userId)
            ->where("currency", 12) //df One
            ->first();
    }


    public function getUsdtPriceAttribute()
    {
        return $this->currencyCoin()->value('price') ?? 1;
    }

    public function getPbPriceAttribute()
    {
        $currency_id = $this->attributes['currency'];
        return Currency::getPbPrice($currency_id);
    }

    public function getCnyPriceAttribute()
    {
        $currency_id = $this->attributes['currency'];
        return Currency::getCnyPrice($currency_id);
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'id');
    }

    public function getPrivateAttribute($value)
    {
        return empty($value) ? '' : decrypt($value);
    }

    public function setPrivateAttribute($value)
    {
        $this->attributes['private'] = encrypt($value);
    }

    public function getAccountNumberAttribute($value)
    {
        return $this->user()->value('account_number') ?? '';
    }
}
