<?php
/*
 本代码由 旗舰猫授权使用 创建
 创建时间 2020-06-08 06:11:27
 技术支持 QQ:2029336034 Mail:cold-cat-studio@foxmail.com
 严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
*/

namespace App\Console\Commands;use App\AccountLog;use App\Currency;use App\Level;use App\Users;use App\UsersWallet;use App\Setting;use App\Utils\RPC;use Illuminate\Console\Command;use Illuminate\Support\Facades\DB;class Test extends Command{protected $signature="\x54\x65\x73\x74\x74\x65\x73\x74";protected $description="\xE6\xB5\x8B\xE8\xAF\x95";public function handle(){$this->comment("start");Users::rebate(357,357,3,100,1,2);$this->comment("end");}}
?>