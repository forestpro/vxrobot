<?php
/**
 * Created by PhpStorm.
 * User: boren
 * Date: 17/4/1
 * Time: 下午2:38
 */
require_once __DIR__ . './../vendor/autoload.php';

use Hanson\Vbot\Foundation\Vbot;
use Hanson\Vbot\Message\Entity\Text;
use Hanson\Vbot\Support\Console;
use Hanson\Vbot\Core\Tools;


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
    return http()->post('http://www.tuling123.com/openapi/api', [
        'key' => '1dce02aef026258eff69635a06b0ab7d',
        'info' => $str
    ], true)['text'];

}


$robot->server->setMessageHandler(function ($message) use ($path,$robotName,$searchUrl) {

    Console::log('message:'.json_encode($message));


    // 文字信息
    if ($message instanceof Text) {
        /** @var $message Text */


        // 联系人自动回复
        if ($message->fromType === 'Contact')
        {
            //Text::send($message->from['UserName'],"我是伯仁的小秘书，有什么可直拉说哦！");
            Console::log('msg:'.$message->from['NickName'].' content:'.$message->content);

            if(str_contains($message->content, '游侠')) //游侠: 茶茶
            {
                $message->content = str_replace('：', ':', $message->content);
                $cmds = explode(':', $message->content);
                //取得游侠昵称
                $travelNick = trim($cmds[1]);


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

                $message->content = str_replace('：', ':', $message->content);
                $cmds = explode(':', $message->content);
                //取得游侠昵称
                $serviceTitle = trim($cmds[1]);

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


            }
            // 群组@我回复
        } elseif ($message->fromType === 'Group') {

            $sender = $message->sender["NickName"];

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

                }else{

                    return reply($message->content);
                    //发群消息
                    //Text::send($message->msg['FromUserName'],'@'.$sender.' 虽然还不能理解你说的话，但我觉得很有趣，你能再说一遍吗？[捂脸]');
                }

            }
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