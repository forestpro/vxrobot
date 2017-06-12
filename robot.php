<?php
/**
 * Created by PhpStorm.
 * User: boren
 * Date: 17/4/1
 * Time: 下午2:38
 */
require_once __DIR__ . './../vendor/autoload.php';
require_once __DIR__ . '/Tools.php';
require_once __DIR__ . '/mongodb.php';

use Hanson\Vbot\Foundation\Vbot;
use Hanson\Vbot\Message\Entity\Text;
use Hanson\Vbot\Support\Console;


$path = __DIR__ . '/./../tmp/';
$robot = new Vbot([
    'tmp' => $path,
    'debug' => true
]);

$robotName = '伯仁';
$searchUrl = 'http://api.zuihuiyou.cn/api/search/comprehensive';


// 图灵自动回复
function reply($str)
{
    $reply_arr =  http()->post('http://www.tuling123.com/openapi/api', [
        'key' => '06ff1dd3fc264271bc95a85e629932d6',
        'info' => $str
    ], true);

    $result = $reply_arr['text'];

    if(array_key_exists('url',$reply_arr))
    {
        $result = $result.' '.$reply_arr['url'];
    }

    return $result;

}


$robot->server->setMessageHandler(function ($message) use ($path,$robotName,$searchUrl) {

    Console::log('message:'.json_encode($message));

    if($message->fromType === 'Heartbeats')
    {
        $date = date('H:i');

        Console::log('now:'.$date);

        //定时推送服务 和打卡
        $client = mongodb::getInstance();

        $filter = ['sendTime'=>$date];

        $autoMsgs = $client->Query('autoSendMessage',$filter,[]);

        foreach($autoMsgs as $msg)
        {
            if($msg->type ==='suggest')
            {
                Console::log('推荐:'.$msg->groupName.'->'.$msg->sendTime);

            }else if($msg->type ==='sign')
            {
                Console::log('打卡:'.$msg->groupName.'->'.$msg->sendTime);
            }

            $groupUser = group()->getGmap($msg->groupName);

            if($groupUser !== '' && trim($msg->content) !=='')
            {
                Text::send($groupUser,$msg->content);

            }else{
                Console::log($msg->groupName.' 群不存在！');
            }

        }

        //$username = group()->getGmap('土豪帮');

        //$userinfo = group()->getMembersByNickname($username,'今生无悔');

        //$userinfo1 = group()->getGroupsByNickname();

        //Console::log('今生无悔: '. $username .' ->'.json_encode($userinfo).' ->'.json_encode($userinfo1));

        $groupinfo =  $this->getObject("@@6071062b9c73e5850ab2df7c127979a8dcdd44075326dc156873f9a4c0c40d9a", 'UserName', false, false);

        Console::log("group info:".json_encode($groupinfo));

    }else{

            // 文字信息
            if ($message instanceof Text) {
                /** @var $message Text */

                $client = mongodb::getInstance();

                // 联系人自动回复
                if ($message->fromType === 'Contact')
                {
                    //Text::send($message->from['UserName'],"我是伯仁的小秘书，有什么可直拉说哦！");
                    Console::log('msg:'.$message->from['NickName'].' content:'.$message->content);

                    $chat = ['MsgId'=>$message->msg['MsgId'],'isAt'=>$message->isAt,'type'=>$message->fromType,'NickName'=>$message->from['NickName'],'content'=>$message->content,'createTime'=>$message->time];

                    $client->insert('wxchats',$chat);

                    if(str_contains($message->content, '游侠')) //游侠: 茶茶
                    {
                       // $message->content = str_replace('：', ':', $message->content);
                       // $cmds = explode(':', $message->content);
                        //取得游侠昵称
                        $travelNick = Tools::groupSearch($message->content);//trim($cmds[1]);


                        $searchParam = array('keyWord'=>$travelNick,'types'=>array('travelMaster'),'from'=>0,'size'=>5);

                        $result = Tools::sendPost($searchUrl,$searchParam);

                        $resp = $result['response'];

                        Console::log('res:'.$resp);

                        if($result['code'] == 200)
                        {

                            $travels = json_decode($resp);

                            foreach($travels->travelMaster->rows as $travel)
                            {
                                if($travel->_score >= 1)
                                {
                                    Console::log('travelName:'.$travel->_source->nickname);

                                    //Text::send($message->from['UserName'],$travel->country.'.'.$travel->nickname."  http://mall.zuihuiyou.com/yx_homepage/".$travel->_id);
                                    Text::send($message->from['UserName'],$travel->_source->country.'.'.$travel->_source->nickname."  http://mall.zuihuiyou.com/yx_homepage/".$travel->_id);
                                }
                            }

                        }else{
                            Console::log('httpError:'.$result['code']);
                        }

                    }else  if(str_contains($message->content, '服务')){

                        //取得服务标题
                        $serviceTitle = Tools::groupSearch($message->content);//trim($cmds[1]);

                        $searchParam = array('keyWord'=>$serviceTitle,'types'=>array('travelMasterService'),'from'=>0,'size'=>5);

                        $result = Tools::sendPost($searchUrl,$searchParam);

                        $resp = $result['response'];

                        Console::log('res:'.$resp);

                        if($result['code'] == 200)
                        {

                            $services = json_decode($resp);

                            foreach($services->travelMasterService->rows as $service)
                            {
                                if($service->_score >= 1)
                                {
                                    Console::log('title:'.$service->_source->title);

                                    //Text::send($message->from['UserName'],$travel->country.'.'.$travel->nickname."  http://mall.zuihuiyou.com/yx_homepage/".$travel->_id);
                                    Text::send($message->from['UserName'],$service->_source->country.'.'.$service->_source->providerNickname.'('.$service->_source->title.")  http://mall.zuihuiyou.com/servicedescription/{$service->_source->providerId},{$service->_source->serviceId}");
                                }
                            }

                        }else{
                            Console::log('httpError:'.$result['code']);
                        }


                    }else{

                        return reply($message->content);
                    }
                    // 群组@我回复
                } elseif ($message->fromType === 'Group') {

                    $sender = $message->sender["NickName"];

                    $chat = ['MsgId'=>$message->msg['MsgId'],'isAt'=>$message->isAt,'type'=>$message->fromType,'groupName'=>$message->from['NickName'],'senderNickName'=>$message->sender['NickName'],'content'=>$message->content,'createTime'=>$message->time];
                    $client->insert('wxchats',$chat);

                    Console::log('group msg:'.$message->from['NickName'].'->'.$sender.' content:'.$message->content);

                    if ($message->isAt)
                    {

                        if(str_contains($message->content, '拉人'))
                        {
                            $members = Tools::groupManager($message->content,$robotName);

                            $result = group()->addMember($message->from['UserName'], $members);

                            Console::log($result ? '拉人成功' : '拉人失败');

                        }else if(str_contains($message->content, '踢人'))
                        {
                            $members = Tools::groupManager($message->content,$robotName);

                            $result = group()->deleteMember($message->from['UserName'], $members);

                            Console::log($result ? '踢人成功' : '踢人失败');

                        }else if(str_contains($message->content, '玩乐') || str_contains($message->content, '服务')){

                            $serviceTitle = Tools::groupSearch($message->content);

                            $searchParam = array('keyWord'=>$serviceTitle,'types'=>array('travelMasterService'),'from'=>0,'size'=>5);

                            $result = Tools::sendPost($searchUrl,$searchParam);

                            $resp = $result['response'];

                            Console::log('res:'.$resp);

                            if($result['code'] == 200)
                            {

                                $services = json_decode($resp);

                                foreach($services->travelMasterService->rows as $service)
                                {
                                    if($service->_score >= 1)
                                    {
                                        Console::log('title:'.$service->_source->title);

                                        Text::send($message->msg['FromUserName'],$service->_source->country.'.'.$service->_source->providerNickname.'('.$service->_source->title.")  http://mall.zuihuiyou.com/servicedescription/{$service->_source->providerId},{$service->_source->serviceId}");
                                    }
                                }

                            }else{
                                Console::log('httpError:'.$result['code']);
                            }


                        }if(str_contains($message->content, '游侠')) //游侠: 茶茶
                        {
                            //取得游侠昵称
                            $travelNick  = Tools::groupSearch($message->content);//trim($cmds[1]);

                            $searchParam = array('keyWord'=>$travelNick,'types'=>array('travelMaster'),'from'=>0,'size'=>5);

                            $result = Tools::sendPost($searchUrl,$searchParam);

                            $resp = $result['response'];

                            Console::log('res:'.$resp);

                            if($result['code'] == 200)
                            {

                                $travels = json_decode($resp);

                                foreach($travels->travelMaster->rows as $travel)
                                {
                                    if($travel->_score >= 1)
                                    {
                                        Console::log('travelName:'.$travel->_source->nickname);

                                        //Text::send($message->from['UserName'],$travel->country.'.'.$travel->nickname."  http://mall.zuihuiyou.com/yx_homepage/".$travel->_id);
                                        Text::send($message->msg['FromUserName'],$travel->_source->country.'.'.$travel->_source->nickname."  http://mall.zuihuiyou.com/yx_homepage/".$travel->_id);
                                    }
                                }

                            }else{
                                Console::log('httpError:'.$result['code']);
                            }

                        }else{ //自动回复

                            return reply($message->content);
                            //发群消息
                            //Text::send($message->msg['FromUserName'],'@'.$sender.' 虽然还不能理解你说的话，但我觉得很有趣，你能再说一遍吗？[捂脸]');
                        }

                    }

                }else if($message->fromType === 'Self') //我自已发出的消息
                {
                    $chat = ['MsgId'=>$message->msg['MsgId'],'isAt'=>$message->isAt,'type'=>$message->fromType,'toNickName'=>$message->from['NickName'],'content'=>$message->content,'createTime'=>$message->time];
                    $client->insert('wxchats',$chat);
                }

            }
            // 群组变动
            if ($message instanceof GroupChange) {

                    /** @var $message GroupChange */
                    if ($message->action === 'ADD') {
                        Console::log('新人进群');
                        return '欢迎新人 ' . $message->nickname;
                    } elseif ($message->action === 'REMOVE') {
                        Console::log('群主踢人了');
                        return $message->content;
                    }
            }

            // 请求添加信息(作者的列子失效)
            /*if ($message instanceof RequestFriend) {

                $message->verifyUser($message::VIA);
                if ($message->info['Content'] === '上山打老虎') {
                    $message->verifyUser($message::VIA);
                }
            }*/

            //请求添加信息
            if($message->fromType === 'Special' && $message->msg["FromUserName"]==='fmessage' && $message->msg["MsgType"] ===37)
            {
                Console::log('有新好友：'.$message->info['Content'].' via:'.$message::VIA);
                //认证通过
                $message->verifyUser($message::VIA);
            }

            // 新增好友
            if ($message instanceof \Hanson\Vbot\Message\Entity\NewFriend) {
               Console::log('新加好友：' . $message->from['NickName']);
            }
    }

});


$robot->server->setExitHandler(function () {
    \Hanson\Vbot\Support\Console::log('其他设备登录');
});

$robot->server->setExceptionHandler(function () {
    \Hanson\Vbot\Support\Console::log('异常退出');
});

Console::log('启动中,请稍等...');

$robot->server->run();