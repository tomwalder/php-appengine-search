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
require_once('../vendor/autoload.php');

try {

    // We'll need an index
    $obj_index = new \Search\Index('library');

    // Define our Book Schema
    $obj_book_schema = (new \Search\Schema())
        ->addAtom('isbn')
        ->addText('title')
        ->addText('author')
        ->addNumber('price');

    // Create Book (FROM SCHEMA)
    $obj_book1 = $obj_book_schema->createDocument();
    $obj_book1->title = 'Romeo and Juliet';
    $obj_book1->author = 'William Shakespeare';
    $obj_book1->isbn = '1840224339';
    $obj_book1->price = 9.99;

    // Insert
    $obj_index->put($obj_book1);

    echo "OK";

} catch (\Exception $obj_ex) {
    echo $obj_ex->getMessage();
    syslog(LOG_CRIT, $obj_ex->getMessage());
}