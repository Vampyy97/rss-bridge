<?php

class LivemintMixBridge extends BridgeAbstract {
    const NAME = 'Livemint Mixed Feed';
    const URI = 'https://www.livemint.com/';
    const DESCRIPTION = 'Aggregates and returns random items from Livemint opinion, politics, and news RSS feeds';
    const MAINTAINER = 'ChatGPT';

    // List of feed URLs to mix
    private $feeds = [
        'https://www.livemint.com/rss/opinion',
        'https://www.livemint.com/rss/politics',
        'https://www.livemint.com/rss/news',
    ];

    public function collectData() {
        $allItems = [];

        foreach ($this->feeds as $feedUrl) {
            $rss = $this->fetchFeed($feedUrl);
            if (!$rss) {
                continue; // Skip if feed not loaded
            }

            foreach ($rss->channel->item as $item) {
                $allItems[] = $item;
            }
        }

        if (empty($allItems)) {
            throw new \Exception('No feed items found from Livemint feeds.');
        }

        // Shuffle and pick random items, you can change the count as needed
        shuffle($allItems);
        $count = min(10, count($allItems)); // Return up to 10 items

        for ($i = 0; $i < $count; $i++) {
            $item = $allItems[$i];
            $newItem = [];

            $newItem['title'] = (string) $item->title;
            $newItem['uri'] = (string) $item->link;
            $newItem['content'] = (string) $item->description;
            $newItem['timestamp'] = isset($item->pubDate) ? strtotime((string) $item->pubDate) : time();
            $newItem['uid'] = $newItem['uri'];

            // Check for enclosure or media:content for images if available
            if (isset($item->enclosure)) {
                $newItem['enclosures'] = [(string) $item->enclosure['url']];
            } elseif (isset($item->children('media', true)->content)) {
                $media = $item->children('media', true)->content;
                $newItem['enclosures'] = [(string) $media->attributes()->url];
            }

            $this->items[] = $newItem;
        }
    }

    // Helper to load RSS feed XML and return SimpleXMLElement or false
    private function fetchFeed(string $url) {
        $content = getContents($url);
        if (!$content) {
            return false;
        }

        libxml_use_internal_errors(true);
        $rss = simplexml_load_string($content);
        libxml_clear_errors();

        return $rss ?: false;
    }
}