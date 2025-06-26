<?php

class TheWireBridge extends BridgeAbstract {
    const NAME = 'The Wire Videos';
    const URI = 'https://thewire.in/';
    const DESCRIPTION = 'Fetches video posts from The Wire homepage';
    const MAINTAINER = 'ChatGPT';
    const PARAMETERS = [];

    public function collectData() {
        $html = getSimpleHTMLDOM(self::URI);
        if (!$html) {
            throw new \Exception('Could not fetch The Wire homepage.');
        }

        $videosContainer = $html->find('div.hp-videos', 0);
        if (!$videosContainer) {
            throw new \Exception('Could not find videos container on The Wire homepage.');
        }

        foreach ($videosContainer->find('div.hp-video-content') as $video) {
            $linkTag = $video->find('div.hp-video-content-image a', 0);
            if (!$linkTag || !isset($linkTag->href)) {
                continue;
            }

            $item = [];
            $item['uri'] = self::URI . $linkTag->href;
            $item['uid'] = $item['uri'];

            $imgTag = $linkTag->find('img#article-image', 0);
            if ($imgTag && isset($imgTag->src)) {
                $item['enclosures'] = [$imgTag->src];
            } else {
                $item['enclosures'] = [];
            }

            $titleDiv = $video->find('div.hp-video-title', 0);
            $item['title'] = $titleDiv ? trim($titleDiv->plaintext) : 'No title';

            $item['content'] = $item['title'];

            // No timestamp info available in snippet, so use current time
            $item['timestamp'] = time();

            $this->items[] = $item;
        }

        if (count($this->items) === 0) {
            throw new \Exception('No videos found on The Wire homepage.');
        }
    }
}
