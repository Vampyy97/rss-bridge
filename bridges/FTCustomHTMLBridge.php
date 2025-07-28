<?php

class FTCustomHTMLBridge extends BridgeAbstract {
    const NAME = 'Financial Times Custom HTML';
    const URI = 'https://vampyy97.github.io/index.html';
    const DESCRIPTION = 'Extract articles listed in index.html that link to individual article_X.html pages';
    const MAINTAINER = 'Vipul Agrawal';

    public function collectData() {
        $indexHtml = getSimpleHTMLDOM(self::URI);
        if (!$indexHtml) {
            throw new Exception('Could not fetch index.html');
        }

        foreach ($indexHtml->find('ul li a') as $a) {
            $href = $a->href;
            $title = trim($a->plaintext);

            if (!$href || !$title) continue;

            $fullUrl = urljoin(self::URI, $href);
            $articleHtml = getSimpleHTMLDOM($fullUrl);
            if (!$articleHtml) continue;

            // Extract article content (everything inside <body>)
            $body = $articleHtml->find('body', 0);
            $content = $body ? $body->innertext : 'Content unavailable';

            $item = [];
            $item['title'] = $title;
            $item['uri'] = $fullUrl;
            $item['content'] = $content;
            $item['timestamp'] = time();
            $item['author'] = 'Financial Times (HTML)';

            $this->items[] = $item;
        }

        if (empty($this->items)) {
            throw new Exception('No articles found or fetched from index.');
        }
    }
}
