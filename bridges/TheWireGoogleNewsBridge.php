<?php

class TheWireGoogleNewsBridge extends BridgeAbstract {
    const NAME = 'The Wire Google News Publications';
    const URI = 'https://news.google.com/publications/CAAqJAgKIh5DQklTRUFnTWFnd0tDblJvWlhkcGNtVXVhVzRvQUFQAQ?hl=en-IN&gl=IN&ceid=IN%3Aen';
    const DESCRIPTION = 'Latest articles from The Wire Google News Publications feed';
    const MAINTAINER = 'ChatGPT';
    const PARAMETERS = [];

    public function collectData() {
        $html = getSimpleHTMLDOM(self::URI);
        if (!$html) {
            throw new Exception('Could not fetch Google News publications page.');
        }

        // Find all article containers
        $articles = $html->find('div.IL9Cne');

        if (!$articles || count($articles) === 0) {
            throw new Exception('No articles found on the Google News publications page.');
        }

        foreach ($articles as $article) {
            $item = [];

            // Article URL inside <a class="JtKRv">
            $linkElem = $article->find('a.JtKRv', 0);
            if (!$linkElem || !isset($linkElem->href)) {
                continue; // skip if no link found
            }

            $href = $linkElem->href;

            // Google News URLs often have relative links starting with './read/'
            if (strpos($href, './') === 0) {
                $href = 'https://news.google.com/' . ltrim($href, './');
            } elseif (strpos($href, '/') === 0) {
                $href = 'https://news.google.com' . $href;
            }
            $item['uri'] = $href;
            $item['uid'] = $href;

            // Title is the anchor text inside the <a.JtKRv>
            $item['title'] = trim($linkElem->plaintext);

            // Optional: Extract image (if any)
            $imgElem = $article->find('img.Quavad', 0);
            if ($imgElem && isset($imgElem->src)) {
                $item['enclosures'] = [$imgElem->src];
            }

            // Optional: Extract source name or other meta if available
            // Usually in the div with class .XlKvRb but not reliable here; skipping for now.

            // Set timestamp as current time (no timestamp visible)
            $item['timestamp'] = time();

            // No content summary in Google News snippets, empty string
            $item['content'] = '';

            $this->items[] = $item;
        }

        if (empty($this->items)) {
            throw new Exception('No valid articles parsed from the Google News publications page.');
        }
    }
}
