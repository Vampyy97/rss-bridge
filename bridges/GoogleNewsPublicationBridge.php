<?php

class GoogleNewsPublicationBridge extends BridgeAbstract {
    const NAME = 'Google News Publication';
    // This URL can be parameterized if you want dynamic search or publication IDs
    const URI = 'https://news.google.com/search?q=site:thewire.in&hl=en-IN&gl=IN&ceid=IN:en';
    const DESCRIPTION = 'Google News publication articles feed';
    const MAINTAINER = 'ChatGPT';
    const PARAMETERS = [];

    public function collectData() {
        $html = getSimpleHTMLDOM(self::URI);
        if (!$html) {
            throw new Exception('Could not fetch Google News publication page.');
        }

        // Find all article containers with class m5k28
        $articles = $html->find('div.m5k28');

        if (!$articles || count($articles) === 0) {
            throw new Exception('No articles found on Google News publication page.');
        }

        foreach ($articles as $article) {
            $item = [];

            // Article link - the first <a> with class WwrzSb inside div.XlKvRb
            $linkElem = $article->find('div.XlKvRb a.WwrzSb', 0);
            if (!$linkElem || !isset($linkElem->href)) {
                continue;
            }
            $href = $linkElem->href;
            // Clean up relative URLs if any
            if (strpos($href, './') === 0) {
                $href = 'https://news.google.com' . substr($href, 1);
            } elseif (strpos($href, '/') === 0) {
                $href = 'https://news.google.com' . $href;
            }
            $item['uri'] = $href;
            $item['uid'] = $href;

            // Title inside <a class="JtKRv">
            $titleElem = $article->find('a.JtKRv', 0);
            $item['title'] = $titleElem ? trim($titleElem->plaintext) : 'No title';

            // Image src inside <img class="Quavad vwBmvb">
            $imgElem = $article->find('figure img.Quavad', 0);
            if ($imgElem && isset($imgElem->src)) {
                $item['enclosures'] = [$imgElem->src];
            }

            // No author or timestamp in this container, fallback to empty or current time
            $item['author'] = '';
            $item['timestamp'] = time();

            // Description is missing here - leave empty or you can try fetching article content later

            $item['content'] = '';

            $this->items[] = $item;
        }

        if (empty($this->items)) {
            throw new Exception('No valid articles parsed from Google News publication.');
        }
    }
}
