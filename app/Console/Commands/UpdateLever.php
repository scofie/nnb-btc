<?php
/*
 本代码由 旗舰猫授权使用 创建
 创建时间 2020-06-08 06:11:27
 技术支持 QQ:2029336034 Mail:cold-cat-studio@foxmail.com
 严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
*/

namespace App\Console\Commands;use Illuminate\Console\Command;use Illuminate\Support\Facades\DB;use Illuminate\Support\Facades\Log;use App\{LeverTransaction};use App\Jobs\LeverClose;class UpdateLever extends Command{protected $signature="\x72\x65\x6D\x6F\x76\x65\x5F\x74\x61\x73\x6B";protected $description="\xE7\xA7\xBB\xE9\x99\xA4\xE7\xA7\xAF\xE5\x8E\x8B\xE4\xBB\xBB\xE5\x8A\xA1";public function handle(){$this->comment("开始任务");\Illuminate\Support\Facades\Redis::del('queues:lever:update');$this->comment("结束任务");}}
?>