<?php

namespace App;

enum HttpRespones : string
{
    case OK = '200 OK';
    case CREATED = '201 Created';
    case ACCEPTED = '202 Accepted';
    case NO_CONTENT = '204 No Content';
    case BAD_REQUEST = '400 Bad Request';
    case UNAUTHORIZED = '401 Unauthorized';
    case FORBIDDEN = '403 Forbidden';
    case NOT_FOUND = '404 Not Found';
    case INTERNAL_SERVER_ERROR = '500 Internal Server Error';
    public function getMessage(): string
    {
        return match ($this) {
            self::OK => 'The request has succeeded.',
            self::CREATED => 'The request has been fulfilled and resulted in a new resource being created.',
            self::ACCEPTED => 'The request has been accepted for processing, but the processing has not been completed.',
            self::NO_CONTENT => 'The server successfully processed the request, but is not returning any content.',
            self::BAD_REQUEST => 'The server could not understand the request due to invalid syntax.',
            self::UNAUTHORIZED => 'The client must authenticate itself to get the requested response.',
            self::FORBIDDEN => 'The client does not have access rights to the content.',
            self::NOT_FOUND => 'The server can not find the requested resource.',
            self::INTERNAL_SERVER_ERROR => 'The server has encountered a situation it does not know how to handle.',
        };
    }
}

