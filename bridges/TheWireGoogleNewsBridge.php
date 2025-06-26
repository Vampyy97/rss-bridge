<?php

class TheWireGoogleNewsBridge extends BridgeAbstract {
    const NAME = 'The Wire Articles via Google News';
    const URI = 'https://news.google.com/search?q=thewire&hl=en-IN&gl=IN&ceid=IN%3Aen';
    const DESCRIPTION = 'Fetches latest The Wire articles via Google News search results';
    const MAINTAINER = 'ChatGPT';
    const PARAMETERS = [];

    public function collectData() {
        $html = getSimpleHTMLDOM(self::URI);
        if (!$html) {
            throw new Exception('Could not fetch Google News search results.');
        }

        // Find each article container
        foreach ($html->find('div.IL9Cne') as $articleDiv) {
            $item = [];

            // Extract source site (optional, to confirm it's thewire.in)
            $source = $articleDiv->find('div.vr1PYe', 0);
            if (!$source || strtolower(trim($source->plaintext)) !== 'thewire.in') {
                // Skip if source isn't The Wire
                continue;
            }

            // Extract article link and title
            $linkElem = $articleDiv->find('a.JtKRv', 0);
            if (!$linkElem || !isset($linkElem->href)) {
                continue;
            }

            // Google News uses relative links starting with './read/'
            $href = $linkElem->href;
            if (strpos($href, './') === 0) {
                // Absolute URL construction
                $href = 'https://news.google.com' . substr($href, 1);
            }

            $item['uri'] = $href;
            $item['uid'] = $href;

            $item['title'] = trim($linkElem->plaintext);

            // Extract thumbnail image if available
            $imgElem = $articleDiv->find('img.qEdqNd', 0);
            if ($imgElem && isset($imgElem->src)) {
                $item['enclosures'] = [$imgElem->src];
            }

            // Description is not usually present in Google News search results, so leave empty or add fallback
            $item['content'] = '';

            // Timestamp is not directly present; use current time as fallback
            $item['timestamp'] = time();

            $this->items[] = $item;
        }

        if (empty($this->items)) {
            throw new Exception('No The Wire articles found on Google News.');
        }
    }
}
