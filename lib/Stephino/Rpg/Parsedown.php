<?php

/**
 * Parsedown
 * 
 * @title      Markdown
 * @desc       Markdown parser
 * @copyright  (c) Parsedown - parsedown.org
 * @author     Emanuil Rusev - erusev.com
 * @author     Mark Jivko <stephino.team@gmail.com>
 * @package    stephino-rpg
 * @license    MIT
 * 
 * Copyright (c) 2013-2018 Emanuil Rusev
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */
class Stephino_Rpg_Parsedown {
    
    /**
     * Singleton instances
     * 
     * @var Stephino_Rpg_Parsedown[]
     */
    protected static $_instances = array();
    
    protected $_definitionData = array();
    protected $_specialCharacters = array(
        '\\', '`', '*', '_', '{', '}', '[', ']', '(', ')', '>', '#', '+', '-', '.', '!', '|', '~'
    );
    protected $_strongRegex = array(
        '*' => '/^[*]{2}((?:\\\\\*|[^*]|[*][^*]*+[*])+?)[*]{2}(?![*])/s',
        '_' => '/^__((?:\\\\_|[^_]|_[^_]*+_)+?)__(?!_)/us',
    );
    protected $_emRegex = array(
        '*' => '/^[*]((?:\\\\\*|[^*]|[*][*][^*]+?[*][*])+?)[*](?![*])/s',
        '_' => '/^_((?:\\\\_|[^_]|__[^_]*__)+?)_(?!_)\b/us',
    );
    protected $_regexHtmlAttribute = '[a-zA-Z_:][\w:.-]*+(?:\s*+=\s*+(?:[^"\'=<>`\s]+|"[^"]*+"|\'[^\']*+\'))?+';
    protected $_voidElements = array(
        'area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source',
    );
    protected $_textLevelElements = array(
        'a', 'br', 'bdo', 'abbr', 'blink', 'nextid', 'acronym', 'basefont',
        'b', 'em', 'big', 'cite', 'small', 'spacer', 'listing',
        'i', 'rp', 'del', 'code', 'strike', 'marquee',
        'q', 'rt', 'ins', 'font', 'strong',
        's', 'tt', 'kbd', 'mark',
        'u', 'xm', 'sub', 'nobr',
        'sup', 'ruby',
        'var', 'span',
        'wbr', 'time',
    );
    protected $_breaksEnabled = false;
    protected $_markupEscaped = false;
    protected $_urlsLinked = true;
    protected $_safeMode = false;
    protected $_strictMode = false;
    protected $_safeLinksWhitelist = array(
        'http://',
        'https://',
        'ftp://',
        'ftps://',
        'mailto:',
        'tel:',
        'data:image/png;base64,',
        'data:image/gif;base64,',
        'data:image/jpeg;base64,',
        'irc:',
        'ircs:',
        'git:',
        'ssh:',
        'news:',
        'steam:',
    );
    protected $_blockTypes = array(
        '#' => array('Header'),
        '*' => array('Rule', 'List'),
        '+' => array('List'),
        '-' => array('SetextHeader', 'Table', 'Rule', 'List'),
        '0' => array('List'),
        '1' => array('List'),
        '2' => array('List'),
        '3' => array('List'),
        '4' => array('List'),
        '5' => array('List'),
        '6' => array('List'),
        '7' => array('List'),
        '8' => array('List'),
        '9' => array('List'),
        ':' => array('Table'),
        '<' => array('Comment', 'Markup'),
        '=' => array('SetextHeader'),
        '>' => array('Quote'),
        '[' => array('Reference'),
        '_' => array('Rule'),
        '`' => array('FencedCode'),
        '|' => array('Table'),
        '~' => array('FencedCode'),
    );
    protected $_unmarkedBlockTypes = array(
        'Code',
    );
    protected $_inlineTypes = array(
        '!' => array('Image'),
        '&' => array('SpecialCharacter'),
        '*' => array('Emphasis'),
        ':' => array('Url'),
        '<' => array('UrlTag', 'EmailTag', 'Markup'),
        '[' => array('Link'),
        '_' => array('Emphasis'),
        '`' => array('Code'),
        '~' => array('Strikethrough'),
        '\\' => array('EscapeSequence'),
    );
    protected $_inlineMarkerList = '!*_&[:<`~\\';

    /**
     * Get a Singleton instance of Stephino_Rpg_Parsedown
     * 
     * @param string $name Instance name
     * @return Stephino_Rpg_Parsedown
     */
    public static function instance($name = 'default') {
        if (!isset(self::$_instances[$name])) {
            self::$_instances[$name] = new static();
        }
        return self::$_instances[$name];
    }
    
    /**
     * Parse MarkDown-formatted text into HTML
     * 
     * @param string $text
     * @return string HTML Text
     */
    public function parse($text) {
        return $this->text($text);
    }
    
    /**
     * Parse MarkDown-formatted text into HTML
     * 
     * @param string $text
     * @return string HTML Text
     */
    public function text($text) {
        return trim($this->_elements($this->_textElements($text)), "\n");
    }
    
    /**
     * Parse inline text
     * 
     * @param string   $text         Text to parse
     * @param string[] $nonNestables (optional) List of non-nestable elements
     * @return string
     */
    public function line($text, $nonNestables = array()) {
        return $this->_elements($this->_lineElements($text, $nonNestables));
    }
    
    /**
     * Set breaks enabled
     * 
     * @param boolean $breaksEnabled Breaks enabled
     * @return Stephino_Rpg_Parsedown
     */
    public function setBreaksEnabled($breaksEnabled) {
        $this->_breaksEnabled = $breaksEnabled;
        return $this;
    }

    /**
     * Ignore markups
     * 
     * @param boolean $markupEscaped Markup escaped
     * @return Stephino_Rpg_Parsedown
     */
    public function setMarkupEscaped($markupEscaped) {
        $this->_markupEscaped = (boolean) $markupEscaped;
        return $this;
    }

    /**
     * Convert URLs to anchors
     * 
     * @param boolean $urlsLinked URLs linked
     * @return Stephino_Rpg_Parsedown
     */
    public function setUrlsLinked($urlsLinked) {
        $this->_urlsLinked = (boolean) $urlsLinked;
        return $this;
    }

    /**
     * Set safe mode: ignore markups and comments and sanitize elements
     * 
     * @param boolean $safeMode Safe mode
     * @return Stephino_Rpg_Parsedown
     */
    public function setSafeMode($safeMode) {
        $this->_safeMode = (bool) $safeMode;
        return $this;
    }

    /**
     * Set headers strict mode: headers are ignored if there is no space between # and the first character
     * 
     * @param boolean $strictMode Strict mode
     * @return Stephino_Rpg_Parsedown
     */
    public function setStrictMode($strictMode) {
        $this->_strictMode = (bool) $strictMode;
        return $this;
    }
    
    protected static function _pregReplaceElements($regexp, $elements, $text) {
        $newElements = array();

        while (preg_match($regexp, $text, $matches, PREG_OFFSET_CAPTURE)) {
            $offset = $matches[0][1];
            $before = substr($text, 0, $offset);
            $after = substr($text, $offset + strlen($matches[0][0]));
            $newElements[] = array('text' => $before);
            foreach ($elements as $Element) {
                $newElements[] = $Element;
            }
            $text = $after;
        }

        $newElements[] = array('text' => $text);
        return $newElements;
    }
    
    protected static function _escape($text, $allowQuotes = false) {
        return htmlspecialchars($text, $allowQuotes ? ENT_NOQUOTES : ENT_QUOTES, 'UTF-8');
    }

    protected static function _striAtStart($string, $needle) {
        $len = strlen($needle);
        if ($len > strlen($string)) {
            return false;
        } else {
            return strtolower(substr($string, 0, $len)) === strtolower($needle);
        }
    }

    protected function _textElements($text) {
        // Make sure no definitions are set
        $this->_definitionData = array();

        // Standardize line breaks
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        // Remove surrounding line breaks
        $text = trim($text, "\n");

        // Split text into lines
        $lines = explode("\n", $text);

        // Iterate through lines to identify blocks
        return $this->_linesElements($lines);
    }
    
    protected function _lines(array $lines) {
        return $this->_elements($this->_linesElements($lines));
    }

    protected function _linesElements($lines) {
        $Elements = array();
        $CurrentBlock = null;

        foreach ($lines as $line) {
            if (chop($line) === '') {
                if (isset($CurrentBlock)) {
                    $CurrentBlock['interrupted'] = (isset($CurrentBlock['interrupted']) ? $CurrentBlock['interrupted'] + 1 : 1);
                }
                continue;
            }

            while (($beforeTab = strstr($line, "\t", true)) !== false) {
                $shortage = 4 - strlen($beforeTab) % 4;
                $line = $beforeTab
                    . str_repeat(' ', $shortage)
                    . substr($line, strlen($beforeTab) + 1);
            }

            $indent = strspn($line, ' ');
            $text = $indent > 0 ? substr($line, $indent) : $line;
            $Line = array('body' => $line, 'indent' => $indent, 'text' => $text);
            if (isset($CurrentBlock['continuable'])) {
                $methodName = '_block' . $CurrentBlock['type'] . 'Continue';
                $Block = $this->$methodName($Line, $CurrentBlock);
                if (isset($Block)) {
                    $CurrentBlock = $Block;
                    continue;
                } else {
                    if ($this->_isBlockCompletable($CurrentBlock['type'])) {
                        $methodName = '_block' . $CurrentBlock['type'] . 'Complete';
                        $CurrentBlock = $this->$methodName($CurrentBlock);
                    }
                }
            }

            $marker = $text[0];
            $blockTypes = $this->_unmarkedBlockTypes;

            if (isset($this->_blockTypes[$marker])) {
                foreach ($this->_blockTypes[$marker] as $blockType) {
                    $blockTypes [] = $blockType;
                }
            }

            foreach ($blockTypes as $blockType) {
                $Block = $this->{"_block$blockType"}($Line, $CurrentBlock);
                if (isset($Block)) {
                    $Block['type'] = $blockType;
                    if (!isset($Block['identified'])) {
                        if (isset($CurrentBlock)) {
                            $Elements[] = $this->_extractElement($CurrentBlock);
                        }
                        $Block['identified'] = true;
                    }
                    if ($this->_isBlockContinuable($blockType)) {
                        $Block['continuable'] = true;
                    }
                    $CurrentBlock = $Block;
                    continue 2;
                }
            }

            if (isset($CurrentBlock) and $CurrentBlock['type'] === 'Paragraph') {
                $Block = $this->_paragraphContinue($Line, $CurrentBlock);
            }

            if (isset($Block)) {
                $CurrentBlock = $Block;
            } else {
                if (isset($CurrentBlock)) {
                    $Elements[] = $this->_extractElement($CurrentBlock);
                }
                $CurrentBlock = $this->_paragraph($Line);
                $CurrentBlock['identified'] = true;
            }
        }

        if (isset($CurrentBlock['continuable']) and $this->_isBlockCompletable($CurrentBlock['type'])) {
            $methodName = '_block' . $CurrentBlock['type'] . 'Complete';
            $CurrentBlock = $this->$methodName($CurrentBlock);
        }

        if (isset($CurrentBlock)) {
            $Elements[] = $this->_extractElement($CurrentBlock);
        }
        return $Elements;
    }

    protected function _extractElement($component) {
        if (!isset($component['element'])) {
            if (isset($component['markup'])) {
                $component['element'] = array('rawHtml' => $component['markup']);
            } elseif (isset($component['hidden'])) {
                $component['element'] = array();
            }
        }
        return $component['element'];
    }

    protected function _isBlockContinuable($type) {
        return method_exists($this, '_block' . $type . 'Continue');
    }

    protected function _isBlockCompletable($type) {
        return method_exists($this, '_block' . $type . 'Complete');
    }

    protected function _blockCode($Line, $block = null) {
        if (isset($block) and $block['type'] === 'Paragraph' and ! isset($block['interrupted'])) {
            return;
        }

        if ($Line['indent'] >= 4) {
            $text = substr($Line['body'], 4);
            $block = array(
                'element' => array(
                    'name' => 'pre',
                    'element' => array(
                        'name' => 'code',
                        'text' => $text,
                    ),
                ),
            );
            return $block;
        }
    }

    protected function _blockCodeContinue($line, $block) {
        if ($line['indent'] >= 4) {
            if (isset($block['interrupted'])) {
                $block['element']['element']['text'] .= str_repeat("\n", $block['interrupted']);

                unset($block['interrupted']);
            }
            $block['element']['element']['text'] .= "\n";
            $text = substr($line['body'], 4);
            $block['element']['element']['text'] .= $text;
            return $block;
        }
    }

    protected function _blockCodeComplete($block) {
        return $block;
    }

    protected function _blockComment($line) {
        if ($this->_markupEscaped or $this->_safeMode) {
            return;
        }

        if (strpos($line['text'], '<!--') === 0) {
            $Block = array(
                'element' => array(
                    'rawHtml' => $line['body'],
                    'autobreak' => true,
                ),
            );

            if (strpos($line['text'], '-->') !== false) {
                $Block['closed'] = true;
            }

            return $Block;
        }
    }

    protected function _blockCommentContinue($line, array $block) {
        if (isset($block['closed'])) {
            return;
        }

        $block['element']['rawHtml'] .= "\n" . $line['body'];

        if (strpos($line['text'], '-->') !== false) {
            $block['closed'] = true;
        }

        return $block;
    }

    protected function _blockFencedCode($line) {
        $marker = $line['text'][0];
        $openerLength = strspn($line['text'], $marker);
        if ($openerLength < 3) {
            return;
        }
        $infostring = trim(substr($line['text'], $openerLength), "\t ");
        if (strpos($infostring, '`') !== false) {
            return;
        }
        $element = array(
            'name' => 'code',
            'text' => '',
        );
        if ($infostring !== '') {
            /**
             * //www.w3.org/TR/2011/WD-html5-20110525/elements.html#classes
             * Every HTML element may have a class attribute specified.
             * The attribute, if specified, must have a value that is a set
             * of space-separated tokens representing the various classes
             * that the element belongs to.
             * [...]
             * The space characters, for the purposes of this specification,
             * are U+0020 SPACE, U+0009 CHARACTER TABULATION (tab),
             * U+000A LINE FEED (LF), U+000C FORM FEED (FF), and
             * U+000D CARRIAGE RETURN (CR).
             */
            $language = substr($infostring, 0, strcspn($infostring, " \t\n\f\r"));
            $element['attributes'] = array('class' => "language-$language");
        }
        return array(
            'char' => $marker,
            'openerLength' => $openerLength,
            'element' => array(
                'name' => 'pre',
                'element' => $element,
            ),
        );
    }

    protected function _blockFencedCodeContinue($line, $block) {
        if (isset($block['complete'])) {
            return;
        }
        if (isset($block['interrupted'])) {
            $block['element']['element']['text'] .= str_repeat("\n", $block['interrupted']);
            unset($block['interrupted']);
        }
        if (($len = strspn($line['text'], $block['char'])) >= $block['openerLength'] and chop(substr($line['text'], $len), ' ') === '') {
            $block['element']['element']['text'] = substr($block['element']['element']['text'], 1);
            $block['complete'] = true;
            return $block;
        }
        $block['element']['element']['text'] .= "\n" . $line['body'];
        return $block;
    }

    protected function _blockFencedCodeComplete($block) {
        return $block;
    }

    protected function _blockHeader($line) {
        $level = strspn($line['text'], '#');
        if ($level > 6) {
            return;
        }
        $text = trim($line['text'], '#');
        if ($this->_strictMode and isset($text[0]) and $text[0] !== ' ') {
            return;
        }
        $text = trim($text, ' ');
        return array(
            'element' => array(
                'name' => 'h' . $level,
                'handler' => array(
                    'function' => '_lineElements',
                    'argument' => $text,
                    'destination' => 'elements',
                )
            ),
        );
    }

    protected function _blockList($line, $currentBlock = null) {
        list($name, $pattern) = $line['text'][0] <= '-' ? array('ul', '[*+-]') : array('ol', '[0-9]{1,9}+[.\)]');
        if (preg_match('/^(' . $pattern . '([ ]++|$))(.*+)/', $line['text'], $matches)) {
            $contentIndent = strlen($matches[2]);
            if ($contentIndent >= 5) {
                $contentIndent -= 1;
                $matches[1] = substr($matches[1], 0, -$contentIndent);
                $matches[3] = str_repeat(' ', $contentIndent) . $matches[3];
            } elseif ($contentIndent === 0) {
                $matches[1] .= ' ';
            }

            $markerWithoutWhitespace = strstr($matches[1], ' ', true);

            $block = array(
                'indent' => $line['indent'],
                'pattern' => $pattern,
                'data' => array(
                    'type' => $name,
                    'marker' => $matches[1],
                    'markerType' => ($name === 'ul' ? $markerWithoutWhitespace : substr($markerWithoutWhitespace, -1)),
                ),
                'element' => array(
                    'name' => $name,
                    'elements' => array(),
                ),
            );
            $block['data']['markerTypeRegex'] = preg_quote($block['data']['markerType'], '/');

            if ($name === 'ol') {
                $listStart = ltrim(strstr($matches[1], $block['data']['markerType'], true), '0') ?: '0';
                if ($listStart !== '1') {
                    if (
                        isset($currentBlock)
                        and $currentBlock['type'] === 'Paragraph'
                        and ! isset($currentBlock['interrupted'])
                    ) {
                        return;
                    }

                    $block['element']['attributes'] = array('start' => $listStart);
                }
            }

            $block['li'] = array(
                'name' => 'li',
                'handler' => array(
                    'function' => '_li',
                    'argument' => !empty($matches[3]) ? array($matches[3]) : array(),
                    'destination' => 'elements'
                )
            );
            $block['element']['elements'] [] = & $block['li'];
            return $block;
        }
    }

    protected function _blockListContinue($line, $block) {
        if (isset($block['interrupted']) and empty($block['li']['handler']['argument'])) {
            return null;
        }

        $requiredIndent = ($block['indent'] + strlen($block['data']['marker']));
        if ($line['indent'] < $requiredIndent
            and (
            (
            $block['data']['type'] === 'ol'
            and preg_match('/^[0-9]++' . $block['data']['markerTypeRegex'] . '(?:[ ]++(.*)|$)/', $line['text'], $matches)
            ) or (
            $block['data']['type'] === 'ul'
            and preg_match('/^' . $block['data']['markerTypeRegex'] . '(?:[ ]++(.*)|$)/', $line['text'], $matches)
            )
            )
        ) {
            if (isset($block['interrupted'])) {
                $block['li']['handler']['argument'] [] = '';
                $block['loose'] = true;
                unset($block['interrupted']);
            }

            unset($block['li']);

            $text = isset($matches[1]) ? $matches[1] : '';
            $block['indent'] = $line['indent'];
            $block['li'] = array(
                'name' => 'li',
                'handler' => array(
                    'function' => '_li',
                    'argument' => array($text),
                    'destination' => 'elements'
                )
            );

            $block['element']['elements'] [] = & $block['li'];
            return $block;
        } elseif ($line['indent'] < $requiredIndent and $this->_blockList($line)) {
            return null;
        }

        if ($line['text'][0] === '[' and $this->_blockReference($line)) {
            return $block;
        }

        if ($line['indent'] >= $requiredIndent) {
            if (isset($block['interrupted'])) {
                $block['li']['handler']['argument'] [] = '';
                $block['loose'] = true;
                unset($block['interrupted']);
            }

            $text = substr($line['body'], $requiredIndent);
            $block['li']['handler']['argument'] [] = $text;
            return $block;
        }

        if (!isset($block['interrupted'])) {
            $text = preg_replace('/^[ ]{0,' . $requiredIndent . '}+/', '', $line['body']);
            $block['li']['handler']['argument'] [] = $text;
            return $block;
        }
    }

    protected function _blockListComplete($block) {
        if (isset($block['loose'])) {
            foreach ($block['element']['elements'] as &$li) {
                if (end($li['handler']['argument']) !== '') {
                    $li['handler']['argument'] [] = '';
                }
            }
        }
        return $block;
    }

    protected function _blockQuote($line) {
        if (preg_match('/^>[ ]?+(.*+)/', $line['text'], $matches)) {
            return array(
                'element' => array(
                    'name' => 'blockquote',
                    'handler' => array(
                        'function' => '_linesElements',
                        'argument' => (array) $matches[1],
                        'destination' => 'elements',
                    )
                ),
            );
        }
    }

    protected function _blockQuoteContinue($line, $block) {
        if (isset($block['interrupted'])) {
            return;
        }
        if ($line['text'][0] === '>' and preg_match('/^>[ ]?+(.*+)/', $line['text'], $matches)) {
            $block['element']['handler']['argument'] [] = $matches[1];
            return $block;
        }
        if (!isset($block['interrupted'])) {
            $block['element']['handler']['argument'] [] = $line['text'];
            return $block;
        }
    }

    protected function _blockRule($line) {
        $marker = $line['text'][0];
        if (substr_count($line['text'], $marker) >= 3 and chop($line['text'], " $marker") === '') {
            return array(
                'element' => array(
                    'name' => 'hr',
                ),
            );
        }
    }

    protected function _blockSetextHeader($line, $block = null) {
        if (!isset($block) or $block['type'] !== 'Paragraph' or isset($block['interrupted'])) {
            return;
        }

        if ($line['indent'] < 4 and chop(chop($line['text'], ' '), $line['text'][0]) === '') {
            $block['element']['name'] = $line['text'][0] === '=' ? 'h1' : 'h2';
            return $block;
        }
    }

    protected function _blockMarkup($line) {
        if ($this->_markupEscaped or $this->_safeMode) {
            return;
        }
        if (preg_match('/^<[\/]?+(\w*)(?:[ ]*+' . $this->_regexHtmlAttribute . ')*+[ ]*+(\/)?>/', $line['text'], $matches)) {
            $element = strtolower($matches[1]);
            if (in_array($element, $this->_textLevelElements)) {
                return;
            }
            return array(
                'name' => $matches[1],
                'element' => array(
                    'rawHtml' => $line['text'],
                    'autobreak' => true,
                ),
            );
        }
    }

    protected function _blockMarkupContinue($line, $block) {
        if (isset($block['closed']) or isset($block['interrupted'])) {
            return;
        }
        $block['element']['rawHtml'] .= "\n" . $line['body'];
        return $block;
    }

    protected function _blockReference($line) {
        if (strpos($line['text'], ']') !== false and preg_match('/^\[(.+?)\]:[ ]*+<?(\S+?)>?(?:[ ]+["\'(](.+)["\')])?[ ]*+$/', $line['text'], $matches)) {
            $id = strtolower($matches[1]);
            $this->_definitionData['Reference'][$id] = array(
                'url' => $matches[2],
                'title' => isset($matches[3]) ? $matches[3] : null,
            );
            return array(
                'element' => array(),
            );
        }
    }

    protected function _blockTable($line, $block = null) {
        if (!isset($block) or $block['type'] !== 'Paragraph' or isset($block['interrupted'])) {
            return;
        }
        if (
            strpos($block['element']['handler']['argument'], '|') === false
            and strpos($line['text'], '|') === false
            and strpos($line['text'], ':') === false
            or strpos($block['element']['handler']['argument'], "\n") !== false
        ) {
            return;
        }
        if (chop($line['text'], ' -:|') !== '') {
            return;
        }
        $alignments = array();
        $divider = $line['text'];
        $divider = trim($divider);
        $divider = trim($divider, '|');
        $dividerCells = explode('|', $divider);
        foreach ($dividerCells as $dividerCell) {
            $dividerCell = trim($dividerCell);
            if ($dividerCell === '') {
                return;
            }
            $alignment = null;
            if ($dividerCell[0] === ':') {
                $alignment = 'left';
            }
            if (substr($dividerCell, - 1) === ':') {
                $alignment = $alignment === 'left' ? 'center' : 'right';
            }
            $alignments [] = $alignment;
        }

        $headerElements = array();
        $header = $block['element']['handler']['argument'];
        $header = trim($header);
        $header = trim($header, '|');
        $headerCells = explode('|', $header);
        if (count($headerCells) !== count($alignments)) {
            return;
        }
        foreach ($headerCells as $index => $headerCell) {
            $headerCell = trim($headerCell);
            $headerElement = array(
                'name' => 'th',
                'handler' => array(
                    'function' => '_lineElements',
                    'argument' => $headerCell,
                    'destination' => 'elements',
                )
            );
            if (isset($alignments[$index])) {
                $alignment = $alignments[$index];
                $headerElement['attributes'] = array(
                    'style' => "text-align: $alignment;",
                );
            }
            $headerElements [] = $headerElement;
        }

        $block = array(
            'alignments' => $alignments,
            'identified' => true,
            'element' => array(
                'name' => 'table',
                'elements' => array(),
            ),
        );

        $block['element']['elements'] [] = array(
            'name' => 'thead',
        );

        $block['element']['elements'] [] = array(
            'name' => 'tbody',
            'elements' => array(),
        );

        $block['element']['elements'][0]['elements'] [] = array(
            'name' => 'tr',
            'elements' => $headerElements,
        );

        return $block;
    }

    protected function _blockTableContinue($line, $block) {
        if (isset($block['interrupted'])) {
            return;
        }

        if (count($block['alignments']) === 1 or $line['text'][0] === '|' or strpos($line['text'], '|')) {
            $elements = array();
            $row = $line['text'];
            $row = trim($row);
            $row = trim($row, '|');
            preg_match_all('/(?:(\\\\[|])|[^|`]|`[^`]++`|`)++/', $row, $matches);
            $cells = array_slice($matches[0], 0, count($block['alignments']));
            foreach ($cells as $index => $cell) {
                $cell = trim($cell);

                $element = array(
                    'name' => 'td',
                    'handler' => array(
                        'function' => '_lineElements',
                        'argument' => $cell,
                        'destination' => 'elements',
                    )
                );

                if (isset($block['alignments'][$index])) {
                    $element['attributes'] = array(
                        'style' => 'text-align: ' . $block['alignments'][$index] . ';',
                    );
                }
                $elements [] = $element;
            }

            $block['element']['elements'][1]['elements'] [] = array(
                'name' => 'tr',
                'elements' => $elements,
            );

            return $block;
        }
    }
    
    protected function _paragraph($line) {
        return array(
            'type' => 'Paragraph',
            'element' => array(
                'name' => 'p',
                'handler' => array(
                    'function' => '_lineElements',
                    'argument' => $line['text'],
                    'destination' => 'elements',
                ),
            ),
        );
    }

    protected function _paragraphContinue($line, $block) {
        if (isset($block['interrupted'])) {
            return;
        }
        $block['element']['handler']['argument'] .= "\n" . $line['text'];
        return $block;
    }

    protected function _lineElements($text, $nonNestables = array()) {
        // Standardize line breaks
        $text = str_replace(array("\r\n", "\r"), "\n", $text);
        $elements = array();
        $nonNestables = (empty($nonNestables) ? array() : array_combine($nonNestables, $nonNestables));

        // $excerpt is based on the first occurrence of a marker
        while ($excerpt = strpbrk($text, $this->_inlineMarkerList)) {
            $marker = $excerpt[0];
            $markerPosition = strlen($text) - strlen($excerpt);
            $Excerpt = array('text' => $excerpt, 'context' => $text);
            foreach ($this->_inlineTypes[$marker] as $inlineType) {
                // Check to see if the current inline type is nestable in the current context
                if (isset($nonNestables[$inlineType])) {
                    continue;
                }
                $inline = $this->{"_inline$inlineType"}($Excerpt);
                if (!isset($inline)) {
                    continue;
                }

                // Makes sure that the inline belongs to "our" marker
                if (isset($inline['position']) and $inline['position'] > $markerPosition) {
                    continue;
                }

                // Sets a default inline position
                if (!isset($inline['position'])) {
                    $inline['position'] = $markerPosition;
                }

                // Cause the new element to 'inherit' our non nestables
                $inline['element']['nonNestables'] = isset($inline['element']['nonNestables']) ? array_merge($inline['element']['nonNestables'], $nonNestables) : $nonNestables;

                // The text that comes before the inline
                $unmarkedText = substr($text, 0, $inline['position']);

                // Compile the unmarked text
                $inlineText = $this->_inlineText($unmarkedText);
                $elements[] = $inlineText['element'];

                // Compile the inline
                $elements[] = $this->_extractElement($inline);

                // Remove the examined text
                $text = substr($text, $inline['position'] + $inline['extent']);
                continue 2;
            }

            // The marker does not belong to an inline
            $unmarkedText = substr($text, 0, $markerPosition + 1);

            $inlineText = $this->_inlineText($unmarkedText);
            $elements[] = $inlineText['element'];
            $text = substr($text, $markerPosition + 1);
        }

        $inlineText = $this->_inlineText($text);
        $elements[] = $inlineText['element'];
        foreach ($elements as &$element) {
            if (!isset($element['autobreak'])) {
                $element['autobreak'] = false;
            }
        }

        return $elements;
    }

    protected function _inlineText($text) {
        $inline = array(
            'extent' => strlen($text),
            'element' => array(),
        );

        $inline['element']['elements'] = self::_pregReplaceElements(
            $this->_breaksEnabled 
                ? '/[ ]*+\n/' 
                : '/(?:[ ]*+\\\\|[ ]{2,}+)\n/', 
            array(
                array('name' => 'br'),
                array('text' => "\n"),
            ), 
            $text
        );

        return $inline;
    }

    protected function _inlineCode($excerpt) {
        $marker = $excerpt['text'][0];
        if (preg_match('/^([' . $marker . ']++)[ ]*+(.+?)[ ]*+(?<![' . $marker . '])\1(?!' . $marker . ')/s', $excerpt['text'], $matches)) {
            $text = $matches[2];
            $text = preg_replace('/[ ]*+\n/', ' ', $text);
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'code',
                    'text' => $text,
                ),
            );
        }
    }

    protected function _inlineEmailTag($excerpt) {
        $hostnameLabel = '[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?';
        $commonMarkEmail = '[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]++@' . $hostnameLabel . '(?:\.' . $hostnameLabel . ')*';
        if (strpos($excerpt['text'], '>') !== false and preg_match("/^<((mailto:)?$commonMarkEmail)>/i", $excerpt['text'], $matches)) {
            $url = $matches[1];
            if (!isset($matches[2])) {
                $url = "mailto:$url";
            }
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $matches[1],
                    'attributes' => array(
                        'href' => $url,
                        'target' => '_blank',
                    ),
                ),
            );
        }
    }

    protected function _inlineEmphasis($excerpt) {
        if (!isset($excerpt['text'][1])) {
            return;
        }
        $marker = $excerpt['text'][0];
        if ($excerpt['text'][1] === $marker and preg_match($this->_strongRegex[$marker], $excerpt['text'], $matches)) {
            $emphasis = 'strong';
        } elseif (preg_match($this->_emRegex[$marker], $excerpt['text'], $matches)) {
            $emphasis = 'em';
        } else {
            return;
        }
        return array(
            'extent' => strlen($matches[0]),
            'element' => array(
                'name' => $emphasis,
                'handler' => array(
                    'function' => '_lineElements',
                    'argument' => $matches[1],
                    'destination' => 'elements',
                )
            ),
        );
    }

    protected function _inlineEscapeSequence($excerpt) {
        if (isset($excerpt['text'][1]) and in_array($excerpt['text'][1], $this->_specialCharacters)) {
            return array(
                'element' => array('rawHtml' => $excerpt['text'][1]),
                'extent' => 2,
            );
        }
    }

    protected function _inlineImage($excerpt) {
        if (!isset($excerpt['text'][1]) or $excerpt['text'][1] !== '[') {
            return;
        }
        $excerpt['text'] = substr($excerpt['text'], 1);
        $link = $this->_inlineLink($excerpt);
        if ($link === null) {
            return;
        }
        $inline = array(
            'extent' => $link['extent'] + 1,
            'element' => array(
                'name' => 'img',
                'attributes' => array(
                    'src' => $link['element']['attributes']['href'],
                    'alt' => $link['element']['handler']['argument'],
                ),
                'autobreak' => true,
            ),
        );
        $inline['element']['attributes'] += $link['element']['attributes'];
        unset($inline['element']['attributes']['href']);
        return $inline;
    }

    protected function _inlineLink($excerpt) {
        $element = array(
            'name' => 'a',
            'handler' => array(
                'function' => '_lineElements',
                'argument' => null,
                'destination' => 'elements',
            ),
            'nonNestables' => array('Url', 'Link'),
            'attributes' => array(
                'href' => null,
                'title' => null,
                'target' => '_blank',
            ),
        );
        $extent = 0;
        $remainder = $excerpt['text'];
        if (preg_match('/\[((?:[^][]++|(?R))*+)\]/', $remainder, $matches)) {
            $element['handler']['argument'] = $matches[1];
            $extent += strlen($matches[0]);
            $remainder = substr($remainder, $extent);
        } else {
            return;
        }
        if (preg_match('/^[(]\s*+((?:[^ ()]++|[(][^ )]+[)])++)(?:[ ]+("[^"]*+"|\'[^\']*+\'))?\s*+[)]/', $remainder, $matches)) {
            $element['attributes']['href'] = $matches[1];

            if (isset($matches[2])) {
                $element['attributes']['title'] = substr($matches[2], 1, - 1);
            }

            $extent += strlen($matches[0]);
        } else {
            if (preg_match('/^\s*\[(.*?)\]/', $remainder, $matches)) {
                $definition = strlen($matches[1]) ? $matches[1] : $element['handler']['argument'];
                $definition = strtolower($definition);

                $extent += strlen($matches[0]);
            } else {
                $definition = strtolower($element['handler']['argument']);
            }

            if (!isset($this->_definitionData['Reference'][$definition])) {
                return;
            }

            $Definition = $this->_definitionData['Reference'][$definition];
            $element['attributes']['href'] = $Definition['url'];
            $element['attributes']['title'] = $Definition['title'];
        }

        return array(
            'extent' => $extent,
            'element' => $element,
        );
    }

    protected function _inlineMarkup($excerpt) {
        if ($this->_markupEscaped or $this->_safeMode or strpos($excerpt['text'], '>') === false) {
            return;
        }

        if ($excerpt['text'][1] === '/' and preg_match('/^<\/\w[\w-]*+[ ]*+>/s', $excerpt['text'], $matches)) {
            return array(
                'element' => array('rawHtml' => $matches[0]),
                'extent' => strlen($matches[0]),
            );
        }

        if ($excerpt['text'][1] === '!' and preg_match('/^<!---?[^>-](?:-?+[^-])*-->/s', $excerpt['text'], $matches)) {
            return array(
                'element' => array('rawHtml' => $matches[0]),
                'extent' => strlen($matches[0]),
            );
        }

        if ($excerpt['text'][1] !== ' ' and preg_match('/^<\w[\w-]*+(?:[ ]*+' . $this->_regexHtmlAttribute . ')*+[ ]*+\/?>/s', $excerpt['text'], $matches)) {
            return array(
                'element' => array('rawHtml' => $matches[0]),
                'extent' => strlen($matches[0]),
            );
        }
    }

    protected function _inlineSpecialCharacter($excerpt) {
        if (substr($excerpt['text'], 1, 1) !== ' ' and strpos($excerpt['text'], ';') !== false
            and preg_match('/^&(#?+[0-9a-zA-Z]++);/', $excerpt['text'], $matches)
        ) {
            return array(
                'element' => array('rawHtml' => '&' . $matches[1] . ';'),
                'extent' => strlen($matches[0]),
            );
        }
    }

    protected function _inlineStrikethrough($excerpt) {
        if (!isset($excerpt['text'][1])) {
            return;
        }
        if ($excerpt['text'][1] === '~' and preg_match('/^~~(?=\S)(.+?)(?<=\S)~~/', $excerpt['text'], $matches)) {
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'del',
                    'handler' => array(
                        'function' => '_lineElements',
                        'argument' => $matches[1],
                        'destination' => 'elements',
                    )
                ),
            );
        }
    }

    protected function _inlineUrl($excerpt) {
        if (!$this->_urlsLinked or !isset($excerpt['text'][2]) or $excerpt['text'][2] !== '/') {
            return;
        }

        if (strpos($excerpt['context'], 'http') !== false
            and preg_match('/\bhttps?+:[\/]{2}[^\s<]+\b\/*+/ui', $excerpt['context'], $matches, PREG_OFFSET_CAPTURE)
        ) {
            $url = $matches[0][0];
            return array(
                'extent' => strlen($matches[0][0]),
                'position' => $matches[0][1],
                'element' => array(
                    'name' => 'a',
                    'text' => $url,
                    'attributes' => array(
                        'href' => $url,
                        'target' => '_blank',
                    ),
                ),
            );
        }
    }

    protected function _inlineUrlTag($excerpt) {
        if (strpos($excerpt['text'], '>') !== false and preg_match('/^<(\w++:\/{2}[^ >]++)>/i', $excerpt['text'], $matches)) {
            $url = $matches[1];
            return array(
                'extent' => strlen($matches[0]),
                'element' => array(
                    'name' => 'a',
                    'text' => $url,
                    'attributes' => array(
                        'href' => $url,
                        'target' => '_blank',
                    ),
                ),
            );
        }
    }

    protected function _unmarkedText($text) {
        $inline = $this->_inlineText($text);
        return $this->_element($inline['element']);
    }

    protected function _handle($element) {
        if (isset($element['handler'])) {
            if (!isset($element['nonNestables'])) {
                $element['nonNestables'] = array();
            }

            if (is_string($element['handler'])) {
                $function = $element['handler'];
                $argument = $element['text'];
                unset($element['text']);
                $destination = 'rawHtml';
            } else {
                $function = $element['handler']['function'];
                $argument = $element['handler']['argument'];
                $destination = $element['handler']['destination'];
            }

            $element[$destination] = $this->{$function}($argument, $element['nonNestables']);

            if ($destination === 'handler') {
                $element = $this->_handle($element);
            }
            unset($element['handler']);
        }
        return $element;
    }

    protected function _handleElementRecursive($element) {
        return $this->_elementApplyRecursive(array($this, '_handle'), $element);
    }

    protected function _handleElementsRecursive($elements) {
        return $this->_elementsApplyRecursive(array($this, '_handle'), $elements);
    }

    protected function _elementApplyRecursive($closure, $element) {
        $element = call_user_func($closure, $element);
        if (isset($element['elements'])) {
            $element['elements'] = $this->_elementsApplyRecursive($closure, $element['elements']);
        } elseif (isset($element['element'])) {
            $element['element'] = $this->_elementApplyRecursive($closure, $element['element']);
        }
        return $element;
    }

    protected function _elementApplyRecursiveDepthFirst($closure, $element) {
        if (isset($element['elements'])) {
            $element['elements'] = $this->_elementsApplyRecursiveDepthFirst($closure, $element['elements']);
        } elseif (isset($element['element'])) {
            $element['element'] = $this->_elementsApplyRecursiveDepthFirst($closure, $element['element']);
        }
        return call_user_func($closure, $element);
    }

    protected function _elementsApplyRecursive($closure, $elements) {
        foreach ($elements as &$element) {
            $element = $this->_elementApplyRecursive($closure, $element);
        }
        return $elements;
    }

    protected function _elementsApplyRecursiveDepthFirst($closure, $elements) {
        foreach ($elements as &$element) {
            $element = $this->_elementApplyRecursiveDepthFirst($closure, $element);
        }
        return $elements;
    }

    protected function _element($element) {
        if ($this->_safeMode) {
            $element = $this->_sanitiseElement($element);
        }

        // Identity map if element has no handler
        $element = $this->_handle($element);
        $hasName = isset($element['name']);
        $markup = '';
        if ($hasName) {
            $markup .= '<' . $element['name'];
            if (isset($element['attributes'])) {
                foreach ($element['attributes'] as $name => $value) {
                    if ($value === null) {
                        continue;
                    }

                    $markup .= " $name=\"" . self::_escape($value) . '"';
                }
            }
        }
        $permitRawHtml = false;
        if (isset($element['text'])) {
            $text = $element['text'];
        }
        
        // Very strongly consider an alternative if you're writing an extension
        elseif (isset($element['rawHtml'])) {
            $text = $element['rawHtml'];
            $allowRawHtmlInSafeMode = isset($element['allowRawHtmlInSafeMode']) && $element['allowRawHtmlInSafeMode'];
            $permitRawHtml = !$this->_safeMode || $allowRawHtmlInSafeMode;
        }

        $hasContent = isset($text) || isset($element['element']) || isset($element['elements']);

        if ($hasContent) {
            $markup .= $hasName ? '>' : '';
            if (isset($element['elements'])) {
                $markup .= $this->_elements($element['elements']);
            } elseif (isset($element['element'])) {
                $markup .= $this->_element($element['element']);
            } else {
                if (!$permitRawHtml) {
                    $markup .= self::_escape($text, true);
                } else {
                    $markup .= $text;
                }
            }

            $markup .= $hasName ? '</' . $element['name'] . '>' : '';
        } elseif ($hasName) {
            $markup .= ' />';
        }
        return $markup;
    }

    protected function _elements($elements) {
        $markup = '';
        $autoBreak = true;
        foreach ($elements as $element) {
            if (empty($element)) {
                continue;
            }
            $autoBreakNext = (isset($element['autobreak']) ? $element['autobreak'] : isset($element['name']));
            
            // (autobreak === false) covers both sides of an element
            $autoBreak = !$autoBreak ? $autoBreak : $autoBreakNext;
            $markup .= ($autoBreak ? "\n" : '') . $this->_element($element);
            $autoBreak = $autoBreakNext;
        }
        $markup .= $autoBreak ? "\n" : '';
        return $markup;
    }

    protected function _li($lines) {
        $elements = $this->_linesElements($lines);

        if (!in_array('', $lines)
            and isset($elements[0]) and isset($elements[0]['name'])
            and $elements[0]['name'] === 'p'
        ) {
            unset($elements[0]['name']);
        }

        return $elements;
    }

    protected function _sanitiseElement($element) {
        static $goodAttribute = '/^[a-zA-Z0-9][a-zA-Z0-9-_]*+$/';
        static $safeUrlNameToAtt = array(
            'a' => 'href',
            'img' => 'src',
        );
        if (!isset($element['name'])) {
            unset($element['attributes']);
            return $element;
        }
        if (isset($safeUrlNameToAtt[$element['name']])) {
            $element = $this->_filterUnsafeUrlInAttribute($element, $safeUrlNameToAtt[$element['name']]);
        }

        if (!empty($element['attributes'])) {
            foreach ($element['attributes'] as $att => $val) {
                // Filter out badly parsed attribute
                if (!preg_match($goodAttribute, $att)) {
                    unset($element['attributes'][$att]);
                }
                // Dump onevent attribute
                elseif (self::_striAtStart($att, 'on')) {
                    unset($element['attributes'][$att]);
                }
            }
        }
        return $element;
    }

    protected function _filterUnsafeUrlInAttribute($element, $attribute) {
        foreach ($this->_safeLinksWhitelist as $scheme) {
            if (self::_striAtStart($element['attributes'][$attribute], $scheme)) {
                return $element;
            }
        }
        $element['attributes'][$attribute] = str_replace(':', '%3A', $element['attributes'][$attribute]);
        return $element;
    }
}

/* EOF */