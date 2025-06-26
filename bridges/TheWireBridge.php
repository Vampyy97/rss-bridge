<?php
// URL of The Wire homepage or category page
$url = "https://thewire.in/";

// Fetch the HTML content
$html = file_get_contents($url);
if (!$html) {
    die("Could not fetch The Wire homepage.");
}

// Load HTML into DOMDocument
libxml_use_internal_errors(true); // Suppress warnings for malformed HTML
$doc = new DOMDocument();
$doc->loadHTML($html);
libxml_clear_errors();

// Use XPath to query specific article blocks
$xpath = new DOMXPath($doc);

// XPath query to select article containers (adjusted per your inspect snippet)
$articles = $xpath->query("//div[contains(@class, 'article-container')]");

header("Content-Type: application/rss+xml; charset=UTF-8");

// Start RSS feed output
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0">
<channel>
    <title>The Wire - Latest Articles</title>
    <link>https://thewire.in/</link>
    <description>Latest news and articles from The Wire</description>
<?php

$maxItems = 10;
$count = 0;

foreach ($articles as $article) {
    if ($count >= $maxItems) break;

    // Extract article title
    $titleNode = $xpath->query(".//div[contains(@class, 'article-title')]//a", $article);
    $title = $titleNode->length ? trim($titleNode->item(0)->nodeValue) : "No title";

    // Extract article link
    $link = $titleNode->length ? $titleNode->item(0)->getAttribute('href') : '';

    // Ensure full URL
    if ($link && strpos($link, 'http') !== 0) {
        $link = rtrim($url, '/') . '/' . ltrim($link, '/');
    }

    // Extract image URL
    $imgNode = $xpath->query(".//a[contains(@class, 'article-image-container')]//img", $article);
    $imageUrl = $imgNode->length ? $imgNode->item(0)->getAttribute('src') : '';

    // Extract excerpt if available
    $excerptNode = $xpath->query(".//div[contains(@class, 'article-excerpt')]", $article);
    $excerpt = $excerptNode->length ? trim($excerptNode->item(0)->nodeValue) : '';

    // Output RSS item
    echo "<item>\n";
    echo "<title><![CDATA[$title]]></title>\n";
    echo "<link><![CDATA[$link]]></link>\n";
    if ($imageUrl) {
        echo "<enclosure url=\"" . htmlspecialchars($imageUrl) . "\" type=\"image/jpeg\" />\n";
    }
    if ($excerpt) {
        echo "<description><![CDATA[$excerpt]]></description>\n";
    }
    echo "</item>\n";

    $count++;
}
?>
</channel>
</rss>
