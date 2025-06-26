<?php
 
class ScrollBridge extends BridgeAbstract {
const NAME = 'Scroll.in Latest News';
const URI = 'https://scroll.in/latest';
const DESCRIPTION = 'Fetches the latest headlines from Scroll.in';
    const MAINTAINER = 'ChatGPT';
    const PARAMETERS = [];
 
    // ✅ Escape XML entities to avoid broken feeds
    private function xmlEscape($string) {
        return htmlspecialchars($string, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
 
    public function collectData() {
        $html = getSimpleHTMLDOM(self::URI);
        if (!$html) {
            return;
        }
 
        foreach ($html->find('div.all-stories-container ul li.row-story') as $li) {
            $a = $li->find('a', 0);
            if (!$a || !$a->href) continue;
            $meta = $a->find('div.row-story-meta', 0);
 
            $item = [];
            $item['uri'] = $a->href;
            $item['uid'] = $a->href;
 
            $img = $a->find('figure img', 0);
            $item['enclosures'] = ($img && $img->src) ? [$img->src] : [];
 
            $title = $meta && $meta->find('h1', 0) ? trim($meta->find('h1', 0)->plaintext) : 'No title';
            $summary = $meta && $meta->find('h2', 0) ? trim($meta->find('h2', 0)->plaintext) : '';
            $author = $meta && $meta->find('address', 0) ? trim($meta->find('address', 0)->plaintext) : '';
 
            $item['title'] = $this->xmlEscape($title);
            $item['content'] = $this->xmlEscape($summary ?: $title);
            $item['author'] = $this->xmlEscape($author);
            $item['timestamp'] = ($meta && $meta->find('time', 0) && $meta->find('time', 0)->datetime)
                ? strtotime($meta->find('time', 0)->datetime)
                : time();
 
            $this->items[] = $item;
        }
 
        foreach ($html->find('div.trending-collection ol li.row-story') as $li) {
            $a = $li->find('a', 0);
            if (!$a || !$a->href) continue;
            $meta = $a->find('div.row-story-meta', 0);
 
            $item = [];
            $item['uri'] = $a->href;
            $item['uid'] = $a->href;
 
            $img = $a->find('figure img', 0);
            $item['enclosures'] = ($img && $img->src) ? [$img->src] : [];
 
            $title = $meta && $meta->find('h1', 0) ? trim($meta->find('h1', 0)->plaintext) : 'No title';
            $author = $meta && $meta->find('address', 0) ? trim($meta->find('address', 0)->plaintext) : '';
 
            $item['title'] = $this->xmlEscape($title);
            $item['content'] = $this->xmlEscape($title);
            $item['author'] = $this->xmlEscape($author);
            $item['timestamp'] = ($meta && $meta->find('time', 0) && $meta->find('time', 0)->datetime)
                ? strtotime($meta->find('time', 0)->datetime)
                : time();
 
            $this->items[] = $item;
        }
    }
}