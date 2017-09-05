<?php

namespace dlds\mlm\tests\unit;

use Codeception\Util\Stub;
use dlds\mlm\kernel\MlmPocket;
use dlds\mlm\kernel\MlmPocketItem;

class MlmPocketTest extends \Codeception\Test\Unit
{
    /**
     * @var \dlds\mlm\tests\UnitTester
     */
    protected $tester;

    /**
     * Tests pocket item adding and popping
     */
    public function testAddPop()
    {
        $pocket = MlmPocket::instance();

        $pocket->disablePersistence();

        $pocket->clear();

        $item = Stub::make(MlmPocketItem::class);

        $pocket->add($item);

        $popped = $pocket->pop();

        verify($item)->equals($popped);
    }

    /**
     * Tests pocket item size methods
     */
    public function testSize()
    {
        $item = Stub::make(MlmPocketItem::class);

        $pocket = MlmPocket::instance();
        $pocket->disablePersistence();
        $pocket->clear();

        verify($pocket->size())->equals(0);
        verify($pocket->isEmpty())->true();

        $pocket->add($item);

        verify($pocket->size())->equals(1);
        verify($pocket->isEmpty())->false();

        $pocket->add($item);

        verify($pocket->size())->equals(2);
        verify($pocket->isEmpty())->false();

        $pocket->add($item);

        verify($pocket->size())->equals(3);
        verify($pocket->isEmpty())->false();

        $pocket->pop();

        verify($pocket->size())->equals(2);
        verify($pocket->isEmpty())->false();

        $pocket->clear();

        verify($pocket->size())->equals(0);
        verify($pocket->isEmpty())->true();
    }

    /**
     * Tests pocket persistence
     */
    public function testPersistence()
    {
        $item = Stub::make(MlmPocketItem::class);

        $pocket = MlmPocket::instance();
        $pocket->enablePersistence();

        verify($pocket->size())->equals(0);
        verify($pocket->isEmpty())->true();

        $pocket->add($item);

        verify($pocket->size())->equals(1);
        verify($pocket->isEmpty())->false();

        $pocket->add($item);

        verify($pocket->size())->equals(2);
        verify($pocket->isEmpty())->false();

        $pocket->add($item);

        verify($pocket->size())->equals(3);
        verify($pocket->isEmpty())->false();

        $pocket->clear();

        verify($pocket->size())->equals(3);
        verify($pocket->isEmpty())->false();

        $pocket->disablePersistence();

        $pocket->clear();

        verify($pocket->size())->equals(0);
        verify($pocket->isEmpty())->true();
    }

}