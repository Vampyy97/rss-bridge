<?php

class TheWireBridge extends BridgeAbstract {
    const NAME = 'The Wire - Latest News';
    const URI = 'https://thewire.in/';
    const DESCRIPTION = 'Latest stories from TheWire.in';
    const MAINTAINER = 'ChatGPT';
    const PARAMETERS = [];

    public function collectData() {
        $html = getSimpleHTMLDOM(self::URI);
        if (!$html) {
            return;
        }

        foreach ($html->find('div.card') as $article) {
            $linkTag = $article->find('a.card__title', 0);
            if (!$linkTag) continue;

            $uri = $linkTag->href;
            $title = trim($linkTag->plaintext);

            $imgTag = $article->find('img', 0);
            $image = $imgTag ? $imgTag->src : '';

            $summary = $article->find('div.card__summary', 0);
            $desc = $summary ? trim($summary->plaintext) : $title;

            $this->items[] = [
                'uri' => $uri,
                'title' => $title,
                'content' => ($image ? '<img src="' . $image . '"/><br>' : '') . $desc,
                'timestamp' => time(), // No datetime found, fallback to current
                'enclosures' => $image ? [$image] : [],
            ];
        }
    }
}