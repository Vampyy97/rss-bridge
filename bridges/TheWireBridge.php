<?php

class TheWireBridge extends BridgeAbstract {
    const NAME = 'The Wire';
    const URI = 'https://thewire.in/';
    const DESCRIPTION = 'Latest articles from The Wire';
    const MAINTAINER = 'ChatGPT';
    const PARAMETERS = [];

    /**
     * Fetches the webpage using cURL with a browser User-Agent.
     * Throws exception on failure.
     */
    private function fetchPage(string $url): string {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0 Safari/537.36');
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Curl error: $error");
        }
        curl_close($ch);
        return $result;
    }

    public function collectData() {
        try {
            $htmlContent = $this->fetchPage(self::URI);
        } catch (Exception $e) {
            throw new Exception("Could not fetch The Wire homepage: " . $e->getMessage());
        }

        $html = str_get_html($htmlContent);
        if (!$html) {
            throw new Exception("Failed to parse HTML from The Wire");
        }

        // Based on your inspect element, articles are in div.listing__block--main div.listing__item
        $articles = $html->find('div.listing__block--main div.listing__item');

        if (!$articles) {
            throw new Exception("No articles found on The Wire homepage");
        }

        foreach ($articles as $article) {
            $item = [];

            // Article URL and UID
            $linkElement = $article->find('a', 0);
            if (!$linkElement || !isset($linkElement->href)) {
                continue;
            }
            $item['uri'] = $linkElement->href;
            $item['uid'] = $linkElement->href;

            // Title
            $titleElement = $article->find('h2.listing__title', 0);
            $item['title'] = $titleElement ? trim($titleElement->plaintext) : 'No title';

            // Summary / Description - looks like <p class="listing__summary">
            $summaryElement = $article->find('p.listing__summary', 0);
            if ($summaryElement) {
                $item['content'] = trim($summaryElement->plaintext);
            } else {
                $item['content'] = $item['title']; // fallback
            }

            // Author - appears to be in span.listing__author
            $authorElement = $article->find('span.listing__author', 0);
            $item['author'] = $authorElement ? trim($authorElement->plaintext) : '';

            // Timestamp - usually in time.listing__date, with datetime attribute
            $timeElement = $article->find('time.listing__date', 0);
            if ($timeElement && isset($timeElement->datetime)) {
                $item['timestamp'] = strtotime($timeElement->datetime);
            } else {
                $item['timestamp'] = time();
            }

            $this->items[] = $item;
        }
    }
}
