<?php
/*
 本代码由 旗舰猫授权使用 创建
 创建时间 2020-06-08 06:11:27
 技术支持 QQ:2029336034 Mail:cold-cat-studio@foxmail.com
 严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
*/

namespace App\Console\Commands;use Illuminate\Console\Command;use App\UsersWallet;use App\Jobs\UpdateBalance as UpdateBalanceJob;class UpdateBalance extends Command{protected $signature="\x75\x70\x64\x61\x74\x65\x5F\x62\x61\x6C\x61\x6E\x63\x65";protected $description="\xE6\x9B\xB4\xE6\x96\xB0\xE7\x94\xA8\xE6\x88\xB7\xE4\xBD\x99\xE9\xA2\x9D";public function handle(){$this->comment("开始执行");UsersWallet::chunk(100,function($wallets){$wallets->each(function($item,$key){UpdateBalanceJob::dispatch($item)->onQueue('update:block:balance');});});$this->comment("执行完成");}}
?>