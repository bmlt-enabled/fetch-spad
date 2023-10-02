<?php

namespace Spad;

class Main
{
    const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:105.0) Gecko/20100101 Firefox/105.0';
    const SPAD_URL = 'https://spadna.org';
    const SPAD_DOM_ELEMENT = 'table';
    const CHAR_ENCODING = "UTF-8";
    const SPAD_CLASS = 'spad-rendered-element';

    public function runMain(array $atts = []): string
    {
        $args = shortcode_atts(['layout' => ''], $atts);
        $spad_layout = $this->sanitizeLayout($args);
        $spad_body = $this->fetchSpadBody();
        return $this->generateContent($spad_layout, $spad_body);
    }

    protected function sanitizeLayout(array $args): string
    {
        return !empty($args['layout']) ? sanitize_text_field(strtolower($args['layout'])) : get_option('spad_layout');
    }

    protected function fetchSpadBody(): string
    {
        $response = wp_remote_get(self::SPAD_URL, ['headers' => ['User-Agent' => self::USER_AGENT], 'timeout' => 60]);
        return wp_remote_retrieve_body($response);
    }

    protected function generateContent(string $spad_layout, string $spad_body): string
    {
        $spad_data = $this->prepareSpadData($spad_body);
        return $spad_layout === 'block' ? $this->generateBlockContent($spad_data) : $this->generateDefaultContent($spad_data);
    }

    protected function prepareSpadData(string $spad_body): string
    {
        $spad_data = str_replace('--', '&mdash;', $spad_body);
        return str_replace('Page Page', 'Page', $spad_data); // TODO: Remove when NAWS fixes
    }

    protected function generateBlockContent(string $spad_data): string
    {
        $domDoc = $this->createDomDocument($spad_data);
        $spad_ids = array('spad-date','spad-title','spad-page','spad-quote','spad-quote-source','spad-content','spad-divider','spad-thought','spad-copyright');
        $spad_class = 'spad-rendered-element';
        $i = 0;
        $k = 1;
        $content = '<div id="spad-container" class="'.$spad_class.'">';

        foreach ($domDoc->getElementsByTagName('tr') as $element) {
            if ($i != 5) {
                $formated_element = trim($element->nodeValue);
                $content .= '<div id="'.$spad_ids[$i].'" class="'.$spad_class.'">'.$formated_element.'</div>';
            } else {
                $xpath = new \DOMXPath($domDoc);
                foreach ($xpath->query('//tr') as $row) {
                    $row_values = array();
                    foreach ($xpath->query('td', $row) as $cell) {
                        $innerHTML= '';
                        $children = $cell->childNodes;
                        foreach ($children as $child) {
                            $innerHTML .= $child->ownerDocument->saveXML($child);
                        }
                        $row_values[] = $innerHTML;
                    }
                    $values[] = $row_values;
                }
                $break_array = preg_split('/<br[^>]*>/i', (join('', $values[5])));
                $content .= '<div id="'.$spad_ids[$i].'" class="'.$spad_class.'">';
                foreach ($break_array as $p) {
                    if (!empty($p)) {
                        $formated_element = '<p id="'.$spad_ids[$i].'-'.$k.'" class="'.$spad_class.'">'.trim($p).'</p>';
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
        $d = new \DOMDocument();
        libxml_use_internal_errors(true);
        $d->loadHTML(mb_convert_encoding($data, 'HTML-ENTITIES', self::CHAR_ENCODING));
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        return $d;
    }

    protected function generateDefaultContent(string $spad_data): string
    {
        $domDoc = $this->createDomDocument($spad_data);
        $xpath = new \DOMXpath($domDoc);
        $body = $xpath->query("//" . self::SPAD_DOM_ELEMENT);
        $spad = new \DOMDocument;
        foreach ($body as $child) {
            $spad->appendChild($spad->importNode($child, true));
        }
        return $spad->saveHTML();
    }
}
