<?php
class CaravanBridge extends BridgeAbstract {
    const NAME = 'Caravan Magazine (Multi-section Random Feed)';
    const URI = 'https://caravanmagazine.in/';
    const DESCRIPTION = 'Fetches and mixes articles from Caravan Politics, Media, and Society sections (2024 layout)';
    const MAINTAINER = 'Vipul';

    const PARAMETERS = [];

    // Hardcoded sections
    private $sections = [
        'https://caravanmagazine.in/politics',
        'https://caravanmagazine.in/media',
        'https://caravanmagazine.in/society',
    ];

    public function collectData() {
        $allItems = [];
        foreach ($this->sections as $sectionUrl) {
            $html = getSimpleHTMLDOM($sectionUrl);
            if (!$html) continue;

            foreach ($html->find('div.usp-wkni24') as $card) {
                // Article link
                $alink = $card->find('div.usp-cz4eoi a.usp-jfzd85', 0);
                if (!$alink) continue;

                $relurl = $alink->href;
                $uri = strpos($relurl, 'http') === 0 ? $relurl : 'https://caravanmagazine.in' . $relurl;

                // Title
                $title = trim($alink->find('span.usp-htaabc', 0)->plaintext ?? '');
                if (!$title) continue;

                // Section/category
                $section = trim($alink->find('span.usp-hyckll', 0)->plaintext ?? '');

                // Authors
                $authors = trim($alink->find('span.usp-nkz2al', 0)->plaintext ?? '');

                // Image
                $img = '';
                $aimg = $card->find('div.usp-frnmpu a img.usp-lmbjuj', 0);
                if ($aimg && $aimg->src) {
                    $img = $aimg->src;
                    if (strpos($img, '//') === 0) $img = 'https:' . $img;
                }

                // Compose content
                $content = '';
                if ($img) $content .= '<img src="' . $img . '" style="max-width:500px;"><br>';
                if ($section) $content .= '<b>' . htmlspecialchars($section) . '</b><br>';
                $content .= '<b>' . htmlspecialchars($title) . '</b><br>';
                if ($authors) $content .= '<i>' . htmlspecialchars($authors) . '</i><br>';

                $allItems[] = [
                    'uri' => $uri,
                    'title' => $title,
                    'author' => $authors,
                    'timestamp' => time(),
                    'content' => $content,
                    'enclosures' => $img ? [$img] : [],
                ];
            }
        }
        // Shuffle for random feed
        shuffle($allItems);

        $this->items = $allItems;
    }
}
