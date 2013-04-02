<?php
/**
 * Generated by PHPUnit_SkeletonGenerator on 2013-02-17 at 00:43:47.
 */
class AddCourseToSessionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AddCourseToSession
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        require_once dirname(__FILE__).'/../../../main/inc/global.inc.php';
        $this->object = new AddCourseToSession;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * Generated from @assert () !== null.
     *
     * @covers AddCourseToSession::search_courses
     */
    public function testSearch_courses()
    {
        $this->assertNotSame(
          null,
          $this->object->search_courses()
        );
    }

    /**
     * Generated from @assert ('abc', 'single') !== null.
     *
     * @covers AddCourseToSession::search_courses
     */
    public function testSearch_courses2()
    {
        $this->assertNotSame(
          null,
          $this->object->search_courses('abc', 'single')
        );
    }

    /**
     * Generated from @assert ('abc', 'multiple') !== null.
     *
     * @covers AddCourseToSession::search_courses
     */
    public function testSearch_courses3()
    {
        $this->assertNotSame(
          null,
          $this->object->search_courses('abc', 'multiple')
        );
    }
}