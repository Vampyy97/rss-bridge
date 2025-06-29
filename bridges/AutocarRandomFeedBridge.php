<?php

class AutocarRandomFeedBridge extends BridgeAbstract {
    const NAME = 'Autocar India Random Feed';
    const URI = 'https://www.autocarindia.com/';
    const DESCRIPTION = 'Shows a randomised mix of feeds from car road tests, bike road tests, and magazine sections.';
    const MAINTAINER = 'ChatGPT';

    // RSS URLs
    private $rssUrls = [
        'https://www.autocarindia.com/rss/car-road-tests',
        'https://www.autocarindia.com/rss/bike-road-tests',
        'https://www.autocarindia.com/rss/magazine',
    ];

    // How many random feeds to show
    const RANDOM_FEED_COUNT = 15;

    public function collectData() {
        $allItems = [];

        foreach ($this->rssUrls as $url) {
            $rss = getSimpleHTMLDOM($url);
            if (!$rss) continue;

            foreach ($rss->find('item') as $entry) {
                $item = [];
                $item['title'] = $entry->find('title', 0)->plaintext;
                $item['uri'] = $entry->find('link', 0)->plaintext;
                $item['uid'] = $item['uri'];

                $desc = $entry->find('description', 0);
                $item['content'] = $desc ? $desc->innertext : '';

                $date = $entry->find('pubDate', 0);
                if ($date) $item['timestamp'] = strtotime($date->plaintext);

                $img = $entry->find('enclosure[url]', 0);
                if ($img) $item['enclosures'] = [$img->url];

                $allItems[] = $item;
            }
        }

        // Shuffle items to randomize
        shuffle($allItems);

        // Return only RANDOM_FEED_COUNT items
        $this->items = array_slice($allItems, 0, self::RANDOM_FEED_COUNT);
    }
}
