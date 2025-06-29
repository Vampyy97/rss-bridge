<?php

class AutocarRandomFeedBridge extends BridgeAbstract {
    const NAME = 'Autocar India Random Feed';
    const URI = 'https://www.autocarindia.com/';
    const DESCRIPTION = 'Aggregates random articles from multiple Autocar India RSS feeds with proper HTML content.';
    const MAINTAINER = 'ChatGPT-OpenAI';

    // List of RSS feed URLs
    private $feedUrls = [
        'https://www.autocarindia.com/rss/car-road-tests',
        'https://www.autocarindia.com/rss/bike-road-tests',
        'https://www.autocarindia.com/rss/magazine'
    ];

    const PARAMETERS = [];

    public function collectData() {
        $allItems = [];

        foreach ($this->feedUrls as $feedUrl) {
            $rss = getSimpleHTMLDOM($feedUrl);
            if (!$rss) continue;

            foreach ($rss->find('item') as $entry) {
                $item = [];
                $item['title'] = trim($entry->find('title', 0)->innertext);
                $item['uri'] = $entry->find('link', 0)->innertext;
                $item['uid'] = $item['uri'];

                // Use the raw HTML description as content
                $desc = $entry->find('description', 0);
                // decode HTML entities just ONCE
                $htmlContent = html_entity_decode($desc ? $desc->innertext : '', ENT_QUOTES | ENT_XML1, 'UTF-8');
                $item['content'] = $htmlContent;

                // Enclosure/image (optional)
                $enclosure = $entry->find('enclosure', 0);
                if ($enclosure && isset($enclosure->src)) {
                    $item['enclosures'][] = $enclosure->src;
                }

                // Date
                $dateNode = $entry->find('pubDate', 0);
                if ($dateNode) {
                    $item['timestamp'] = strtotime($dateNode->innertext);
                }

                $allItems[] = $item;
            }
        }

        // Shuffle to make it random, and slice to limit (optional, here to 30 items)
        shuffle($allItems);
        $this->items = array_slice($allItems, 0, 30);
    }
}
