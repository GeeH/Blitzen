<?php

namespace Blitzen;


class HttpResponseSenderTest extends \PHPUnit_Framework_TestCase
{
    public function testSendHeaders()
    {
        $header = $this->getMockBuilder('Zend\Http\Headers')
            ->disableOriginalConstructor()
            ->getMock();

        $response = $this->getMockBuilder('Zend\Http\PhpEnvironment\Response')
            ->disableOriginalConstructor()
            ->setMethods(['getHeaders', 'renderStatusLine'])
            ->getMock();
        $response->expects($this->atLeastOnce())
            ->method('getHeaders')
            ->will(
                $this->returnValue(
                    [
                        $header
                    ]
                )
            );
        $response->expects($this->atLeastOnce())
            ->method('renderStatusLine')
            ->will($this->returnValue('404 Not Found'));
        $httpResponseSender = new HttpResponseSender();
        $this->assertTrue($httpResponseSender->sendHeaders($response));
        $this->assertFalse($httpResponseSender->sendHeaders($response));

    }

    public function testSendBody()
    {
        $response = $this->getMockBuilder('Zend\Http\PhpEnvironment\Response')
            ->disableOriginalConstructor()
            ->setMethods(['getContent'])
            ->getMock();

        $response->expects($this->once())
            ->method('getContent')
            ->will($this->returnValue('I see a shadow'));

        $httpResponseSender = new HttpResponseSender();

        $this->assertTrue($httpResponseSender->sendContent($response));
        $this->assertFalse($httpResponseSender->sendContent($response));
    }


    protected function setUp()
    {
    }

    // tests

    protected function tearDown()
    {
    }

}