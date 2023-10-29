<?php

namespace Spad;

use FetchMeditation\SPADLanguage;
use FetchMeditation\SPADSettings;
use FetchMeditation\SPAD;

class Reading
{
    public function renderReading($atts = []): string
    {
        $layout = sanitize_text_field(strtolower($atts['layout'] ?? get_option('spad_layout')));
        $settings = new SPADSettings(SPADLanguage::English);
        $instance = SPAD::getInstance($settings);
        $entry = $instance->fetch();
        return static::buildLayout($entry, $layout === "block");
    }

    private static function buildLayout(object $entry, bool $inBlock): string
    {
        $cssIdentifier = $inBlock ? 'spad' : 'spad-table';

        $paragraphContent = '';
        $count = 1;

        foreach ($entry->content as $c) {
            $paragraphContent .= $inBlock ? "<p id=\"$cssIdentifier-content-$count\" class=\"$cssIdentifier-rendered-element\">$c<p>" : "$c<br><br>";
            $count++;
        }

        $content = $inBlock
            ? '<div id="' . $cssIdentifier . '-container" class="' . $cssIdentifier . '-rendered-element">'
            : '<table align="center" id="' . $cssIdentifier . '-container" class="' . $cssIdentifier . '">';

        $data = [
            'date' => $entry->date,
            'title' => $entry->title,
            'page' => $entry->page,
            'quote' => $entry->quote,
            'source' => $entry->source,
            'paragraphs' => $paragraphContent,
            'divider' => '&mdash;&mdash;&mdash; &nbsp;  &nbsp; &mdash;&mdash;&mdash; &nbsp;  &nbsp; &mdash;&mdash;&mdash; &nbsp;  &nbsp; &mdash;&mdash;&mdash; &nbsp;  &nbsp; &mdash;&mdash;&mdash;',
            'thought' => $entry->thought,
            'copyright' => $entry->copyright,
        ];

        foreach ($data as $key => $value) {
            if (!empty($value)) {
                if ($key === 'quote' && !$inBlock) {
                    $element = '<i>' . $value . '</i>';
                } elseif ($key === 'title' && !$inBlock) {
                    $element = '<h1>' . $value . '</h1>';
                } elseif ($key === 'date' && !$inBlock) {
                    $element = '<h2>' . $value . '</h2>';
                } elseif ($key === 'divider' && $inBlock) {
                    $element = '';
                } else {
                    $element = $value;
                }

                $content .= $inBlock ? "<div id=\"$cssIdentifier-$key\" class=\"$cssIdentifier-rendered-element\">$element</div>" : '<tr><td align="' . ($key === 'title' || $key === 'divider' ? 'center' : 'left') . '">' . $element . ($key === 'source' || $key === 'quote' || $key === 'thought' || $key === 'divider' || $key === 'page' ? '<br><br>' : '') . '</td></tr>';
            }
        }

        $content .= $inBlock ? '</div>' : '</table>';
        $content = str_replace('--', '&mdash;', $content);
        $content = str_replace('Page Page', 'Page', $content);

        return $content;
    }
}
