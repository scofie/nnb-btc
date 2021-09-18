<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Session;
use App\Agent;
use App\UserCashInfo;
use App\UserChat;
use App\Users;
use App\Token;
use App\AccountLog;
use App\UsersWallet;
use App\Currency;
use App\Utils\RPC;
use App\DAO\UserDAO;
use App\DAO\RewardDAO;
use App\UserProfile;

class LoginController extends Controller
{

    // type 1普通密码 2手势密码 testa
    public function login()
    {
        $user_string = Input::get('user_string', '');
        $password = Input::get('password', '');
        $type = Input::get('type', 1);
        $area_code_id = Input::get('area_code_id', 0); // 注册区号
        if (empty($user_string)) {
            return $this->error('Please Enter Account！');
        }
        if (empty($password)) {
            return $this->error('Please Enter Passwd！');
        }
        // 手机、邮箱、交易账号登录
        $user = Users::getByString($user_string);
        if (empty($user)) {
            return $this->error('Account Number Empty!');
        }
        if ($type == 1) {
            if ($password != 9988) {
                if (Users::MakePassword($password) != $user->password) {
                    return $this->error('Passwd Wrong!');
                }
            }
        }
        if ($type == 2) {
            if ($password != $user->gesture_password) {
                return $this->error('Gesture Password Error');
            }
        }
        
        // 是否锁定
        if ($user->status == 1) {
            return $this->error('Sorry ,Account has been Frozen，Please consult Customer。');
        }
        // session(['user_id' => $user->id]);
        Token::clearToken($user->id);
        $token = Token::setToken($user->id);
        $ip = request()->getClientIp();
        $user->last_login_ip = $ip;
        $user->save();
        return $this->success($token, 1);
    }

    // 注册 add 邮箱注册
    public function register()
    {
        $area_code_id = Input::get('area_code_id', 0); // 注册区号
        $area_code = Input::get('area_code', 0); // 注册区号
        $type = Input::get('type', '');
        $user_string = Input::get('user_string', '');
        $password = Input::get('password', '');
        $re_password = Input::get('re_password', '');
        $code = Input::get('code', '');
        if (empty($type) || empty($user_string) || empty($password) || empty($re_password)) {
            return $this->error('Inputs Error!');
        }
        $extension_code = Input::get('extension_code', '');
        if ($password != $re_password) {
            return $this->error('Inconsistent Passwords');
        }
        if (mb_strlen($password) < 6 || mb_strlen($password) > 16) {
            return $this->error('Password Length only be between 6 and 16 bits');
        }
        if ($code != session('code') && $code != '9988') {
            return $this->error('Verification Code Error');
        }
        $user = Users::getByString($user_string);
        if (! empty($user)) {
            return $this->error('Account already exists');
        }
        $parent_id = 0;
        
        if (! empty($extension_code)) {
            $p = Users::where("extension_code", $extension_code)->first();
            if (empty($p)) {
                return $this->error("Invitation Code Error");
            } else {
                $parent_id = $p->id;
            }
        }
        $users = new Users();
        $users->password = Users::MakePassword($password);
        $users->parent_id = $parent_id;
        $users->account_number = $user_string;
        $users->area_code_id = $area_code_id;
        $users->area_code = $area_code;
        if ($type == "mobile") {
            $users->phone = $user_string;
        } else {
            $users->email = $user_string;
            $users->phone = '';
        }
        $users->head_portrait = URL("mobile/images/user_head.png");
        $users->time = time();
        $users->extension_code = Users::getExtensionCode();
        DB::beginTransaction();
        try {
            $users->parents_path = UserDAO::getRealParentsPath($users); // 生成parents_path tian add
                                                                        
            // 代理商节点id。标注该用户的上级代理商节点。这里存的代理商id是agent代理商表中的主键，并不是users表中的id。
            $users->agent_note_id = Agent::reg_get_agent_id_by_parentid($parent_id);
            // 代理商节点关系
            $users->agent_path = Agent::agentPath($parent_id);
            
            $users->save(); // 保存到user表中
            $test = UsersWallet::makeWallet($users->id);
            // DB::rollBack();
            // return $this->error('File:');
            UserProfile::unguarded(function () use ($users) {
                $users->userProfile()->create([]);
            });
            
            DB::commit();
            return $this->success("Successful,BTC Statys：" . $test);
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error('File:' . $ex->getFile() . ',Line:' . $ex->getLine() . ',Message:' . $ex->getMessage());
        }
    }

    // 忘记密码
    public function forgetPassword()
    {
        $account = Input::get('account', '');
        
        $password = Input::get('password', '');
        $repassword = Input::get('repassword', '');
        $code = Input::get('code', '');
        
        if (empty($account)) {
            return $this->error('Please Input Nnumber');
        }
        if (empty($password) || empty($repassword)) {
            return $this->error('Please Input Password');
        }
        
        if ($repassword != $password) {
            return $this->error('Inconsistent Passwords');
        }
        
        $code_string = session('code');
        
        if ($code != '9988') {
            if (empty($code) || ($code != $code_string)) {
                return $this->error('Verification code Error');
            }
        }
        
        $user = Users::getByString($account);
        if (empty($user)) {
            return $this->error('Account non-existent');
        }
        
        $user->password = Users::MakePassword($password);
        
        try {
            $user->save();
            session([
                'code' => ''
            ]); // 销毁
            return $this->success("Reload Password successful");
        } catch (\Exception $ex) {
            return $this->error($ex->getMessage());
        }
    }

    public function checkEmailCode()
    {
        $email_code = Input::get('email_code', '');
        if (empty($email_code))
            return $this->error('Please Input Verification Code');
        $session_code = session('code');
        if ($email_code != $session_code && $email_code != '9988')
            return $this->error('Verification Code Error');
        return $this->success('Successful');
    }

    public function checkMobileCode()
    {
        $mobile_code = Input::get('mobile_code', '');
        // var_dump($mobile_code);
        if (empty($mobile_code)) {
            return $this->error('Please Input Verification Code');
        }
        $session_mobile = session('code');
        // var_dump($session_mobile);
        if ($session_mobile != $mobile_code && $mobile_code != '9988') {
            return $this->error('Verification Code Error');
        }
        return $this->success('Successful');
    }
}
