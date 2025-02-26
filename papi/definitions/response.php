<?php

namespace Papi\Definitions;

define('HTTP_INFORMATIONAL_CONTINUE', 100);
define('HTTP_INFORMATIONAL_SWITCHING_PROTOCOL', 101);
define('HTTP_INFORMATIONAL_EARLY_HINTS', 103);
define('HTTP_SUCCESS_OK', 200);
define('HTTP_SUCCESS_CREATED', 201);
define('HTTP_SUCCESS_ACCEPTED', 202);
define('HTTP_SUCCESS_NON_AUTHROITATIVE_INFORMATION', 203);
define('HTTP_SUCCESS_NO_CONTENT', 204);
define('HTTP_SUCCESS_RESET_CONTENT', 205);
define('HTTP_SUCCESS_PARTIAL_CONTENT', 206);
define('HTTP_SUCCESS_IM_USED', 226);
define('HTTP_REDIRECT_MULTIPLE_CHOICES', 300);
define('HTTP_REDIRECT_MOVED_PERMANENTLY', 301);
define('HTTP_REDIRECT_FOUND', 302);
define('HTTP_REDIRECT_SEE_OTHER', 303);
define('HTTP_REDIRECT_TEMPORARY_REDIRECT', 307);
define('HTTP_REDIRECT_PERMANENT_REDIRECT', 308);
define('HTTP_CLIENT_ERROR_BAD_REQUEST', 400);
define('HTTP_CLIENT_ERROR_UNAUTORIZED', 401);
define('HTTP_CLIENT_ERROR_PAYMENT_REQUIRED', 402);
define('HTTP_CLIENT_ERROR_FORBIDDEN', 403);
define('HTTP_CLIENT_ERROR_NOT_FOUND', 404);
define('HTTP_CLIENT_ERROR_METHOD_NOT_ALLOWED', 405);
define('HTTP_CLIENT_ERROR_NOT_ACCEPTABLE', 406);
define('HTTP_CLIENT_ERROR_PROXY_AUTHENTICATION_REQUIRED', 407);
define('HTTP_CLIENT_ERROR_REQUEST_TIMEOUT', 408);
define('HTTP_CLIENT_ERROR_CONFLICT', 409);
define('HTTP_CLIENT_ERROR_GONE', 410);
define('HTTP_CLIENT_ERROR_LENGTH_REQUIRED', 411);
define('HTTP_CLIENT_ERROR_PRECONDITON_FAILED', 412);
define('HTTP_CLIENT_ERROR_CONTENT_TOO_LARGE', 413);
define('HTTP_CLIENT_ERROR_URI_TOO_LONG', 414);
define('HTTP_CLIENT_ERROR_UNSUPPORTED_MEDIA_TYPE', 415);
define('HTTP_CLIENT_ERROR_RANGE_NOT_SATISFIABLE', 416);
define('HTTP_CLIENT_ERROR_EXPECTATION_FAILED', 417);
define('HTTP_CLIENT_ERROR_IM_A_TEAPOT', 418);
define('HTTP_CLIENT_ERROR_MISDIRECTED_REQUEST', 421);
define('HTTP_CLIENT_ERROR_TOO_EARLY', 425);
define('HTTP_CLIENT_ERROR_UPGRADE_REQUIRED', 426);
define('HTTP_CLIENT_ERROR_PRECONDITION_REQUIRED', 428);
define('HTTP_CLIENT_ERROR_TOO_MANY_REQUESTS', 429);
define('HTTP_CLIENT_ERROR_REQUEST_HEADER_FIELDS_TOO_LARGE', 431);
define('HTTP_CLIENT_ERROR_UNAVAILABLE_FOR_LEGAL_REASONS', 451);
define('HTTP_SERVER_ERROR_INTERNAL_SERVER_ERROR', 500);
define('HTTP_SERVER_ERROR_NOT_IMPLEMENTED', 501);
define('HTTP_SERVER_ERROR_BAD_GATEWAY', 502);
define('HTTP_SERVER_ERROR_SERVICE_UNAVAILABLE', 503);
define('HTTP_SERVER_ERROR_GATEWAY_TIMEOUT', 504);
define('HTTP_SERVER_ERROR_HTTP_VERSION_NOT_SUPPORTED', 505);
define('HTTP_SERVER_ERROR_VARIANT_ALSO_NEGOTIATES', 506);
define('HTTP_SERVER_ERROR_NOT_EXTENDED', 510);
define('HTTP_SERVER_ERROR_NETWORK_AUTHENTICATION_REQUIRED', 511);

class Response
{
    private $response;

    /**
     * Default constructor for Response object
     */
    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * Get the int that corresponds to the defined response
     *
     * @return int
     */
    public function get()
    {
        return $this->response;
    }
}
