<?php

class TheWireVideosBridge extends BridgeAbstract {
    const NAME = 'The Wire Videos Section';
    const URI = 'https://thewire.in/';
    const DESCRIPTION = 'Fetches latest videos with titles, links, and thumbnails from The Wire';
    const MAINTAINER = 'ChatGPT';
    const PARAMETERS = [];

    public function collectData() {
        $html = getSimpleHTMLDOM(self::URI);
        if (!$html) {
            throw new \Exception('Could not fetch The Wire homepage.');
        }

        // Find the videos container
        $videosContainer = $html->find('div.hp-videos', 0);
        if (!$videosContainer) {
            throw new \Exception('Could not find videos container on The Wire homepage.');
        }

        // Find each video block inside container
        foreach ($videosContainer->find('div.hp-video-content') as $video) {
            $item = [];

            // Find link and normalize URL
            $a = $video->find('a', 0);
            if (!$a || !isset($a->href)) {
                continue;
            }
            $item['uri'] = self::URI . ltrim($a->href, '/');
            $item['uid'] = $item['uri'];

            // Get the image src
            $img = $video->find('img#article-image', 0);
            $item['enclosures'] = ($img && $img->src) ? [$img->src] : [];

            // Extract title text from the div.hp-video-title sibling
            $titleDiv = $video->find('div.hp-video-title', 0);
            $item['title'] = $titleDiv ? trim($titleDiv->plaintext) : 'No title';

            // Optional: no description available in this snippet, so just use title
            $item['content'] = $item['title'];

            // Timestamp not available in snippet; fallback to current time
            $item['timestamp'] = time();

            $this->items[] = $item;
        }
    }
}
