<?php

class TheWireBridge extends BridgeAbstract {
    const NAME = 'The Wire';
    const URI = 'https://thewire.in/';
    const DESCRIPTION = 'Fetches videos and headlines from The Wire homepage';
    const MAINTAINER = 'ChatGPT';
    const PARAMETERS = [];

    public function collectData() {
        $html = getSimpleHTMLDOM(self::URI);
        if (!$html) {
            return;
        }

        $container = $html->find('div.hp-videos-container', 0);
        if (!$container) {
            throw new \Exception('Could not find videos container on The Wire homepage');
        }

        foreach ($container->find('div.hp-video-content') as $videoDiv) {
            $link = $videoDiv->find('div.hp-video-content-image a', 0);
            if (!$link || !$link->href) {
                continue;
            }

            $item = [];
            $item['uri'] = $link->href;
            $item['uid'] = $link->href;

            $img = $link->find('img', 0);
            if ($img && $img->src) {
                $item['enclosures'] = [$img->src];
            } else {
                $item['enclosures'] = [];
            }

            $titleDiv = $videoDiv->find('div.hp-video-title', 0);
            $item['title'] = $titleDiv ? trim($titleDiv->plaintext) : 'No title';

            // You can enhance content here by scraping more text if needed
            $item['content'] = $item['title'];

            // Timestamp or author info is not clearly present in the snippet; leaving empty
            $item['timestamp'] = time();

            $this->items[] = $item;
        }

        if (count($this->items) === 0) {
            throw new \Exception('No videos found on The Wire homepage');
        }
    }
}
