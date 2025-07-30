<?php

class FSSAIRaidNewsBridge extends BridgeAbstract {
    const NAME = 'FSSAI & Food Raid News';
    const URI = 'https://news.google.com';
    const DESCRIPTION = 'Fetches food raid and restaurant audit news from NDTV, The Hindu, Times Now, Dainik Bhaskar and more';
    const MAINTAINER = 'vipul';

    public function collectData() {
        $queries = [
            'site:ndtv.com FSSAI food raid',
            'site:thehindu.com restaurant inspection',
            'site:timesnownews.com hotel raid expired food',
            'site:indiatoday.in food safety raid',
            'site:hindustantimes.com restaurant hygiene audit',
            'site:dnaindia.com FSSAI inspection kitchen',
            'site:indianexpress.com food sample seized',
            'site:timesofindia.indiatimes.com hotel sealed food violation',
            'site:bhaskar.com FSSAI होटल छापा',
            'site:amarujala.com रेस्टोरेंट छापा',
            'site:navbharattimes.indiatimes.com FSSAI जांच'
        ];

        foreach ($queries as $keyword) {
            $query = urlencode($keyword);
            $feedUrl = "https://www.google.com/alerts/feeds/13915103463037456789/12345678901234567890?q={$query}"; // Placeholder for real scraping
            $searchUrl = "https://news.google.com/rss/search?q={$query}+when:7d&hl=en-IN&gl=IN&ceid=IN:en";
            $rss = @simplexml_load_file($searchUrl);

            if (!$rss || !isset($rss->channel->item)) {
                continue;
            }

            foreach ($rss->channel->item as $entry) {
                $this->items[] = [
                    'uri' => (string)$entry->link,
                    'title' => (string)$entry->title,
                    'timestamp' => strtotime((string)$entry->pubDate),
                    'content' => (string)$entry->description,
                    'author' => isset($entry->source) ? (string)$entry->source : 'Google News'
                ];
            }
        }
    }
}