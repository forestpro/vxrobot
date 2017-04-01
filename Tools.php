<?php
/**
 * Created by PhpStorm.
 * User: boren
 * Date: 17/4/1
 * Time: 下午2:37
 */

class Tools
{
    /**
     * @param $content
     * 群管理拉人，踢人
     */
    public static function groupManager($content,$robotName)
    {
        $content = str_replace('：', ':', $content);
        $content = str_replace('，', ',', $content);

        $cmdIndex = strpos( $content,":");
        $content = substr($content,$cmdIndex+1);

        $nicknames = explode(',', $content);

        $members = [];
        foreach ($nicknames as $nickname)
        {
            if($robotName !== $nickname )
            {
                $members[] = contact()->getUsernameByNickname($nickname);
               // Console::log('昵称:'.$nickname);
            }
        }

        //Console::log('$members:'.json_encode($members));

        return $members;
    }

    /**
     *
     * @param $content
     * @return mixed|string
     */
    public static function groupSearch($content)
    {
        $content = str_replace('：', ':', $content);
        $content = str_replace('，', ',', $content);

        $cmdIndex = strpos( $content,":");
        $content = substr($content,$cmdIndex+1);

        return $content;
    }

    /**
     * 发post
     * @param $url
     * @param $data
     */
    public static function sendPost($url,$data)
    {
        /* $postdata = http_build_query($data);
         $options = array(
             'http' => array(
                 'method' => 'POST',
                 'header' => 'Content-type:application/json',
                 'content' => $postdata,
                 'timeout' => 60 // 超时时间（单位:s）
             )
         );
         $context = stream_context_create($options);
         $result = file_get_contents($url, false, $context);

         return $result;*/

        $jsonStr =json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($jsonStr)
            )
        );

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = array('code'=>$httpCode,'response'=>$response);

        return $result;
    }
}