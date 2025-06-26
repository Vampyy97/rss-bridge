<?php

class IndianExpressMixBridge extends BridgeAbstract {
    const NAME = 'Indian Express Mixed Feed';
    const URI = 'https://indianexpress.com/';
    const DESCRIPTION = 'Fetches and mixes content from top Indian Express categories';
    const MAINTAINER = 'Vampyy97';
    const PARAMETERS = [];

    private const FEEDS = [
        'https://indianexpress.com/section/explained/feed/',
        'https://indianexpress.com/section/political-pulse/feed/',
        'https://indianexpress.com/section/technology/artificial-intelligence/feed/',
        'https://indianexpress.com/section/opinion/feed/',
        'https://indianexpress.com/section/world/feed/',
    ];

    public function collectData() {
        $feedUrl = self::FEEDS[array_rand(self::FEEDS)];
        $rss = simplexml_load_string(getContents($feedUrl));

        foreach ($rss->channel->item as $item) {
            $this->items[] = [
                'uri' => (string) $item->link,
                'title' => (string) $item->title,
                'content' => (string) $item->description,
                'timestamp' => strtotime((string) $item->pubDate),
                'author' => (string) $item->author,
            ];
        }
    }
}