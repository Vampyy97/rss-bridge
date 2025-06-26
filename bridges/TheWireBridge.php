<?php

class TheWireBridge extends BridgeAbstract {
    const NAME = 'The Wire Articles';
    const URI = 'https://thewire.in/';
    const DESCRIPTION = 'Fetches latest article headlines, images, and descriptions from The Wire homepage';
    const MAINTAINER = 'ChatGPT';
    const PARAMETERS = [];

    public function collectData() {
        $html = getSimpleHTMLDOM(self::URI);
        if (!$html) {
            throw new \Exception('Could not fetch The Wire homepage.');
        }

        // Find article containers (update selector if site changes)
        $articles = $html->find('div.tw-article-list div.tw-article'); 
        if (!$articles) {
            throw new \Exception('No articles found on The Wire homepage.');
        }

        foreach ($articles as $article) {
            $item = [];

            // Extract article link and make absolute URL
            $link = $article->find('a.tw-article-link', 0);
            if (!$link || !isset($link->href)) {
                continue;
            }
            $item['uri'] = self::URI . ltrim($link->href, '/');
            $item['uid'] = $item['uri'];

            // Extract title
            $titleEl = $article->find('h3.tw-article-title', 0);
            $item['title'] = $titleEl ? trim($titleEl->plaintext) : 'No title';

            // Extract description (if available)
            $descEl = $article->find('p.tw-article-description', 0);
            $item['content'] = $descEl ? trim($descEl->plaintext) : $item['title'];

            // Extract image URL (if available)
            $imgEl = $article->find('img.tw-article-image', 0);
            $item['enclosures'] = ($imgEl && $imgEl->src) ? [$imgEl->src] : [];

            // Extract timestamp if available (else fallback to now)
            $timeEl = $article->find('time', 0);
            if ($timeEl && $timeEl->datetime) {
                $item['timestamp'] = strtotime($timeEl->datetime);
            } else {
                $item['timestamp'] = time();
            }

            $this->items[] = $item;
        }
    }
}
