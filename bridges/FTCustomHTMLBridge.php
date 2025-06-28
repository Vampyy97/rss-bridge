<?php

class FTCustomHTMLBridge extends BridgeAbstract {
    const NAME = 'Financial Times Custom HTML';
    const URI = 'https://vampyy97.github.io/FT_240625_P2.html';
    const DESCRIPTION = 'Extract articles structured with <h2> headings from a custom HTML file';
    const MAINTAINER = 'YourName';

    public function collectData() {
        $html = getSimpleHTMLDOM(self::URI);
        if (!$html) {
            throw new Exception('Could not fetch the HTML file.');
        }

        // Get the main title (from <h1>)
        $mainTitleElem = $html->find('h1', 0);
        $mainTitle = $mainTitleElem ? $mainTitleElem->plaintext : 'Financial Times';

        // Find all <h2>
        foreach ($html->find('h2') as $h2) {
            $item = [];
            $item['title'] = trim($h2->plaintext);

            // Start gathering content after this <h2> until the next <h2>
            $contentHtml = '';
            $elem = $h2->next_sibling();

            // Defensive: Sometimes next_sibling may return text nodes, skip those
            while ($elem && (!isset($elem->tag) || strtolower($elem->tag) != 'h2')) {
                if (isset($elem->outertext)) {
                    $contentHtml .= $elem->outertext;
                }
                $elem = $elem->next_sibling();
            }

            $item['content'] = $contentHtml;
            $item['uri'] = self::URI . '#' . urlencode($item['title']);
            $item['timestamp'] = time();
            $item['author'] = $mainTitle;

            $this->items[] = $item;
        }

        if (empty($this->items)) {
            throw new Exception('No articles found in the HTML file.');
        }
    }
}


