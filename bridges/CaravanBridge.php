<?php

class CaravanBridge extends BridgeAbstract {
    const NAME = 'The Caravan Magazine (Random Category)';
    const URI = 'https://caravanmagazine.in/';
    const DESCRIPTION = 'Random articles from The Caravan categories: Media, Politics, Business';
    const MAINTAINER = 'ChatGPT';
    const PARAMETERS = [];

    private $categoryUrls = [
        'https://caravanmagazine.in/media',
        'https://caravanmagazine.in/politics',
        'https://caravanmagazine.in/business',
    ];

    public function collectData() {
        // Randomly pick a category URL
        $url = $this->categoryUrls[array_rand($this->categoryUrls)];

        // Fetch HTML
        $html = getSimpleHTMLDOM($url);
        if (!$html) {
            throw new Exception('Could not fetch The Caravan category page.');
        }

        // Find article containers
        $articles = $html->find('div.usp-cz4eoi');

        if (!$articles || count($articles) === 0) {
            throw new Exception('No articles found on The Caravan page: ' . $url);
        }

        foreach ($articles as $article) {
            $item = [];

            $link = $article->find('a.usp-hyckll', 0);
            if (!$link || !isset($link->href)) {
                continue;
            }

            $href = $link->href;
            if (strpos($href, '/') === 0) {
                $href = self::URI . ltrim($href, '/');
            }
            $item['uri'] = $href;
            $item['uid'] = $href;

            $categorySpans = $link->find('span.usp-qn1l76');
            $categories = [];
            if ($categorySpans) {
                foreach ($categorySpans as $span) {
                    $text = trim($span->plaintext);
                    if ($text !== '/' && $text !== '') {
                        $categories[] = $text;
                    }
                }
            }
            $item['category'] = implode(' / ', $categories);

            $title = trim($link->plaintext);
            foreach ($categories as $cat) {
                $title = str_replace($cat, '', $title);
            }
            $title = trim(str_replace(['/', "Editor's Pick"], '', $title));
            $item['title'] = $title ?: 'No title';

            $sourceSpan = $link->find('span.usp-nkz2al span', 0);
            $item['author'] = $sourceSpan ? trim($sourceSpan->plaintext) : 'The Caravan';

            $item['content'] = '';
            $item['timestamp'] = time();

            $this->items[] = $item;
        }

        if (empty($this->items)) {
            throw new Exception('No valid articles parsed from The Caravan page: ' . $url);
        }
    }
}
