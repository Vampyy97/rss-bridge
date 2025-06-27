<?php

class FTCustomHTMLBridge extends BridgeAbstract {
    const NAME = 'FT Custom HTML Bridge';
    const URI = 'https://vampyy97.github.io/FT_240625_P2.html';
    const DESCRIPTION = 'Parses the user-hosted FT HTML and returns items as feed';
    const MAINTAINER = 'Vampyy97';

    const PARAMETERS = [];

    public function collectData() {
        $html = getSimpleHTMLDOM(self::URI);
        if (!$html) {
            returnServerError('Could not load FT HTML');
        }

        // Loop through article blocks (you can fine-tune selector as per structure)
        foreach ($html->find('h2') as $index => $heading) {
            $item = [];

            $item['title'] = trim($heading->plaintext);
            $item['uri'] = self::URI . '#article-' . $index;
            $item['uid'] = md5($item['title']);

            // Try to get the following paragraph and image (adjust tag positions as needed)
            $content = '';

            $next = $heading->next_sibling();
            while ($next && ($next->tag !== 'h2')) {
                if ($next->tag === 'p') {
                    $content .= '<p>' . $next->innertext . '</p>';
                } elseif ($next->tag === 'img') {
                    $imgSrc = $next->src;
                    $content .= '<img src="' . $imgSrc . '">';
                }
                $next = $next->next_sibling();
            }

            $item['content'] = $content;
            $item['timestamp'] = time();

            $this->items[] = $item;
        }
    }
}
