<?php


namespace App\Http\Controllers\Api;


use App\AccountLog;
use App\LegalDealSend;
use App\Logic\CoinTradeLogic;
use App\Logic\WalletLogic;
use App\MarketHour;
use App\Setting;
use App\Users;
use App\UsersWallet;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class NoticeController extends Controller
{
    public function walletNotify(Request $request){

        $param = $request->getContent();
        $param = json_decode($param,true);
        $data = $param['data'];
        $sign = $param['sign'];
        if(!$data || !$sign){
            var_dump($request->getContent());
            // log_exception('钱包回调请求数据异常',$param);
                return $this->error('');
        }
        $data['appid'] = WalletLogic::$appId;
        if(WalletLogic::getSign($data) != $sign){
            // log_exception('钱包签名异常',$param);
            return $this->error('钱包签名异常');
        }
        if($data['type'] == 2){

            if(WalletLogic::isInChargeQueue($data['to'])){
                WalletLogic::delChargingQueue($data['to']);
//                WalletLogic::withdraw($data['to']);
            }

            //充值转出成功后 删除转出队列
            WalletLogic::delWithdrawQueue($data['from']);

            echo 'success';exit;
        }
        $address = $data['to'];

        $amount = $data['amount'];
        //先查询是否有值
        $record = DB::table('ztpay_log')->where('unique_key',$data['hash'])->first();
        if(!$record){
            if(WalletLogic::isChangeAddress($address)){
                echo 'success';exit; // 过滤掉充值零钱的消息
            }
            $legal = UsersWallet::where('address',$address)->orWhere('address_2',$address)
                ->lockForUpdate()
                ->first();
            if(!$legal){
                // log_exception('找不到钱包',$param);
                return $this->error('');
            }
            DB::beginTransaction();
            try{

                change_wallet_balance(
                    $legal,
                    2,
                    $amount,
                    AccountLog::WALLET_CURRENCY_IN,
                    '充币记录',
                    false,
                    0,
                    0,
                    serialize([
                        'address_from' => $data['from'],
                        'address_to' => $data['to']
                    ]),
                    false,
                    true
                );
                DB::table('ztpay_log')->insert(['unique_key'=>$data['hash'],'body' => json_encode($param),'created_at'=>date("Y-m-d H:i:s")]);
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                throw $e;
            }
        }

//        $amount = bc_sub($data['amount'],$data['fee_amount'],8);

        try{
            WalletLogic::addWithdrawQueue($address);
        }catch (\Exception $e){
            throw $e;
        }
        echo 'success';

        exit;
        return $this->success('');
    }

    public function withdrawQueue(){
        // $redis = \App\Service\RedisService::getInstance();
       
        $address = WalletLogic::getQueue();
        // var_dump(WalletLogic::isPro());exit;
        foreach($address as $add){
            echo "提现 $add 开始".PHP_EOL;
            $res = WalletLogic::withdraw($add);
            if($res){
                echo "提现$add 完毕 移除出提现队列".PHP_EOL;
                WalletLogic::delWithdrawQueue($add);
            }
        }
    }

    public function test(){
        CoinTradeLogic::matchBuyTrade(1,3,9400);
        CoinTradeLogic::matchSellTrade(1,3,9400);
//
//        $address = [
//          '0x9e44d81bea892e02cca1e5aaab548209e42a0bad',
//            '0x332ccb6285a4365031f5b580fa310a2f4c0bf430',
//            '0x340fe906456241bb407fb1920766d22af20f0532',
//            '0xbd5191dbdc9eb50671086f01078ef0220ae4b2e0',
//            '0xc8eb9e888a36f1ca719f92144d796d68bfeb9b66',
//            '0xb146e8c69269dc2781b9401ac45843b4b183199e',
//            '0x338b2dbe9e283b114f418f034810ad69a2c33e0e',
//            '0x9e44d81bea892e02cca1e5aaab548209e42a0bad'
//        ];
//        foreach($address as $add){
//            $balance = WalletLogic::getBalance($add);
//
//        }

        exit;

        $data = [
            'appid' => WalletLogic::$appId,
            'method' => 'get_balance',
            'name' => 'ETH',
            'address' => '0x9e7468c1acb6f6c47e01660f0909cac286f6da1c'
        ];
        $data['sign'] = WalletLogic::getSign($data);
        $http_client = new Client();
        $response = $http_client->post('https://sapi.ztpay.org/api/v2', [
            'form_params' => $data
        ]);
        $result = json_decode($response->getBody()->getContents());
        return $this->success($result);
    }
}
