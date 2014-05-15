<?php

include(__DIR__ . '/webdata/init.inc.php');
include(__DIR__ . '/webdata/stdlibs/FeedWriter-master/FeedTypes.php');

///srv/data/
$qDate = date('Y-m-d');
if (isset($_GET['q'])) {
    $q = strtotime($_GET['q']);
    if ($q > 0) {
        $qDate = date('Y-m-d', $q);
    }
}

$cacheFile = '/srv/data/rss-' . $qDate . '.json';

if($qDate === date('Y-m-d') && file_exists($cacheFile) && (mktime() - filemtime($cacheFile) > 3600)) {
    unlink($cacheFile);
}

if (file_exists($cacheFile)) {
    $items = json_decode(file_get_contents($cacheFile), true);
} else {
    $items = array();

    $start = strtotime($qDate . ' 00:00:00');
    $end = strtotime($qDate . ' 23:59:59');

    $data = News::search("created_at >= {$start} and created_at <= {$end}");

    foreach ($data as $news) {
        foreach ($news->infos as $info) {
            $text = $info->title . $info->body;
            if (preg_match('#(柯文哲|柯P)#i', $text)) {
                $items[] = array(
                    'title' => $info->title,
                    'url' => $news->url,
                    'created' => $news->created_at,
                    'body' => $info->body,
                );
                continue;
            }
        }
    }

    file_put_contents($cacheFile, json_encode($items));
}

$kpaFeed = new RSS2FeedWriter();

$kpaFeed->setTitle('柯文哲國際後援會');
$kpaFeed->setLink('http://kpa.tw/');
$kpaFeed->setDescription('柯文哲國際後援會');

$kpaFeed->setChannelElement('language', 'zh-tw');
$kpaFeed->setChannelElement('pubDate', date(DATE_RSS, time()));

foreach ($items as $item) {
    $newItem = $kpaFeed->createNewItem();
    $newItem->setTitle($item['title']);
    $newItem->setLink($item['url']);
    $newItem->setDate($item['created']);
    $newItem->setDescription($item['body']);
    $kpaFeed->addItem($newItem);
}

$kpaFeed->generateFeed();