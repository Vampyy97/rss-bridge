<?php

class TheWireGoogleNewsBridge extends BridgeAbstract {
    const NAME = 'The Wire via Google News';
    const URI = 'https://news.google.com/search?q=site:thewire.in&hl=en-IN&gl=IN&ceid=IN:en';
    const DESCRIPTION = 'Fetch latest The Wire articles using Google News';
    const MAINTAINER = 'ChatGPT';
    const PARAMETERS = [];

    public function collectData() {
        $html = getSimpleHTMLDOM(self::URI);
        if (!$html) {
            throw new Exception('Could not fetch Google News search page.');
        }

        // Google News article container selector
        foreach ($html->find('article') as $article) {
            $a = $article->find('a[href^="./articles/"]', 0);
            if (!$a) continue;

            $item = [];

            // Google News links are relative; fix the URL
            $relativeUrl = $a->href;
            $url = 'https://news.google.com' . substr($relativeUrl, 1);
            $item['uri'] = $url;
            $item['title'] = $a->plaintext;

            // Extract time published if available
            $time = $article->find('time', 0);
            $item['timestamp'] = $time ? strtotime($time->datetime) : time();

            // Extract snippet or description if available
            $snippet = $article->find('span[jsname="fbQN7e"]', 0);
            $item['content'] = $snippet ? $snippet->plaintext : '';

            // Images are sometimes in 'img' tags inside article
            $img = $article->find('img', 0);
            if ($img && isset($img->src)) {
                $item['enclosures'] = [$img->src];
            }

            $this->items[] = $item;
        }
    }
}