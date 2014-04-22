<?php
/**
 * User: garyhockin
 * Date: 21/04/2014
 * Time: 18:33
 */

namespace Blitzen;


use Zend\Http\Header\MultipleHeaderInterface;
use Zend\Http\PhpEnvironment\Response;

class HttpResponseSender
{
    /**
     * @var bool
     */
    public $contentSent = false;
    public $headersSent = false;

    /**
     * Sends the Http response headers
     *
     * @param Response $response
     * @return bool
     */
    public function sendHeaders(Response $response)
    {
        if (headers_sent() || $this->headersSent) {
            return false;
        }

        foreach ($response->getHeaders() as $header) {
            if ($header instanceof MultipleHeaderInterface) {
                header($header->toString(), false);
                continue;
            }
            header($header->toString());
        }

        $status = $response->renderStatusLine();
        header($status);

        $this->headersSent = true;

        return true;
    }

    /**
     * @param Response $response
     * @return bool
     */
    public function sendContent(Response $response)
    {
        if ($this->contentSent) {
            return false;
        }
        echo $response->getContent();
        $this->contentSent = true;
        return true;
    }
} 