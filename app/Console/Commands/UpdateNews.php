<?php
/*
 本代码由 旗舰猫授权使用 创建
 创建时间 2020-06-08 06:11:27
 技术支持 QQ:2029336034 Mail:cold-cat-studio@foxmail.com
 严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
*/

namespace App\Console\Commands;use App\News;use App\UserReal;use Illuminate\Console\Command;class UpdateNews extends Command{protected $signature="\x75\x70\x64\x61\x74\x65\x5F\x6E\x65\x77\x73";protected $description="\xE6\x9B\xB4\xE6\x96\xB0\xE9\xA1\xB9\xE7\x9B\xAE\xE7\x9A\x84\xE6\x96\xB0\xE9\x97\xBB";public function __construct(){parent::__construct();}protected $searches=["\x63\x66\x6D\x63\x6F\x69\x6E"=>"\x74\x6F\x65\x78",];public function handle(){unset($N2wtI8E);$news_list=News::get();foreach($news_list as $news){foreach($this->searches as $k=>$v){unset($N2wtI8E);$news->content=str_replace($k,$v,$news->content);unset($N2wtI8E);$news->title=str_replace($k,$v,$news->title);unset($N2wtI8E);$news->keyword=str_replace($k,$v,$news->keyword);unset($N2wtI8E);$news->abstract=str_replace($k,$v,$news->abstract);unset($N2wtI8E);$news->thumbnail=str_replace($k,$v,$news->thumbnail);unset($N2wtI8E);$news->cover=str_replace($k,$v,$news->cover);}$news->save();}}}
?>