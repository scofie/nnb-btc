<?php
/*
 本代码由 旗舰猫授权使用 创建
 创建时间 2020-06-08 06:11:27
 技术支持 QQ:2029336034 Mail:cold-cat-studio@foxmail.com
 严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
*/

namespace App\Console\Commands;use App\Utils\RPC;use App\Users;use App\UsersWallet;use App\HuobiSymbol;use Illuminate\Console\Command;use GuzzleHttp\Client;class UpdateHuobiSymbol extends Command{protected $signature="\x75\x70\x64\x61\x74\x65\x5F\x48\x75\x6F\x62\x69\x5F\x53\x79\x6D\x62\x6F\x6C";protected $description="\xE6\x9B\xB4\xE6\x96\xB0\xE7\x81\xAB\xE5\xB8\x81\xE4\xBA\xA4\xE6\x98\x93\xE5\xAF\xB9";public function handle(){$this->comment("start1");unset($N2wtI8E);$url='api.huobi.br.com/v1/common/symbols';$N2w8E=new Client();unset($N2wtI8F);$cli=$N2w8E;unset($N2wtI8E);$content=$cli->get($url)->getBody()->getContents();unset($N2wtI8E);$content=json_decode($content,true);HuobiSymbol::getSymbolsData($content['data']);$this->comment("end");}}
?>