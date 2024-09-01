<?php

namespace App\Jobs\Traits;

trait ErrorCodeDefaultContentTrait
{

    public function getErrorCodeDefaultContent($errorCode)
    {
        $method = "getError{$errorCode}Content";
        if (method_exists($this, $method)) {
            return $this->$method();
        }
    }

    public function getError400Content(): string
    {
        return "<h1>400 Bad Request</h1>\n
                <h2>If the problem continues, contact the site owner</h2>";
    }

    public function getError401Content(): string
    {
        return "<h1>401 Unauthorized</h1>\n
            <h2>You do not have permission to access this resource.</h2>";
    }

    public function getError403Content(): string
    {
        return "<h1>403 Forbidden</h1>\n
            <h2>You do not have permission to access this resource.</h2>";
    }

    public function getError404Content(): string
    {
        return "<h1>404 Not Found</h1>\n
            <h2>The page you are looking for could not be found.</h2>";
    }

    public function getError405Content(): string
    {
        return "<h1>405 Method Not Allowed</h1>\n
            <h2>The HTTP method used for this request is not allowed.</h2>";
    }

    public function getError406Content(): string
    {
        return "<h1>406 Not Acceptable</h1>\n
            <h2>The requested resource is not capable of generating content acceptable according to the Accept headers sent in the request.</h2>";
    }

    public function getError407Content(): string
    {
        return "<h1>407 Proxy Authentication Required</h1>\n
            <h2>Authentication with the proxy server is required to access the requested resource.</h2>";
    }

    public function getError408Content(): string
    {
        return "<h1>408 Request Timeout</h1>\n
            <h2>The server timed out waiting for the request.</h2>";
    }

    public function getError409Content(): string
    {
        return "<h1>409 Conflict</h1>\n
            <h2>The request could not be completed due to a conflict with the current state of the resource.</h2>";
    }

    public function getError410Content(): string
    {
        return "<h1>410 Gone</h1>\n
            <h2>The requested resource is no longer available and has been permanently removed.</h2>";
    }

    public function getError411Content(): string
    {
        return "<h1>411 Length Required</h1>\n
            <h2>The server refuses to accept the request without a defined Content-Length header.</h2>";
    }

    public function getError412Content(): string
    {
        return "<h1>412 Precondition Failed</h1>\n
            <h2>The server does not meet one of the preconditions specified in the request headers.</h2>";
    }

    public function getError413Content(): string
    {
        return "<h1>413 Request Entity Too Large</h1>\n
            <h2>The request entity is larger than the server is willing or able to process.</h2>";
    }

    public function getError414Content(): string
    {
        return "<h1>414 Request-URI Too Long</h1>\n
            <h2>The requested URI (Uniform Resource Identifier) is too long for the server to process.</h2>";
    }

    public function getError415Content(): string
    {
        return "<h1>415 Unsupported Media Type</h1>\n
            <h2>The media type of the requested data is not supported by the server.</h2>";
    }

    public function getError416Content(): string
    {
        return "<h1>416 Requested Range Not Satisfiable</h1>\n
            <h2>The range specified in the `Range` header is not satisfiable.</h2>";
    }

    public function getError417Content(): string
    {
        return "<h1>417 Expectation Failed</h1>\n
            <h2>The expectation given in the `Expect` request header could not be met by the server.</h2>";
    }

    public function getError422Content(): string
    {
        return "<h1>422 Unprocessable Entity</h1>\n
            <h2>The server understands the content type of the request entity, but was unable to process the contained instructions.</h2>";
    }

    public function getError423Content(): string
    {
        return "<h1>423 Locked</h1>\n
            <h2>The resource you are trying to access is currently locked and cannot be modified.</h2>";
    }

    public function getError424Content(): string
    {
        return "<h1>424 Failed Dependency</h1>\n
            <h2>The request failed because it depended on another request that failed.</h2>";
    }

    public function getError500Content(): string
    {
        return "<h1>500 Internal Server Error</h1>\n
            <h2>There was an unexpected error on the server side.</h2>";
    }

    public function getError501Content(): string
    {
        return "<h1>501 Not Implemented</h1>\n
            <h2>The server does not support the functionality required to fulfill the request.</h2>";
    }

    public function getError502Content(): string
    {
        return "<h1>502 Bad Gateway</h1>\n
            <h2>The server received an invalid response from an upstream server.</h2>";
    }

    public function getError503Content(): string
    {
        return "<h1>503 Service Unavailable</h1>\n
            <h2>The server is currently unable to handle the request.</h2>";
    }

    public function getError504Content(): string
    {
        return "<h1>504 Gateway Timeout</h1>\n
            <h2>The server did not receive a timely response from an upstream server.</h2>";
    }

    public function getError505Content(): string
    {
        return "<h1>505 HTTP Version Not Supported</h1>\n
            <h2>The server does not support the HTTP protocol version that was used in the request.</h2>";
    }

    public function getError506Content(): string
    {
        return "<h1>506 Variant Also Negotiates</h1>\n
            <h2>The server encountered an internal configuration error.</h2>";
    }

    public function getError507Content(): string
    {
        return "<h1>507 Insufficient Storage</h1>\n
            <h2>The server is unable to store the representation needed to complete the request.</h2>";
    }

    public function getError508Content(): string
    {
        return "<h1>508 Not Extended</h1>\n
            <h2>The server is unable to fulfill the request due to an extension requirement.</h2>";
    }
}
