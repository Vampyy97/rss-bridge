<?php

class TheWireCategoryBridge extends BridgeAbstract {
    const NAME = 'The Wire Politics Category';
    const URI = 'https://thewire.in/category/politics/all';
    const DESCRIPTION = 'Latest articles from The Wire Politics category';
    const MAINTAINER = 'ChatGPT';
    const PARAMETERS = [];

    public function collectData() {
        // Fetch the HTML content of the category page
        $html = getSimpleHTMLDOM(self::URI);
        if (!$html) {
            throw new Exception('Could not fetch The Wire politics category page.');
        }

        // Find all featured article containers
        $articles = $html->find('div.category-featured-article-container');

        if (!$articles || count($articles) === 0) {
            throw new Exception('No articles found in The Wire politics category.');
        }

        foreach ($articles as $article) {
            $item = [];

            // Extract article URL from the first <a> tag inside the container
            $linkElem = $article->find('a', 0);
            if (!$linkElem || !isset($linkElem->href)) {
                continue; // skip if no link found
            }
            $href = $linkElem->href;
            if (strpos($href, '/') === 0) {
                $href = 'https://thewire.in' . $href;
            }
            $item['uri'] = $href;
            $item['uid'] = $href;

            // Title inside div.category-featured-article-title > a
            $titleElem = $article->find('div.category-featured-article-title a', 0);
            $item['title'] = $titleElem ? trim($titleElem->plaintext) : 'No title';

            // Image inside the first <img> in the container with class 'category-featured-article-image'
            $imgElem = $article->find('img.category-featured-article-image', 0);
            if ($imgElem && isset($imgElem->src)) {
                $item['enclosures'] = [$imgElem->src];
            }

            // Author name inside div.category-featured-article-author-wrapper > a
            $authorElem = $article->find('div.category-featured-article-author-wrapper a', 0);
            $item['author'] = $authorElem ? trim($authorElem->plaintext) : '';

            // Category name inside div.category-featured-article-mini-wrapper > div > a (optional)
            $catElem = $article->find('div.category-featured-article-category a', 0);
            $item['category'] = $catElem ? trim($catElem->plaintext) : 'Politics';

            // Article summary inside div.category-featured-article-content
            $summaryElem = $article->find('div.category-featured-article-content', 0);
            $item['content'] = $summaryElem ? trim($summaryElem->plaintext) : '';

            // No published date visible here, fallback current timestamp
            $item['timestamp'] = time();

            $this->items[] = $item;
        }

        if (empty($this->items)) {
            throw new Exception('No valid articles parsed from The Wire politics category.');
        }
    }
}