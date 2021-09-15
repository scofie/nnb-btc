<?php


namespace App\Jobs;


use App\CurrencyMatch;
use App\Logic\CoinTradeLogic;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CoinTradeHandel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params)
    {
        $this->params = $params;
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        extract($this->params);
        //价格大于0才做更新处理
        //处理买未成交的且符合价格的
        //处理卖未成交且符合价格的
        $match = CurrencyMatch::where([
            'legal_id' => $legal_id,
            'currency_id' => $currency_id,
            'open_coin_trade' => 1
        ])->first();
        if(!$match){
            return;
        }
        if (bc_comp($now_price, 0) > 0) {
            CoinTradeLogic::matchBuyTrade($currency_id,$legal_id,$now_price);
            CoinTradeLogic::matchSellTrade($currency_id,$legal_id,$now_price);
//            LeverTransaction::newPrice($legal_id, $currency_id, $now_price);
        } else {
        }
    }
}
