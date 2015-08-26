# Full Text Search for PHP on Google App Engine #

This library provides native PHP access to the Google App Engine Search API.

At the time of writing there is no off-the-shelf way to access the Google App Engine full text search API from the PHP runtime.

Generally this means developers cannot access the service without using [Python/Java/Go proxy modules](https://github.com/tomwalder/phpne14-text-search) - which adds complexity, another language, additional potential points of failure and performance impact.

**PRE-ALPHA** This library is in the very early stages of development. Do not use it in production. It will change.

## Table of Contents ##

- [Examples](#examples)
- [Queries](#queries)
- [Local Development](#local-development-environment)
- [Google Software](#google-software)

## Examples ##

I find examples a great way to decide if I want to even try out a library, so here's a couple for you. 

```php
// Schema describing a book
$obj_schema = (new \Search\Schema())
    ->addText('title')
    ->addText('author')
    ->addAtom('isbn')
    ->addNumber('price');
        
// Create and populate a document
$obj_book = $obj_schema->createDocument();
$obj_book->title = 'Romeo and Juliet';
$obj_book->author = 'William Shakespeare';
$obj_book->isbn = '1840224339';
$obj_book->price = 9.99;
    
// Write it to the Index
$obj_index = new \Search\Index('library');
$obj_index->put($obj_book);
```

You can also use the [Alternative Array Syntax](#alternative-array-syntax) for creating Documents from Schemas, like this

```php
$obj_book = $obj_schema->createDocument([
    'title' => 'The Merchant of Venice',
    'author' => 'William Shakespeare',
    'isbn' => '1840224312',
    'price' => 11.99
]);
```

Now let's do a simple search and display the output

```php
$obj_index = new \Search\Index('library');
$obj_response = $obj_index->search('romeo');
foreach($obj_response->results as $obj_result) {
    echo "Title: {$obj_result->doc->title}, ISBN: {$obj_result->doc->isbn} <br />", PHP_EOL;
}
```

## Getting Started ##

### Install with Composer ###

To install using Composer, use this require line in your `composer.json` for bleeding-edge features, dev-master

`"tomwalder/php-appengine-search": "dev-master"`

Or, if you're using the command line:

`composer require tomwalder/php-appengine-search`

You will need `minimum-stability: dev`

# Queries #

Some simple, valid query strings:
- `price:2.99`
- `romeo`

For *much* more information, see the Python reference docs: https://cloud.google.com/appengine/docs/python/search/query_strings 

# Creating Documents #

## Batch Inserts ##

It's more efficient to insert in batches if you have multiple documents. Up to 200 documents can be inserted at once.

Just pass an array of Document objects into the `Index::put()` method, like this:

```php
$obj_index = new \Search\Index('library');
$obj_index->put([$obj_book1, $obj_book2, $obj_book3]);
```

## Alternative Array Syntax ##

There is an alternative to directly constructing a new `Search\Document` and setting it's member data, which is to use the `Search\Schema::createDocument` factory method as follows.

```php
$obj_book = $obj_schema->createDocument([
    'title' => 'The Merchant of Venice',
    'author' => 'William Shakespeare',
    'isbn' => '1840224312',
    'price' => 11.99
]);
```

## Local Development Environment ##

The Search API is supported locally, because it's included to support the Python, Java and Go App Engine runtimes.

## Google Software ##

I've had to include 2 files from Google to make this work - they are the Protocol Buffer implementations for the Search API. You will find them in the `/libs` folder.

They are also available directly from the following repository: https://github.com/GoogleCloudPlatform/appengine-php-sdk

These 2 files are Copyright 2007 Google Inc.

As and when they make it into the actual live PHP runtime, I will remove them from here.

Thank you to @sjlangley for the assist.