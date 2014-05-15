<?php

header('Content-Type: application/xml');

include(__DIR__ . '/webdata/init.inc.php');
include(__DIR__ . '/webdata/stdlibs/FeedWriter-master/FeedTypes.php');

$kpaFeed = new RSS2FeedWriter();

$kpaFeed->setTitle('柯文哲國際後援會');
$kpaFeed->setLink('http://kpa.tw/');
$kpaFeed->setDescription('柯文哲國際後援會');

$kpaFeed->setChannelElement('language', 'zh-tw');
$kpaFeed->setChannelElement('pubDate', date(DATE_RSS, time()));

$start = strtotime('-1 month');
$data = News::search("created_at >= {$start}");

foreach ($data as $news) {
    foreach ($news->infos as $info) {
        $text = $info->title . $info->body;
        if (preg_match('#(柯文哲|柯P)#i', $text)) {
            $newItem = $kpaFeed->createNewItem();
            $newItem->setTitle($info->title);
            $newItem->setLink($news->url);
            $newItem->setDate($news->created_at);
            $newItem->setDescription($info->body);
            $kpaFeed->addItem($newItem);
            continue;
        }
        
        $content = new StdClass;
        $content->title = $info->title;
        $content->body = $info->body;
        $content->time = $info->time;
        $bodys[] = $content;
    }
}

$kpaFeed->generateFeed();
