<?php
class CaravanBridge extends BridgeAbstract {
    const NAME = 'Caravan Magazine (2024+ layout)';
    const URI = 'https://caravanmagazine.in/';
    const DESCRIPTION = 'Extracts stories from the main grid, including new classes for general and featured articles.';
    const MAINTAINER = 'Vipul';

    const PARAMETERS = [];

    public function collectData(){
        $url = 'https://caravanmagazine.in/'; // Main page or section, adjust as needed
        $html = getSimpleHTMLDOM($url) or returnServerError('Could not load Caravan homepage');

        // Each article box
        foreach($html->find('div.usp-wkni24') as $box) {
            $img = '';
            $title = '';
            $uri = '';
            $author = '';
            $section = '';
            // Get image
            if($aimg = $box->find('div.usp-frnmpu a', 0)) {
                $uri = $aimg->href;
                if(strpos($uri, 'http') !== 0) $uri = 'https://caravanmagazine.in' . $uri;
                $imgTag = $aimg->find('img', 0);
                if($imgTag) {
                    $img = $imgTag->src;
                    if(strpos($img, '//') === 0) $img = 'https:' . $img;
                }
            }
            // Get main link
            if($alink = $box->find('div.usp-cz4eoi a.usp-jfzd85', 0)) {
                // Section/Type
                $section = trim($alink->find('span.usp-hyckll', 0)->plaintext ?? '');
                // Headline
                $title = trim($alink->find('span.usp-htaabc', 0)->plaintext ?? '');
                // Author(s)
                $authbox = $alink->find('span.usp-nkz2al', 0);
                if($authbox) {
                    $author = trim($authbox->plaintext);
                }
            }

            if(!$title) continue;
            $content = '';
            if($img) $content .= "<img src='$img' style='max-width:400px;'><br>";
            if($section) $content .= "<b>$section</b><br>";
            $content .= "<b>$title</b><br>";
            if($author) $content .= "<i>$author</i><br>";

            $this->items[] = [
                'title' => $title,
                'uri' => $uri,
                'content' => $content,
                'author' => $author,
                'timestamp' => time() // No timestamp on homepage grid, could fetch from article page if needed
            ];
        }
    }
}
