<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CI18nHtml
 *
 * @author aspirin
 */
class CI18nHtml extends CHtml {

    /**
     * Generates an HTML element.
     * @param string $tag the tag name
     * @param array $htmlOptions the element attributes. The values will be HTML-encoded using {@link encode()}.
     * Since version 1.0.5, if an 'encode' attribute is given and its value is false,
     * the rest of the attribute values will NOT be HTML-encoded.
     * Since version 1.1.5, attributes whose value is null will not be rendered.
     * @param mixed $content the content to be enclosed between open and close element tags. It will not be HTML-encoded.
     * If false, it means there is no body content.
     * @param boolean $closeTag whether to generate the close tag.
     * @return string the generated HTML element tag
     */
    public static function tag($tag, $htmlOptions = array(), $content = false, $closeTag = true, $langGroup = 'chtml') {
        $html = '<' . $tag . self::renderAttributes($htmlOptions);
        if ($content === false)
            return $closeTag ? $html . ' />' : $html . '>';
        else
            return $closeTag ? $html . '>' . Yii::t($langGroup, $content) . '</' . $tag . '>' : $html . '>' . Yii::t($langGroup, $content);
    }

    /**
     * Generates a hyperlink tag.
     * @param string $text link body. It will NOT be HTML-encoded. Therefore you can pass in HTML code such as an image tag.
     * @param mixed $url a URL or an action route that can be used to create a URL.
     * See {@link normalizeUrl} for more details about how to specify this parameter.
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated hyperlink
     * @see normalizeUrl
     * @see clientChange
     */
    public static function link($text, $url = '#', $htmlOptions = array()) {
        if ($url !== '')
            $htmlOptions['href'] = self::normalizeUrl($url);
        self::clientChange('click', $htmlOptions);
        return self::tag('a', $htmlOptions, $text);
    }

}
