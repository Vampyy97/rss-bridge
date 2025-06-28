<?php

class TheQuintBridge extends BridgeAbstract {
    const NAME = 'The Quint';
    const URI = 'https://www.thequint.com/';
    const DESCRIPTION = 'Latest articles from The Quint politics, law, and explainers sections';
    const MAINTAINER = 'ChatGPT';

    private $sourceUrls = [
        'https://www.thequint.com/news/politics',
        'https://www.thequint.com/news/law',
        'https://www.thequint.com/explainers'
    ];

    public function collectData() {
        $allItems = [];
        foreach ($this->sourceUrls as $url) {
            $opts = [
                'http' => [
                    'method' => "GET",
                    'header' =>
                        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 " .
                        "(KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36\r\n" .
                        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n" .
                        "Accept-Language: en-US,en;q=0.5\r\n" .
                        "Referer: https://www.google.com/\r\n"
                ]
            ];
            $context = stream_context_create($opts);

            $html = file_get_html($url, false, $context);
            if (!$html) continue;

            // 1. Find ALL article cards
            $articleCards = [];
            foreach (['div.card-image-wrapper', 'div.custom-story-card-4', 'div.VxAk1'] as $selector) {
                foreach ($html->find($selector) as $el) {
                    $articleCards[] = $el;
                }
            }

            foreach ($articleCards as $article) {
                $item = [];

                // Find title (may be under h2, possibly with extra wrappers)
                $titleElem = $article->find('h2', 0);
                if (!$titleElem) continue;
                $item['title'] = trim($titleElem->plaintext);

                // Find link
                $linkElem = $article->find('a', 0);
                if (!$linkElem || !isset($linkElem->href)) continue;
                $href = $linkElem->href;
                if (strpos($href, '/') === 0) {
                    $href = 'https://www.thequint.com' . $href;
                }
                $item['uri'] = $href;
                $item['uid'] = $href;

                // Find image
                $imgElem = $article->find('img', 0);
                if ($imgElem && isset($imgElem->src)) {
                    $item['enclosures'] = [$imgElem->src];
                }

                $item['content'] = '<p>' . htmlspecialchars($item['title']) . '</p>';
                $item['timestamp'] = time();

                $allItems[] = $item;
            }
        }
        if (empty($allItems)) {
            throw new Exception('No valid articles parsed from The Quint pages.');
        }
        $this->items = $allItems;
    }
}
?>
