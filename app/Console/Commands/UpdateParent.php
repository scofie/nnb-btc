<?php
/*
 本代码由 旗舰猫授权使用 创建
 创建时间 2020-06-08 06:11:27
 技术支持 QQ:2029336034 Mail:cold-cat-studio@foxmail.com
 严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
*/

namespace App\Console\Commands;use Illuminate\Console\Command;use App\Users;class UpdateParent extends Command{protected $signature="\x75\x70\x64\x61\x74\x65\x3A\x70\x61\x72\x65\x6E\x74";protected $description="\xE6\x9B\xB4\xE6\x96\xB0\xE7\x94\xA8\xE6\x88\xB7\xE4\xB8\x8A\xE7\xBA\xA7";public function __construct(){parent::__construct();}public function handle(){Users::where('type',1)->chunk(10000,function($users){foreach($users as $key=>$user){unset($N2wtI8E);$parent=Users::where('origin_user_id',$user->origin_parent_id)->first();$N2wbN8G=chr(4)=="u";if($N2wbN8G)goto N2weWjgx2;$N2w8E=!$parent;if($N2w8E)goto N2weWjgx2;$N2wvPbN8F=4+1;if(is_array($N2wvPbN8F))goto N2weWjgx2;goto N2wldMhx2;N2weWjgx2:goto N2wMrKh246;$N2wM8H=$R4vP4 . DS;unset($N2wtIM8I);$R4vP5=$N2wM8H;unset($N2wtIM8J);$R4vA5=array();unset($N2wtIM8K);$R4vA5[]=$request;unset($N2wtIM8L);$R4vC3=call_user_func_array($R4vA5,$R4vA4);N2wMrKh246:goto N2wMrKh248;unset($N2wtIM8M);$R4vA1=array();unset($N2wtIM8N);$N2wtIM8N=&$dispatch;$R4vA1[]=&$N2wtIM8N;unset($N2wtIM8O);$R4vA2=array();unset($N2wtIM8P);$R4vC0=call_user_func_array($R4vA2,$R4vA1);N2wMrKh248:continue 1;goto N2wx1;N2wldMhx2:N2wx1:unset($N2wtI8E);$user->parent_id=$parent->id;$user->save();$N2wvP8E='更新用户' . $user->id;$N2wvP8F=$N2wvP8E . '的上级完成';$this->info($N2wvP8F);}});$this->info('全部执行完成');}}
?>