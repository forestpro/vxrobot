<?php
/**
 * Created by PhpStorm.
 * User: boren
 * Date: 17/4/5
 * Time: 下午2:22
 */
//require_once __DIR__ . '/mongodb.php';
require_once __DIR__ . '/simple_html_dom.php';


//$client = mongodb::getInstance();
//$client->insert('test',array());
/*$manager = new MongoDB\Driver\Manager('mongodb://zuihuiyou_user:zuihuiyou_2014@192.168.1.17:30000/user');
$bulk = new MongoDB\Driver\BulkWrite;
$bulk->insert(['x' => 1, 'name'=>'菜鸟教程', 'url' => 'http://www.runoob.com']);
$manager->executeBulkWrite('user.xx', $bulk);*/
//https://www.tripsters.cn/adminqps/index.php?m=home&c=post&a=questionlist&data=&company=&country=%E6%B3%B0%E5%9B%BD&unanswer=2&p=1
//$html = file_get_contents('https://www.tripsters.cn/adminqps/index.php?m=home&c=post&a=questionlist&data=&company=&country=%E6%B3%B0%E5%9B%BD&unanswer=2&p=1');

//第一次抓取共3466页
$filePath = '/Users/boren/Downloads/';
$fileIndex = 1;
$pageIndex = 1;

for($page =1; $page<=3466; $page++)
{
    if($pageIndex ==1 )
    {
        $handle = fopen($filePath.$fileIndex.'.txt','a+');
        $fileIndex ++;
    }

    echo 'file:'.($fileIndex-1).'.txt  p:'.$page.' pi:'.$pageIndex.PHP_EOL;

    $dom = file_get_html('https://www.tripsters.cn/adminqps/index.php?m=home&c=post&a=questionlist&data=&company=&country=%E6%B3%B0%E5%9B%BD&unanswer=2&p='.$page);

    $tbody = $dom->find('table tbody',0);

    foreach($dom->find('table tbody tr') as $tr)
    {
        $i = 0;
        $line = '';
        $area = '';
        $div = $tr->find('td div',0);

        if(empty($div))
        {
            foreach($tr->find('td') as $td)
            {
                $text  = trim($td->plaintext);

                if($i == 1)
                {
                    $area = $text;
                }
                if($i == 2)
                {
                    $area = $area.'.'.$text;
                }

                if($i == 0)
                {
                    $line = $text;
                }

                if($i == 3)
                {
                    $line = $line.'	'.$text;
                }


                $i ++;
            }

            $line = $area.$line.PHP_EOL;
            //echo $line;

            if(trim($line) !=='')
            {
                fwrite($handle,$line);
            }


        }

    }

    $pageIndex++;

    if($pageIndex == 600)
    {
        $pageIndex = 1;
        fclose($handle);
    }
}


//echo $caption->plaintext;