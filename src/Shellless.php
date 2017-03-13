<?php namespace Sukohi\Shellless;

class Shellless {

    private $_content_blocks = [];
    private $_options = [
        'join_step' => 5,
        'min_text_length' => 100
    ];

    public function __construct($options = []) {

        if(!empty($options)) {

            $this->setOptions($options);

        }

    }

    public function setOptions($options) {

        $this->_options = $options;

    }

    public function extract($raw_html) {

        $html = $this->getCorrectedHtml($raw_html);
        $html_length = mb_strlen($html);
        $tag_flag = false;
        $text = '';
        $this->_content_blocks = [];
        $step = 0;
        $prev_letter = '';

        for($i = 0; $i < $html_length; $i++){

            $letter = $html{$i};

            if($prev_letter == ' ' && $letter == ' ') {

                continue;

            }

            if($letter == '<') {

                $tag_flag = true;
                $text = trim(mb_convert_kana($text, 's'));

                if(!empty($text) && mb_strlen($text) > 0) {

                    if(substr($text, -4) == '%br%') {

                        $text = substr($text, 0, -4);

                    }

                    if($step > $this->_options['join_step']) {

                        $this->_content_blocks[] = [
                            'step' => $step,
                            'length' => mb_strlen($text),
                            'text' => str_replace('%br%', '<br>', $text)
                        ];
                        $text = '';
                        $step = 0;

                    } else if(substr($text, -4) != '%br%') {

                        $text .= '%br%';

                    }

                }

            } else if($letter == '>') {

                $tag_flag = false;
                $step++;

            } else if(!$tag_flag) {

                $text .= $letter;

            }

            $prev_letter = $letter;

        }

        return (object) [
            'title' => $this->extract_title($raw_html),
            'best_text' => $this->extract_best_text(),
            'full_text' => $this->extract_full_text(),
            'all_texts' => $this->_content_blocks
        ];

    }

    private function extract_title($html) {

        $title = '';

        if(preg_match('|<title>([^<]+)</title>|i', $html, $matches)) {

            $title = $matches[1];

        }

        return $title;

    }

    private function extract_full_text() {

        $full_text = '';

        foreach ($this->_content_blocks as $content_block) {

            $length = $content_block['length'];

            if($this->isValidText($length)) {

                $full_text .= $content_block['text'];

            }

        }

        return $full_text;

    }

    private function extract_best_text() {

        $best_text = '';
        $max_length = 0;

        foreach ($this->_content_blocks as $content_block) {

            $length = $content_block['length'];

            if($this->isValidText($length) && $length > $max_length) {

                $max_length = $content_block['length'];
                $best_text = $content_block['text'];

            }

        }

        return $best_text;

    }

    private function isValidText($length) {

        return ($length > $this->_options['min_text_length']);

    }

    private function getCorrectedHtml($html) {

        $html = str_replace(["\n", "\r", "\t"], '', $html);
        $html = preg_replace('!<br[^>]*>!i', '%br%', $html);
        $html = preg_replace('!</?(strong|b|u|i|a)[^>]*>!i', '', $html);
        $html = preg_replace('!<(head|a|script|noscript|style)[^>]*>.*?</(head|a|script|noscript|style)>!i', '', $html);
        return $html;

    }

}