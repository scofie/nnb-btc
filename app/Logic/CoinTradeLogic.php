<?php


namespace App\Logic;


use App\AccountLog;
use App\CoinTrade;
use App\CurrencyQuotation;
use App\Setting;
use App\UsersWallet;
use Illuminate\Support\Facades\DB;

class CoinTradeLogic
{
    public static function userSellCoin($userId,$sellCurrencyId,$wantCurrencyId,$amount,$price){
        //第一步  找出钱包
        $wallet = UsersWallet::where("user_id", $userId)
            ->where("currency", $sellCurrencyId)
            ->lockForUpdate()
            ->first();

        //锁钱包该币种数量
        //获取当前价格
        $qut = CurrencyQuotation::getInstance($wantCurrencyId,$sellCurrencyId);
        DB::beginTransaction();
        try{
//            $price = bc_mul($amount,$price,8);
            $result = change_wallet_balance($wallet,2, -$amount, AccountLog::COIN_TRADE_FROZEN, '币币交易下单，资金冻结');
            if ($result !== true) {
                throw new \Exception($result);
            }

            change_wallet_balance(
                $wallet,
                2,
                $amount,
                AccountLog::COIN_TRADE_FROZEN,
                '币币交易下单，冻结资金增加',
                true,
                0,
                0,
                serialize([])
            );
            //生成
            CoinTrade::newTrade($userId,CoinTrade::TRADE_TYPE_SELL,$sellCurrencyId,$wantCurrencyId,$amount,$qut->now_price,$price);
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }


    }

    public static function userBuyCoint($userId,$buyCurrencyId,$payCurrencyId,$amount,$price){
        //第一步  找出钱包
        $wallet = UsersWallet::where("user_id", $userId)
            ->where("currency", $payCurrencyId)
            ->lockForUpdate()
            ->first();
        //锁钱包该币种数量
        $qut = CurrencyQuotation::getInstance($payCurrencyId,$buyCurrencyId);
        $costPrice = bc_mul($price,$amount);
        DB::beginTransaction();
        try{
//            $price = bc_mul($amount,$price,8);
            $result = change_wallet_balance($wallet,2, -$costPrice, AccountLog::COIN_TRADE_FROZEN, '币币交易下单，资金冻结');
            if ($result !== true) {
                throw new \Exception($result);
            }

            change_wallet_balance(
                $wallet,
                2,
                $costPrice,
                AccountLog::COIN_TRADE_FROZEN,
                '币币交易下单，冻结资金增加',
                true,
                0,
                0,
                serialize([])
            );
            //生成
            CoinTrade::newTrade($userId,CoinTrade::TRADE_TYPE_BUY,$buyCurrencyId,$payCurrencyId,$amount,$qut->now_price,$price);
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }
        // 生成订单
    }

    public static function matchSellTrade($currencyId,$legalId,$nowPrice){
        $tradeList = CoinTrade::where([
            'currency_id' => $currencyId,
            'legal_id' => $legalId,
            'status' => 1,
            'type' => 2
        ])->where('target_price','<=',$nowPrice)->limit(1)->get();

        foreach($tradeList as $trade){
            DB::beginTransaction();
            try{

                $wallet = UsersWallet::where("user_id", $trade->u_id)
                    ->where("currency", $trade->legal_id)
                    ->lockForUpdate()
                    ->first();

                $targetWallet = UsersWallet::where("user_id", $trade->u_id)
                    ->where("currency", $trade->currency_id)
                    ->lockForUpdate()
                    ->first();
                if(!$wallet || !$targetWallet){
                    throw new \Exception(sprintf('订单%s找不到用户钱包',$trade->id));
                }

                $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);

                change_wallet_balance(
                    $targetWallet,
                    2,
                    -$trade->trade_amount,
                    AccountLog::COIN_TRADE_FROZEN,
                    '币币交易冻结减少',
                    true,
                    0,
                    0,
                    serialize([])
                );
                $chargeFee = $trade->charge_fee;
                $chargeFee = bc_sub(1,$chargeFee,8);
                //手续费
                $costPrice = bc_mul($costPrice,$chargeFee,8);
                change_wallet_balance($wallet,
                    2,
                    $costPrice,
                    AccountLog::COIN_TRADE,
                    '币币交易成功');
                $trade->status = 2;
                $trade->save();
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                echo $e->getMessage();
                continue;
            }
        }
    }

    public static function matchBuyTrade($currencyId,$legalId,$nowPrice){
        $tradeList = CoinTrade::where([
            'currency_id' => $currencyId,
            'legal_id' => $legalId,
            'status' => 1,
            'type' => 1
        ])->where('target_price','>=',$nowPrice)->limit(1)->get();


        foreach($tradeList as $trade){

            DB::beginTransaction();
            try{
                //1 扣掉冻结资金
                $wallet = UsersWallet::where("user_id", $trade->u_id)
                    ->where("currency", $trade->legal_id)
                    ->lockForUpdate()
                    ->first();

                $targetWallet = UsersWallet::where("user_id", $trade->u_id)
                    ->where("currency", $trade->currency_id)
                    ->lockForUpdate()
                    ->first();
                if(!$wallet || !$targetWallet){
                    throw new \Exception(sprintf('订单%s找不到用户钱包',$trade->id));
                }
                $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);

                change_wallet_balance(
                    $wallet,
                    2,
                    -$costPrice,
                    AccountLog::COIN_TRADE_FROZEN,
                    '币币交易成功，冻结资金减少',
                    true,
                    0,
                    0,
                    serialize([])
                );

                //手续费
                $chargeFee = $trade->charge_fee;
                $chargeFee = bc_sub(1,$chargeFee,8);
                $amount = bc_mul($trade->trade_amount,$chargeFee,8);
                change_wallet_balance($targetWallet,
                    2,
                    $amount,
                    AccountLog::COIN_TRADE,
                    '币币交易成功');
                $trade->status = 2;
                $trade->save();
                DB::commit();
            }catch (\Exception $e){
                DB::rollBack();
                echo $e->getMessage();
                continue;
            }
        }
    }


    public static function forceMatchTrade($tradeId){
        $trade = CoinTrade::find($tradeId);
        if($trade->status != 1){
            throw new \Exception('状态异常');
        }
        DB::beginTransaction();
        try{
            switch ($trade->type){
                case 1:
                    //1 扣掉冻结资金
                    $wallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->legal_id)
                        ->lockForUpdate()
                        ->first();

                    $targetWallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->currency_id)
                        ->lockForUpdate()
                        ->first();
                    if(!$wallet || !$targetWallet){
                        throw new \Exception(sprintf('订单%s找不到用户钱包',$trade->id));
                    }
                    $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);

                    change_wallet_balance(
                        $wallet,
                        2,
                        -$costPrice,
                        AccountLog::COIN_TRADE_FROZEN,
                        '币币交易成功，冻结资金减少',
                        true,
                        0,
                        0,
                        serialize([])
                    );

                    //手续费
                    $chargeFee = $trade->charge_fee;
                    $chargeFee = bc_sub(1,$chargeFee,8);
                    $amount = bc_mul($trade->trade_amount,$chargeFee,8);
                    change_wallet_balance($targetWallet,
                        2,
                        $amount,
                        AccountLog::COIN_TRADE,
                        '币币交易成功');
                    $trade->status = 2;
                    $trade->save();
                    break;
                case  2:

                    $wallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->legal_id)
                        ->lockForUpdate()
                        ->first();

                    $targetWallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->currency_id)
                        ->lockForUpdate()
                        ->first();
                    if(!$wallet || !$targetWallet){
                        throw new \Exception(sprintf('订单%s找不到用户钱包',$trade->id));
                    }

                    $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);

                    change_wallet_balance(
                        $targetWallet,
                        2,
                        -$trade->trade_amount,
                        AccountLog::COIN_TRADE_FROZEN,
                        '币币交易冻结减少',
                        true,
                        0,
                        0,
                        serialize([])
                    );
                    $chargeFee = $trade->charge_fee;
                    $chargeFee = bc_sub(1,$chargeFee,8);
                    //手续费
                    $costPrice = bc_mul($costPrice,$chargeFee,8);
                    change_wallet_balance($wallet,
                        2,
                        $costPrice,
                        AccountLog::COIN_TRADE,
                        '币币交易成功');
                    $trade->status = 2;
                    $trade->save();
                    break;
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            throw $e;
        }

    }

    public static function cancelTrade($id){
        $trade = CoinTrade::find($id);
        if(!$trade)
            throw new \Exception('找不到订单');
        if($trade->status != 1)
            throw new \Exception('订单状态异常');
        switch ($trade->type){
            case 1:
                    DB::beginTransaction();
                    try{
                        //解除冻结  还钱
                        $wallet = UsersWallet::where("user_id", $trade->u_id)
                            ->where("currency", $trade->legal_id)
                            ->lockForUpdate()
                            ->first();
                        $costPrice = bc_mul($trade->target_price,$trade->trade_amount,8);
                        $result = change_wallet_balance($wallet,
                            2,
                            $costPrice,
                            AccountLog::COIN_TRADE_FROZEN,
                            '取消币币交易，资金返还');
                        if ($result !== true) {
                            throw new \Exception($result);
                        }
                        $result = change_wallet_balance(
                            $wallet,
                            2,
                            -$costPrice,
                            AccountLog::COIN_TRADE_FROZEN,
                            '取消币币交易，退换冻结资金',
                            true,
                            0,
                            0,
                            serialize([])
                        );
                        if ($result !== true) {
                            throw new \Exception($result);
                        }
                        $trade ->status = 3;
                        $trade->save();
                        DB::commit();
                    }catch (\Exception $e){
                        DB::rollBack();
                        throw $e;
                    }
                break;
            case 2:
                DB::beginTransaction();
                try{
                    //解除冻结  还钱
                    $wallet = UsersWallet::where("user_id", $trade->u_id)
                        ->where("currency", $trade->currency_id)
                        ->lockForUpdate()
                        ->first();
                    $result = change_wallet_balance($wallet,2, $trade->trade_amount, AccountLog::COIN_TRADE_FROZEN, '取消币币交易');
                    if ($result !== true) {
                        throw new \Exception($result);
                    }

                    $result = change_wallet_balance(
                        $wallet,
                        2,
                        -$trade->trade_amount,
                        AccountLog::COIN_TRADE_FROZEN,
                        '取消币币交易,退换冻结资金',
                        true,
                        0,
                        0,
                        serialize([])
                    );
                    if ($result !== true) {
                        throw new \Exception($result);
                    }
                    $trade ->status = 3;
                    $trade->save();
                    DB::commit();
                }catch (\Exception $e){
                    DB::rollBack();
                    throw $e;
                }
                break;
            default:
                throw new \Exception('类型有误');
        }
        return true;
    }
}
