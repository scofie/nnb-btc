<?php


namespace App\Logic;


use App\Jobs\ZTPayAddressWithdraw;
use App\Service\RedisService;
use App\UsersWallet;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

class WalletLogic
{
    private static $url = "https://sapi.ztpay.org/api/v2";
    public static $appId = "ztpaya3sk0pzv0gyod";
    private static $appSecret = "sQgy2b47Omq4cfK5du24CldOCJI8q8QM";
    private static $eth_address = '0x9e7468c1acb6f6c47e01660f0909cac286f6da1c';//eth手续费地址
    private static $bit_address = '1CEGzxip5oxkAuXy45GxxNeL9a5VDVhQE';//BTC手续费地址
    private static $charge_fee = 0.006;
    private static $charge_fee_btc = 0.000035;
    public static function getAddress($currencyName){
        if($currencyName == 'USDT'){
            $currencyName = $currencyName.'_ERC20';
        }
        $data = [
            'appid' => self::$appId,
            'method' => 'get_address',
            'name' => $currencyName,
        ];

        $data['sign'] = self::getSign($data);
        $http_client = new Client();
        $response = $http_client->post(self::$url, [
             'form_params' => $data
         ]);
        $result = json_decode($response->getBody()->getContents());
        
        // dd($result);
        if ($result->code != 0) {
            throw new \Exception('请求地址接口出错');
        }else{
            return $result->data->address;
        }
    }

    public static function isChangeAddress($address){
        if(in_array($address,[
            self::$eth_address,
            self::$bit_address
        ])){
            return true;
        }else{
            return false;
        }
    }

    public static function chargeEth($address){

        $data = [
            'appid' => self::$appId,
            'method' => 'transfer',
            'name' => 'ETH',
            'from' => self::$eth_address,
            'to' => $address,
            'amount' => self::$charge_fee
        ];
        $data['sign'] = self::getSign($data);
        $http_client = new Client();
        $response = $http_client->post(self::$url, [
            'form_params' => $data
        ]);
        $result = json_decode($response->getBody()->getContents());
        if ($result->code != 0) {
            return false;
        }else{
            return true;
        }
    }

    public static function chargeBit($address){
        $data = [
            'appid' => self::$appId,
            'method' => 'transfer',
            'name' => 'BTC',
            'from' => self::$bit_address,
            'to' => $address,
            'amount' => self::$charge_fee_btc
        ];
        $data['sign'] = self::getSign($data);
        $http_client = new Client();
        $response = $http_client->post(self::$url, [
            'form_params' => $data
        ]);
        $result = json_decode($response->getBody()->getContents());
        if ($result->code != 0) {
            return false;
        }else{
            return true;
        }
    }

    public static function withdraw($address,$amount = null){
        $wallet = UsersWallet::join('currency','currency','=','currency.id')->where('address',$address)->orWhere('address_2',$address)->first();
        if($wallet->currency == 3){
            if($address == $wallet->address){
                $wallet->name = $wallet->name.'_ERC20';
                $to = config('app.usdt_erc20');
                $is_erc = true;
            }else{
                $wallet->name = $wallet->name.'_OMNI';
                $to = config('app.usdt_omni');
                $is_omni = true;
            }

        }

        $balance = self::getBalance($address);
        $fee = 0;
        if($wallet->currency == 3){
            $amount = $balance->USDT;
            if($amount<=0){
                echo sprintf('地址%s余额为0 不提现',$address).PHP_EOL;
                return true;
            }
            $compare = false;
            if(isset($is_erc)){
                $fee = self::$charge_fee;
                $remain_balance = $balance->ETH;
                $compare = $remain_balance>=self::$charge_fee?true:false;
            }
            if(isset($is_omni)){
                $fee = self::$charge_fee_btc;
                $remain_balance = $balance->BTC;
                $compare = $remain_balance>=self::$charge_fee_btc?true:false;
            }

            if($compare){

            }else{
                //充钱 然后调用队列十分钟后再提现
                if(!self::isInChargeQueue($address)){
                    echo sprintf('充值手续费地址%s ',$address).PHP_EOL;
                    $res = isset($is_erc)?self::chargeEth($address):self::chargeBit($address);
                    $res && self::addChargingQueue($address);
                }
                WalletLogic::addWithdrawQueue($address);
                //如果queue 设置的是同步  则不会延迟
//                ZTPayAddressWithdraw::dispatch($address)->delay(Carbon::now()->addMinutes(10));
                return false;
            }
        }else{
            $amount = $balance;
        }


         if ($wallet->currency == 1) {
             $to = config('app.btc');
         } elseif ($wallet->currency == 3) {
             $to = $to;
         } elseif ($wallet->currency == 2) {
             $to = config('app.eth');
             $fee = self::$charge_fee;
         } else{
             throw new \Exception('官方账户不存在'.$wallet->name);
         }
         if($amount<=0){
             echo sprintf('地址%s余额为0 不提现',$address).PHP_EOL;
//             log_exception('账户余额为0','');
             return true;
         }
        $data = [
            'appid' => self::$appId,
            'method' => 'transfer',
            'name' => $wallet->name,
            'from' => $address,
            'to' => $to,
            'amount' => $amount
        ];
         if($fee){
             $data['fee_amount'] = $fee;
         }
         $data['sign'] = self::getSign($data);
        $http_client = new Client();
        $response = $http_client->post(self::$url, [
            'form_params' => $data
        ]);
        $result = json_decode($response->getBody()->getContents());
        if ($result->code != 0) {
            $res = json_encode([
                'result' => $result,
                'data' => $data,
            ]);
            // log_exception('转账失败:'.$result->message,$res);
            throw new \Exception('转账失败:'.$result->message);
        }else{
            return true;
        }
    }

    public static function getBalance($address ,$return_eth = false){
        //先获取余额
        $wallet = UsersWallet::join('currency','currency','=','currency.id')->where('address',$address)->orWhere('address_2',$address)->first();
        if($wallet->currency == 3){
            if($address == $wallet->address){
                $wallet->name = $wallet->name.'_ERC20';
            }else{
                $wallet->name = $wallet->name.'_OMNI';
            }
        }
        $data = [
            'appid' => self::$appId,
            'method' => 'get_balance',
            'name' => $wallet->name,
            'address' => $address
        ];
        $data['sign'] = self::getSign($data);
        $http_client = new Client();
        $response = $http_client->post(self::$url, [
            'form_params' => $data
        ]);
        $result = json_decode($response->getBody()->getContents());
        if ($result->code != 0) {
            throw new \Exception('请求余额错误'.$result->message);
        }else{
            if($wallet->currency == 3){
                return $result->data;
            }
            return $result->data->{$wallet->name};
        }
    }


    public static function getSign($data) {
        $signPars = "";
        ksort($data);
        foreach($data as $k => $v) {
            if("sign" != $k && "" != $v && $v!="0") {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars .= "key=" . self::$appSecret;
        return strtoupper(md5($signPars));
    }

    public static function addChargingQueue($address){
        $redis = \App\Service\RedisService::getInstance();
        $redis->zAdd('charge_address',['NX'],time(),$address);
    }

    public static function delChargingQueue($address){
        $redis = \App\Service\RedisService::getInstance();
        $redis->zRem('charge_address',$address);
    }

    public static function isInChargeQueue($address){
        $redis = \App\Service\RedisService::getInstance();
        $res = $redis->zRank('charge_address',$address);
        if($res === false) {
            return false;
        } else {
            return true;
        }
    }

    public static function addWithdrawQueue($address){
        $redis = \App\Service\RedisService::getInstance();
        $redis->zAdd('withdraw_address',['NX'],time(),$address);
    }

    public static function delWithdrawQueue($address){
        $redis = \App\Service\RedisService::getInstance();
        $redis->zRem('withdraw_address',$address);
    }

    public static function isPro(){
        $redis = \App\Service\RedisService::getInstance();
       
        $res = $redis->get('production_1');
        if(!$res){
            return false;
        }else{
            return true;
        }
    }

    public static function isInQueue($address){
        $redis = \App\Service\RedisService::getInstance();
        $res = $redis->zRank('withdraw_address',$address);
        if($res === false) {
            return false;
        } else {
            return true;
        }
    }

    public static function getQueue(){
        if(self::isPro()){

        }else{
            return [];
        }
        $redis = \App\Service\RedisService::getInstance();
        return $redis->zRange('withdraw_address',0,-1);
    }

}
