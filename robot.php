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
    /*return http()->post('http://www.tuling123.com/openapi/api', [
        'key' => '06ff1dd3fc264271bc95a85e629932d6',//'1dce02aef026258eff69635a06b0ab7d',
        'info' => $str
    ], true)['text'];*/

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

        $content = '';

        //定时打卡
        if($date == '16:45')
        {
            $content = '早安啊亲们！大家都起床没？大家的小伙伴去哪儿微导游-美眉已经恭候多时啦，关于旅行的任何问题欢迎随时向我提问噢！昨天晚上没有哪位亲@我啊，昨晚睡觉好想梦到了呢！';
        }
        if($date == '16:46')
        {
            $content = 'Hello，我是去哪儿微导游-昊哥,给大家提供此次大家前往日本旅行帮助，此群为志同道合的旅友提供行程中交流的互动平台。大家如有目的地吃喝玩乐、门票交通等求推荐，或者任何行前准备、行中问题可以@去哪儿微导游-昊哥，我们会竭尽所能为您解答和推荐。';
        }

        if($date == '21:00')
        {
            $content = '大家晚安，妹妹工作了一天，现在要回窝休息了。非工作时间21：00-09：00之间各位亲有旅行问题可以互帮互助或者给妹妹留言️，紧急问题可以📞联系去哪儿24小时📞️热线10101234获得帮助，妹妹明天9：00会准时出窝和大家会和，不见不散~';
        }

        if($content !== '')
        {
            $groupUser = group()->getGmap('最会游火山部队_苏州');
            Text::send($groupUser,$content);
            Console::log('打卡:'.$content);
        }

        Console::log('now:'.$date);

        //定时推送服务
        $client = mongodb::getInstance();
        $filter = ['sendTime'=>$date];

        $suggests = $client->Query('suggest',$filter,[]);

        foreach($suggests as $suggest)
        {
            Console::log('推荐:'.$suggest->groupName.'->'.$suggest->sendTime);

            $groupUser = group()->getGmap($suggest->groupName);

            if($groupUser !== '')
            {
                Text::send($groupUser,$suggest->content);

            }else{
                Console::log($suggest->groupName.' 群不存在！');
            }


        }


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

                        //$message->content = str_replace('：', ':', $message->content);
                        //$cmds = explode(':', $message->content);
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