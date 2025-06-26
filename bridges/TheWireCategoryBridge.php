<?php

class TheWireCategoryBridge extends BridgeAbstract {
    const NAME = 'The Wire Category Feed';
    const URI = 'https://m.thewire.in/category/28/politics'; // Default category URL
    const DESCRIPTION = 'Fetches articles from The Wire category page on mobile site';
    const MAINTAINER = 'ChatGPT';

    const PARAMETERS = [
        'default' => [
            'category_url' => [
                'name' => 'Category URL',
                'type' => 'text',
                'required' => true,
                'defaultValue' => 'https://m.thewire.in/category/28/politics',
                'exampleValue' => 'https://m.thewire.in/category/28/politics',
                'title' => 'Enter full category URL from m.thewire.in',
            ],
        ],
    ];

    public function collectData() {
        $url = $this->getInput('category_url') ?: self::URI;
        $html = getSimpleHTMLDOM($url);
        if (!$html) {
            throw new \Exception('Could not fetch category page.');
        }

        // Loop through each article container (adjust selector after inspecting the page)
        foreach ($html->find('div.category-article') as $article) {
            $item = [];

            // Get anchor tag with article link and title
            $a = $article->find('a', 0);
            if (!$a || !isset($a->href)) continue;

            $item['uri'] = $a->href;
            $item['uid'] = $item['uri'];

            // Title text
            $item['title'] = trim($a->plaintext);

            // Image if exists
            $img = $article->find('img', 0);
            $item['enclosures'] = ($img && $img->src) ? [$img->src] : [];

            // Description or excerpt if available
            $desc = $article->find('p.excerpt', 0);
            $item['content'] = $desc ? trim($desc->plaintext) : $item['title'];

            // Timestamp fallback to current time (improve if datetime available)
            $item['timestamp'] = time();

            $this->items[] = $item;
        }
    }
}