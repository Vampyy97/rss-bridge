<?php

class TheQuintBridge extends BridgeAbstract {
    const NAME = 'The Quint';
    const URI = 'https://www.thequint.com/';
    const DESCRIPTION = 'Latest articles from The Quint politics, law, and explainers sections';
    const MAINTAINER = 'ChatGPT';

    const PARAMETERS = [];

    // The three source URLs to choose from randomly
    private $sourceUrls = [
        'https://www.thequint.com/news/politics',
        'https://www.thequint.com/news/law',
        'https://www.thequint.com/explainers'
    ];

    public function collectData() {
    $allItems = [];

    foreach ($this->sourceUrls as $url) {
        $html = getSimpleHTMLDOM($url);
        if (!$html) {
            // You can choose to skip or throw an error here
            continue;
        }

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

            $item['content'] = '<p>' . htmlspecialchars($item['title']) . '</p>';

            $item['timestamp'] = time();

            $allItems[] = $item;
        }
    }

    if (empty($allItems)) {
        throw new Exception('No valid articles parsed from The Quint pages.');
    }

    // Assign all collected items to the feed
    $this->items = $allItems;
}
}
