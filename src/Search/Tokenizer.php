<?php
/**
 * Copyright 2015 Tom Walder
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Search;

/**
 * String Tokenizer
 *
 * @author Tom Walder <tom@docnet.nu>
 */
class Tokenizer
{
    /**
     * Change an input string into a list of edge N-Gram tokens for autocomplete usage
     *
     * @param $str_phrase
     * @param $int_min_length
     * @return string
     */
    public function edgeNGram($str_phrase, $int_min_length = 1)
    {
        $arr_tokens = [];

        // Clean-up unwanted characters (assume english for now)
        $str_phrase = preg_replace('#[^a-z0-9 ]#i', '', $str_phrase);

        // @todo move this to non-edge ngram function when we have one.
        // Do we need individual characters?
        // if($int_min_length < 2) {
        //     $arr_chars = str_split($str_phrase);
        //     $arr_tokens = array_merge($arr_tokens, $arr_chars);
        // }

        // Add the words
        $arr_words = explode(' ', $str_phrase);
        $arr_tokens = array_merge($arr_tokens, $arr_words);

        // OK, now split the words
        foreach($arr_words as $str_word) {
            $int_letters = strlen($str_word);
            $arr_ngrams = [];
            for($int_subs = $int_min_length; $int_subs <= ($int_letters - 1); $int_subs++) {
                $arr_ngrams[] = substr($str_word, 0, $int_subs);
            }
            $arr_tokens = array_merge($arr_tokens, $arr_ngrams);
        }

        // Clean up and return
        return str_replace('  ', ' ', implode(' ', $arr_tokens));
    }
}