<?php
class AutocarRandomFeedBridge extends BridgeAbstract {
    const NAME = 'Autocar India Random Feed';
    const URI = 'https://www.autocarindia.com/';
    const DESCRIPTION = 'Aggregates articles from all Autocar India road test and magazine RSS feeds';
    const MAINTAINER = 'Vipul';

    const PARAMETERS = [];

    private $feeds = [
        'https://www.autocarindia.com/rss/car-road-tests',
        'https://www.autocarindia.com/rss/bike-road-tests',
        'https://www.autocarindia.com/rss/magazine'
    ];

    public function collectData() {
        foreach ($this->feeds as $feedUrl) {
            $rss = @simplexml_load_file($feedUrl);
            if(!$rss) continue;

            foreach ($rss->channel->item as $item) {
                $content = (string) $item->description;
                $img = '';
                // Try to extract <img> from description (if any)
                if (preg_match('/<img[^>]+src="([^">]+)"/i', $content, $matches)) {
                    $img = $matches[1];
                }

                $this->items[] = [
                    'uri' => (string) $item->link,
                    'title' => (string) $item->title,
                    'timestamp' => strtotime((string) $item->pubDate),
                    'content' => $img ? "<img src='$img'><br>" . $content : $content,
                    'enclosures' => $img ? [$img] : [],
                ];
            }
        }
    }
}
