<?php

class TheQuintBridge extends BridgeAbstract {
    const NAME = 'The Quint';
    const URI = 'https://www.thequint.com/';
    const DESCRIPTION = 'Latest articles from The Quint politics, law, and explainers sections';
    const MAINTAINER = 'ChatGPT';

    const PARAMETERS = [];

    // Source URLs to fetch articles from
    private $sourceUrls = [
        'https://www.thequint.com/news/politics',
        'https://www.thequint.com/news/law',
        'https://www.thequint.com/explainers'
    ];

    public function collectData() {
        $allItems = [];

        foreach ($this->sourceUrls as $url) {
            // Setup HTTP context with headers to mimic a browser request
            $opts = [
                'http' => [
                    'method' => "GET",
                    'header' => 
                        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) " .
                        "AppleWebKit/537.36 (KHTML, like Gecko) " .
                        "Chrome/114.0.0.0 Safari/537.36\r\n" .
                        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n" .
                        "Accept-Language: en-US,en;q=0.5\r\n" .
                        "Referer: https://www.google.com/\r\n"
                ]
            ];
            $context = stream_context_create($opts);

            // Fetch the HTML with custom headers
            $html = file_get_html($url, false, $context);
            if (!$html) {
                continue; // Skip if fetch failed
            }

            // Find article containers
            $articles = $html->find('div.card-image-wrapper._1o6Zo');
            if (!$articles || count($articles) === 0) {
                continue;
            }

            foreach ($articles as $article) {
                $item = [];

                $titleElem = $article->find('div.cardimage-headline-view div.headline h2', 0);
                if (!$titleElem) {
                    continue;
                }
                $item['title'] = trim($titleElem->plaintext);

                $linkElem = $article->parent()->parent()->find('a', 0);
                if (!$linkElem || !isset($linkElem->href)) {
                    continue;
                }
                $href = $linkElem->href;
                if (strpos($href, '/') === 0) {
                    $href = 'https://www.thequint.com' . $href;
                }
                $item['uri'] = $href;
                $item['uid'] = $href;

                $imgElem = $article->find('figure.qt-figure img.qt-image', 0);
                if ($imgElem && isset($imgElem->src)) {
                    $item['enclosures'] = [$imgElem->src];
                }

                // Basic content is just the title wrapped in <p>
                $item['content'] = '<p>' . htmlspecialchars($item['title']) . '</p>';

                // Use current time as timestamp fallback
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
