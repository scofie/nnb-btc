<?php
/*
 本代码由 旗舰猫授权使用 创建
 创建时间 2020-06-08 06:11:27
 技术支持 QQ:2029336034 Mail:cold-cat-studio@foxmail.com
 严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
*/

namespace App\Console\Commands;use Illuminate\Console\Command;use App\DAO\FactprofitsDAO;use Illuminate\Support\Facades\DB;class ReturnProfit extends Command{protected $signature="\x72\x65\x74\x75\x72\x6E\x3A\x70\x72\x6F\x66\x69\x74";protected $description="\xE8\xBF\x94\xE8\xBF\x98\xE6\x9D\xA0\xE6\x9D\x86\xE4\xBA\xA4\xE6\x98\x93\xE4\xBA\x8F\xE6\x8D\x9F";public function __construct(){parent::__construct();}public function handle(){$N2w8E=new FactprofitsDAO();unset($N2wtI8F);$aaa=$N2w8E;unset($N2wtI8E);$all=DB::table('lever_transaction')->select("user_id")->groupBy('user_id')->get();foreach($all as $key=>$value){var_dump($value->user_id);var_dump($aaa::Profit_loss_release($value->user_id));}}}
?>