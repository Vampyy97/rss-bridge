<?php

class FTCustomHTMLBridge extends BridgeAbstract {
    const NAME = 'FT Custom HTML Bridge';
    const URI = 'https://vampyy97.github.io/FT_240625_P2.html';
    const DESCRIPTION = 'Parses FT-format HTML hosted on GitHub Pages';
    const MAINTAINER = 'Vampyy97';

    const PARAMETERS = [];

    public function collectData() {
        $html = getSimpleHTMLDOM(self::URI);
        if (!$html) {
            returnServerError('Could not load FT HTML');
        }

        $section = $html->find('div.WordSection1', 0);
        if (!$section) {
            returnServerError('WordSection1 not found');
        }

        $elements = $section->find('p.MsoNormal, img');

        $items = [];
        $currentItem = null;

        foreach ($elements as $el) {
            if ($el->tag === 'p' && $el->find('b span', 0)) {
                // New article starts
                if ($currentItem !== null) {
                    $this->items[] = $currentItem;
                }

                $title = trim($el->find('b span', 0)->plaintext);
                $currentItem = [
                    'title' => $title,
                    'uri' => self::URI . '#'. md5($title),
                    'uid' => md5($title),
                    'content' => '',
                    'timestamp' => time(),
                ];
            } elseif ($currentItem !== null) {
                if ($el->tag === 'p') {
                    $currentItem['content'] .= '<p>' . $el->innertext . '</p>';
                } elseif ($el->tag === 'img' && isset($el->src)) {
                    $imgSrc = $el->src;
                    if (strpos($imgSrc, 'http') !== 0) {
                        $imgSrc = dirname(self::URI) . '/' . ltrim($imgSrc, '/');
                    }
                    $currentItem['content'] .= '<img src="' . htmlspecialchars($imgSrc) . '">';
                }
            }
        }

        if ($currentItem !== null) {
            $this->items[] = $currentItem;
        }
    }
}
