<?php

use Ytake\LaravelCouchbase\Design\AbstractDocument;

/**
 * Class DesignDocumentTest
 */
class DesignDocumentTest extends \PHPUnit\Framework\TestCase
{
    public function testShouldBeDocument()
    {
        $testDocument = new class('testing') extends AbstractDocument
        {
            protected function document(): string
            {
                return '
                    function (doc, meta) {
                      emit(meta.id, null);
                    }';
            }
        };
        $document = strval($testDocument);
        $decodeDocument = json_decode($document, true);
        $this->assertArrayHasKey('testing', $decodeDocument);
        $this->assertArrayHasKey('map', $decodeDocument['testing']);
    }
}
