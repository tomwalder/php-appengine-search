# Full Text Search for PHP on Google App Engine #

This library provides native PHP access to the Google App Engine Search API.

At the time of writing there is no off-the-shelf way to access the Google App Engine full text search API from the PHP runtime.

Generally this means developers cannot access the service without using [Python/Java/Go proxy modules](https://github.com/tomwalder/phpne14-text-search) - which adds complexity, another language, additional potential points of failure and performance impact.

**PRE-ALPHA** This library is in the very early stages of development. Do not use it in production. It will change.

## Table of Contents ##

- [Examples](#examples)
- [Install with Composer](#install-with-composer)
- [Queries](#queries)
- [Creating Documents](#creating-documents) including location (Geopoint) and Dates
- [Deleting Documents](#deleting-documents)
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
$obj_book = $obj_schema->createDocument([
    'title' => 'The Merchant of Venice',
    'author' => 'William Shakespeare',
    'isbn' => '1840224312',
    'price' => 11.99
]);
    
// Write it to the Index
$obj_index = new \Search\Index('library');
$obj_index->put($obj_book);
```

In this example, I've used the [Alternative Array Syntax](#alternative-array-syntax) for creating Documents - but you can also do it like this:

```php
$obj_book = $obj_schema->createDocument();
$obj_book->title = 'Romeo and Juliet';
$obj_book->author = 'William Shakespeare';
$obj_book->isbn = '1840224339';
$obj_book->price = 9.99;
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

You can supply a simple query string to `Index::search`

```php
$obj_index->search('romeo');
```

For more control and options, you can supply a `Query` object

```php
$obj_query = (new \Search\Query($str_query))
   ->fields(['isbn', 'price'])
   ->limit(10)
   ->sort('price');
$obj_response = $obj_index->search($obj_query);
```

## Query Strings ##

Some simple, valid query strings:
- `price:2.99`
- `romeo`

Within 100 meters of a lan/lng (documents have a Geopoint field called 'place')
- `distance(place, geopoint(53.4653381,-2.1483717)) < 100`

For *much* more information, see the Python reference docs: https://cloud.google.com/appengine/docs/python/search/query_strings 

## Sorting ##

```php
$obj_query->sort('price');
```

```php
$obj_query->sort('price', Query::ASC);
```

## Limits & Offsets ##

```php
$obj_query->limit(10);
```

```php
$obj_query->offset(5);
```

## Return Fields ##

```php
$obj_query->fields(['isbn', 'price']);
```

## Get Document by ID ##

You can fetch a single document from an index directly, by it's unique Doc ID:

```php
$obj_index->get('some-document-id-here');
```

# Creating Documents #

## Schemas & Field Types ##

As per the Python docs, the available field types are

- **Atom** - an indivisible character string
- **Text** - a plain text string that can be searched word by word
- **HTML** - a string that contains HTML markup tags, only the text outside the markup tags can be searched
- **Number** - a floating point number
- **Date** - a date with year/month/day and optional time
- **Geopoint** - latitude and longitude coordinates

### Dates ###

We support `DateTime` objects or date strings in the format `YYYY-MM-DD` (PHP `date('Y-m-d')`)

```php
$obj_person_schema = (new \Search\Schema())
    ->addText('name')
    ->addDate('dob');

$obj_person = $obj_person_schema->createDocument([
    'name' => 'Marty McFly',
    'dob' => new DateTime()
]);
```

### Geopoints - Location Data ###

Create an entry with a Geopoint field

```php
$obj_pub_schema = (new \Search\Schema())
    ->addText('name')
    ->addGeopoint('where')
    ->addNumber('rating');

$obj_pub = $obj_pub_schema->createDocument([
    'name' => 'Kim by the Sea',
    'where' => [53.4653381, -2.2483717],
    'rating' => 3
]);
```

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

# Deleting Documents #

You can delete documents by calling the `Index::delete()` method.

It support one or more `Document` objects - or one or more Document ID strings - or a mixture of objects and ID strings!

```php
$obj_index = new \Search\Index('library');
$obj_index->delete('some-document-id');
$obj_index->delete([$obj_doc1, $obj_doc2]);
$obj_index->delete([$obj_doc3, 'another-document-id']);
```

# Local Development Environment #

The Search API is supported locally, because it's included to support the Python, Java and Go App Engine runtimes.

# Google Software #

I've had to include 2 files from Google to make this work - they are the Protocol Buffer implementations for the Search API. You will find them in the `/libs` folder.

They are also available directly from the following repository: https://github.com/GoogleCloudPlatform/appengine-php-sdk

These 2 files are Copyright 2007 Google Inc.

As and when they make it into the actual live PHP runtime, I will remove them from here.

Thank you to @sjlangley for the assist.
