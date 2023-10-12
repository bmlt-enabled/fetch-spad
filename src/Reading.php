<?php

namespace Spad;

class Reading
{
    const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0';
    const URL = 'https://spadna.org';
    const DOM_ELEMENT = 'table';
    const CHAR_ENCODING = "UTF-8";
    const CSS_CLASS = 'spad-rendered-element';

    public function renderReading($atts = []): string
    {
        $args = shortcode_atts(['layout' => ''], $atts);
        $layout = $this->sanitizeLayout($args);
        $body = $this->fetchSpadBody();
        return $this->generateContent($layout, $body);
    }

    protected function sanitizeLayout(array $args): string
    {
        return !empty($args['layout']) ? sanitize_text_field(strtolower($args['layout'])) : get_option('spad_layout');
    }

    protected function fetchSpadBody(): string
    {
        $response = wp_remote_get(self::URL, ['headers' => ['User-Agent' => self::USER_AGENT], 'timeout' => 60]);
        return wp_remote_retrieve_body($response);
    }

    protected function generateContent(string $layout, string $body): string
    {
        $data = $this->prepareSpadData($body);
        return $layout === 'block' ? $this->generateBlockContent($data) : $this->generateDefaultContent($data);
    }

    protected function prepareSpadData(string $body): string
    {
        $data = str_replace('--', '&mdash;', $body);
        return str_replace('Page Page', 'Page', $data); // TODO: Remove when NAWS fixes
    }

    protected function generateBlockContent(string $data): string
    {
        $domDoc = $this->createDomDocument($data);
        $cssIds = array('spad-date','spad-title','spad-page','spad-quote','spad-quote-source','spad-content','spad-divider','spad-thought','spad-copyright');
        $content = '<div id="spad-container" class="' . self::CSS_CLASS . '">';
        $values = [];
        $i = 0;
        $k = 1;
        foreach ($domDoc->getElementsByTagName('tr') as $element) {
            if ($i != 5) {
                $formated_element = trim($element->nodeValue);
                $content .= '<div id="' . $cssIds[$i] . '" class="' . self::CSS_CLASS . '">' . $formated_element . '</div>';
            } else {
                $xpath = new \DOMXPath($domDoc);
                foreach ($xpath->query('//tr') as $row) {
                    $row_values = array();
                    foreach ($xpath->query('td', $row) as $cell) {
                        $innerHTML = '';
                        $children = $cell->childNodes;
                        foreach ($children as $child) {
                            $innerHTML .= $child->ownerDocument->saveXML($child);
                        }
                        $row_values[] = $innerHTML;
                    }
                    $values[] = $row_values;
                }
                $break_array = preg_split('/<br[^>]*>/i', (join('', $values[5])));
                $content .= '<div id="' . $cssIds[$i] . '" class="' . self::CSS_CLASS . '">';
                foreach ($break_array as $p) {
                    if (!empty($p)) {
                        $formated_element = '<p id="' . $cssIds[$i] . '-' . $k . '" class="' . self::CSS_CLASS . '">' . trim($p) . '</p>';
                        $content .= preg_replace("/<p[^>]*>([\s]|&nbsp;)*<\/p>/", '', $formated_element);
                        $k++;
                    }
                }
                $content .= '</div>';
            }
            $i++;
        }
        $content .= '</div>';
        return $content;
    }

    protected function createDomDocument(string $data): \DOMDocument
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', self::CHAR_ENCODING));
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        return $dom;
    }

    protected function generateDefaultContent(string $data): string
    {
        $domDoc = $this->createDomDocument($data);
        $xpath = new \DOMXpath($domDoc);
        $body = $xpath->query("//" . self::DOM_ELEMENT);
        $reading = new \DOMDocument();
        foreach ($body as $child) {
            $reading->appendChild($reading->importNode($child, true));
        }
        return $reading->saveHTML();
    }
}
