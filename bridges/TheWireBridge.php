<?php

class TheWireBridge extends BridgeAbstract {
    const NAME = 'The Wire (Politics/Economy/Law)';
    const URI = 'https://thewire.in/';
    const DESCRIPTION = 'Latest articles from The Wire politics, economy, and law sections, including featured and side articles';
    const MAINTAINER = 'YourNameHere';
    const PARAMETERS = [];

    private $sourceUrls = [
        'https://thewire.in/category/politics/all',
        'https://thewire.in/category/economy/all',
        'https://thewire.in/category/law/all'
    ];

    public function collectData() {
        $allItems = [];
        $itemUrls = []; // To avoid duplicates

        foreach ($this->sourceUrls as $url) {
            // Browser-like headers to avoid blocking
            $opts = [
                'http' => [
                    'method' => "GET",
                    'header' =>
                        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) ".
                        "AppleWebKit/537.36 (KHTML, like Gecko) ".
                        "Chrome/124.0.0.0 Safari/537.36\r\n" .
                        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n" .
                        "Accept-Language: en-US,en;q=0.5\r\n" .
                        "Referer: https://www.google.com/\r\n"
                ]
            ];
            $context = stream_context_create($opts);
            $html = file_get_html($url, false, $context);
            if (!$html) continue;

            // 1. Featured articles (big ones at top)
            foreach($html->find('div.category-featured-article-container') as $fa) {
                $titleElem = $fa->find('div.category-featured-article-title a', 0);
                $contentElem = $fa->find('div.category-featured-article-content', 0);
                $imgElem = $fa->find('img.category-featured-article-image', 0);

                if ($titleElem && isset($titleElem->href)) {
                    $href = $titleElem->href;
                    if (strpos($href, '/') === 0) $href = 'https://thewire.in' . $href;
                    if (isset($itemUrls[$href])) continue;
                    $itemUrls[$href] = true;

                    $item = [
                        'title' => trim($titleElem->plaintext),
                        'uri' => $href,
                        'uid' => $href,
                        'author' => '',
                        'timestamp' => time(),
                        'content' => ($imgElem ? '<img src="'.$imgElem->src.'" /><br/>' : '') .
                                     ($contentElem ? $contentElem->plaintext : '')
                    ];

                    // Try to get author
                    $authorElem = $fa->find('div.category-featured-article-author-wrapper a', 0);
                    if ($authorElem) $item['author'] = trim($authorElem->plaintext);

                    $allItems[] = $item;
                }
            }

            // 2. Side articles (rest of the listing)
            foreach($html->find('div.side-article-wrapper-mc') as $sa) {
                $titleElem = $sa->find('div.side-article-title-mc a', 0);
                $imgElem = $sa->find('img#side-article-image-mc', 0);
                $categoryElem = $sa->find('a.side-article-category-mc', 0);

                if ($titleElem && isset($titleElem->href)) {
                    $href = $titleElem->href;
                    if (strpos($href, '/') === 0) $href = 'https://thewire.in' . $href;
                    if (isset($itemUrls[$href])) continue;
                    $itemUrls[$href] = true;

                    $item = [
                        'title' => trim($titleElem->plaintext),
                        'uri' => $href,
                        'uid' => $href,
                        'author' => '',
                        'timestamp' => time(),
                        'content' => ($imgElem ? '<img src="'.$imgElem->src.'" /><br/>' : '') .
                                     ($categoryElem ? '<b>'.$categoryElem->plaintext.'</b><br/>' : '')
                    ];

                    // Try to get author
                    $authorElem = $sa->find('div.side-article-author-mc a', 0);
                    if ($authorElem) $item['author'] = trim($authorElem->plaintext);

                    $allItems[] = $item;
                }
            }
        }

        if (empty($allItems)) {
            throw new Exception('No valid articles parsed from The Wire pages.');
        }

        $this->items = $allItems;
    }
}
