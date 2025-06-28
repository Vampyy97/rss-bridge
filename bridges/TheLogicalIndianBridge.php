<?php

class TheLogicalIndianBridge extends BridgeAbstract {
    const NAME = 'The Logical Indian';
    const URI = 'https://thelogicalindian.com/';
    const DESCRIPTION = 'Fetches latest news and featured articles from The Logical Indian';
    const MAINTAINER = 'ChatGPT';

    // Main URLs for scraping
    private $sourceUrls = [
        'https://thelogicalindian.com/news/',
        'https://thelogicalindian.com/changemakers/',
        'https://thelogicalindian.com/made-my-day/',
    ];

    public function collectData() {
        $allItems = [];

        foreach ($this->sourceUrls as $url) {
            $html = getSimpleHTMLDOM($url);
            if (!$html) continue;

            // Featured Article
            foreach($html->find('div.category-featured-article-container') as $feat) {
                $item = [];

                $linkElem = $feat->find('div.category-featured-article-title a', 0);
                if ($linkElem) {
                    $item['title'] = trim($linkElem->plaintext);
                    $href = $linkElem->href;
                    if (strpos($href, 'http') !== 0) $href = 'https://thelogicalindian.com' . $href;
                    $item['uri'] = $href;
                    $item['uid'] = $href;
                }

                $imgElem = $feat->find('img.category-featured-article-image', 0);
                if ($imgElem) {
                    $item['enclosures'] = [$imgElem->src];
                }

                $descElem = $feat->find('div.category-featured-article-content', 0);
                $item['content'] = $descElem ? $descElem->innertext : $item['title'];

                $authorElem = $feat->find('div.category-featured-article-author-wrapper', 0);
                if ($authorElem) {
                    $item['author'] = trim($authorElem->plaintext);
                }

                $item['timestamp'] = time();
                $allItems[] = $item;
            }

            // Loop: Standard Article Cards
            foreach($html->find('div.elementor-loop-container.elementor-grid div.elementor.elementor-36340.e-loop-item') as $art) {
                $item = [];
                // Title & link
                $titleElem = $art->find('div.elementor-widget-theme-post-title .elementor-heading-title a', 0);
                if ($titleElem) {
                    $item['title'] = trim($titleElem->plaintext);
                    $href = $titleElem->href;
                    if (strpos($href, 'http') !== 0) $href = 'https://thelogicalindian.com' . $href;
                    $item['uri'] = $href;
                    $item['uid'] = $href;
                }
                // Image
                $imgElem = $art->find('div.elementor-widget-theme-post-featured-image .elementor-widget-container a img', 0);
                if ($imgElem) {
                    $item['enclosures'] = [$imgElem->src];
                }
                // Excerpt
                $descElem = $art->find('div.elementor-widget-theme-post-excerpt .elementor-widget-container', 0);
                $item['content'] = $descElem ? $descElem->innertext : $item['title'];

                // Author (sometimes inside .elementor-widget-theme-post-meta)
                $meta = $art->find('.elementor-widget-theme-post-meta .elementor-widget-container', 0);
                if ($meta) $item['author'] = trim($meta->plaintext);

                $item['timestamp'] = time();
                $allItems[] = $item;
            }
        }

        if (empty($allItems)) {
            throw new Exception('No articles found for The Logical Indian');
        }
        $this->items = $allItems;
    }
}
