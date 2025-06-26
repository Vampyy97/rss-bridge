class IndianExpressMixBridge extends BridgeAbstract {
    const NAME = 'Indian Express Mixed Feed';
    const URI = 'https://indianexpress.com/';
    const DESCRIPTION = 'Mix of curated feeds from Indian Express';
    const MAINTAINER = 'Vipul';

    const PARAMETERS = [];

    private $feeds = [
        'https://indianexpress.com/section/opinion/columns/feed/',
        'https://indianexpress.com/section/political-pulse/feed/',
        'https://indianexpress.com/section/explained/feed/',
        'https://indianexpress.com/section/lifestyle/art-and-culture/feed/',
        'https://indianexpress.com/section/world/feed/',
    ];

    public function collectData() {
        foreach ($this->feeds as $feedUrl) {
            $rss = simplexml_load_file($feedUrl);
            foreach ($rss->channel->item as $item) {
                $content = (string) $item->description;
                $img = '';
                // Try to extract <img> from description
                if (preg_match('/<img[^>]+src="([^">]+)"/i', $content, $matches)) {
                    $img = $matches[1];
                }

                $this->items[] = [
                    'uri' => (string) $item->link,
                    'title' => (string) $item->title,
                    'author' => (string) $item->author ?? '',
                    'timestamp' => strtotime((string) $item->pubDate),
                    'content' => $img ? "<img src='$img'><br>" . $content : $content,
                    'enclosures' => $img ? [$img] : [],
                ];
            }
        }
    }
}
