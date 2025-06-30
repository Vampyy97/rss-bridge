<?php
class CaravanBridge extends BridgeAbstract {
    const NAME = 'Caravan Magazine (2024)';
    const URI = 'https://caravanmagazine.in/';
    const DESCRIPTION = 'Fetches latest stories from Caravan using new layout (June 2024)';
    const MAINTAINER = 'Vipul';

    const PARAMETERS = [
        [
            'section' => [
                'name' => 'Section URL',
                'type' => 'text',
                'defaultValue' => 'https://caravanmagazine.in/',
                'required' => false,
                'title' => 'Paste a section URL or leave blank for homepage'
            ]
        ]
    ];

    public function collectData() {
        $sectionUrl = $this->getInput('section') ?: self::URI;
        $html = getSimpleHTMLDOM($sectionUrl) or returnServerError('Could not load Caravan page');

        foreach ($html->find('div.usp-wkni24') as $card) {
            // Main article link
            $alink = $card->find('div.usp-cz4eoi a.usp-jfzd85', 0);
            if (!$alink) continue;

            $relurl = $alink->href;
            $uri = strpos($relurl, 'http') === 0 ? $relurl : 'https://caravanmagazine.in' . $relurl;

            // Title
            $title = trim($alink->find('span.usp-htaabc', 0)->plaintext ?? '');
            if (!$title) continue;

            // Section/category (can be composite, e.g. "Politics / Commentary")
            $section = trim($alink->find('span.usp-hyckll', 0)->plaintext ?? '');

            // Authors (could be one or more, comma separated)
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

            $this->items[] = [
                'uri' => $uri,
                'title' => $title,
                'author' => $authors,
                'timestamp' => time(), // No time in grid; can crawl inner article if needed
                'content' => $content,
                'enclosures' => $img ? [$img] : []
            ];
        }
    }
}
