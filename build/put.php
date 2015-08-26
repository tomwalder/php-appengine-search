PUT<br/><pre><?php
require_once('../vendor/autoload.php');

try {

    // We'll need an index
    $obj_index = new \Search\Index('books1');

    // Define our Book Schema
    $obj_book_schema = (new \Search\Schema())
        ->addAtom('isbn')
        ->addText('title')
        ->addText('author')
        ->addNumber('price');

    // Define our Magazine Schema
    $obj_mag_schema = (new \Search\Schema())
        ->addAtom('isbn')
        ->addText('publication')
        ->addNumber('price');

    // Create Book (FROM SCHEMA)
    $obj_book1 = $obj_book_schema->createDocument();
    $obj_book1->title = 'Romeo and Juliet';
    $obj_book1->author = 'William Shakespeare';
    $obj_book1->isbn = '1840224339';
    $obj_book1->price = 9.99;

    // Create Magazine (FROM SCHEMA)
    $obj_mag1 = $obj_mag_schema->createDocument();
    $obj_mag1->publication = 'phpArchitect';
    $obj_mag1->isbn = '23984723';
    $obj_mag1->price = 2.99;

    // Create Magazine (FROM SCHEMA, using the alternative array syntax)
    $obj_mag2 = $obj_mag_schema->createDocument([
        'publication' => 'Time',
        'isbn' => '925794857398',
        'price' => 3.99
    ]);

    // Insert one, then another (the same index can hold may different schemas of document)
    $obj_index->put($obj_book1);
    $obj_index->put($obj_mag1);
    $obj_index->put($obj_mag2);

    // Create some dynamic Documents (The Schema will build up automatically as we add data)
    $obj_dyn1 = new \Search\Document();
    $obj_dyn1->name = "Marty McFly";

    // And using the alternative array syntax
    $obj_dyn2 = new \Search\Document();
    $obj_dyn2->name = 'Emmett Brown';

    // Insert 2 together (batch puts are more efficient)
    $obj_index->put([$obj_dyn1, $obj_dyn2]);

    echo "OK";

    print_r($obj_index->debug()[0]);

} catch (\Exception $obj_ex) {
    echo $obj_ex->getMessage();
    syslog(LOG_CRIT, $obj_ex->getMessage());
}