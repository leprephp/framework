<?php

/*
 * This file is part of the Lepre package.
 *
 * (c) Daniele De Nobili <danieledenobili@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Lepre\Framework\Http;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Basic implementation of ResponseSenderInterface.
 *
 * @author Daniele De Nobili <danieledenobili@gmail.com>
 */
final class ResponseSender implements ResponseSenderInterface
{
    /**
     * @var array
     */
    private $reasonPhrases = [
        // 1xx
        StatusCodeInterface::STATUS_CONTINUE                        => 'Continue',
        StatusCodeInterface::STATUS_SWITCHING_PROTOCOLS             => 'Switching Protocols',
        StatusCodeInterface::STATUS_PROCESSING                      => 'Processing',

        // 2xx
        StatusCodeInterface::STATUS_OK                              => 'OK',
        StatusCodeInterface::STATUS_CREATED                         => 'Created',
        StatusCodeInterface::STATUS_ACCEPTED                        => 'Accepted',
        StatusCodeInterface::STATUS_NON_AUTHORITATIVE_INFORMATION   => 'Non-Authoritative Information',
        StatusCodeInterface::STATUS_NO_CONTENT                      => 'No Content',
        StatusCodeInterface::STATUS_RESET_CONTENT                   => 'Reset Content',
        StatusCodeInterface::STATUS_PARTIAL_CONTENT                 => 'Partial Content',
        StatusCodeInterface::STATUS_MULTI_STATUS                    => 'Multi-Status',
        StatusCodeInterface::STATUS_ALREADY_REPORTED                => 'Already Reported',
        StatusCodeInterface::STATUS_IM_USED                         => 'IM Used',

        // 3xx
        StatusCodeInterface::STATUS_MULTIPLE_CHOICES                => 'Multiple Choices',
        StatusCodeInterface::STATUS_MOVED_PERMANENTLY               => 'Moved Permanently',
        StatusCodeInterface::STATUS_FOUND                           => 'Found',
        StatusCodeInterface::STATUS_SEE_OTHER                       => 'See Other',
        StatusCodeInterface::STATUS_NOT_MODIFIED                    => 'Not Modified',
        StatusCodeInterface::STATUS_USE_PROXY                       => 'Use Proxy',
        StatusCodeInterface::STATUS_TEMPORARY_REDIRECT              => 'Temporary Redirect',
        StatusCodeInterface::STATUS_PERMANENT_REDIRECT              => 'Permanent Redirect',

        // 4xx
        StatusCodeInterface::STATUS_BAD_REQUEST                     => 'Bad Request',
        StatusCodeInterface::STATUS_UNAUTHORIZED                    => 'Unauthorized',
        StatusCodeInterface::STATUS_PAYMENT_REQUIRED                => 'Payment Required',
        StatusCodeInterface::STATUS_FORBIDDEN                       => 'Forbidden',
        StatusCodeInterface::STATUS_NOT_FOUND                       => 'Not Found',
        StatusCodeInterface::STATUS_METHOD_NOT_ALLOWED              => 'Method Not Allowed',
        StatusCodeInterface::STATUS_NOT_ACCEPTABLE                  => 'Not Acceptable',
        StatusCodeInterface::STATUS_PROXY_AUTHENTICATION_REQUIRED   => 'Proxy Authentication Required',
        StatusCodeInterface::STATUS_REQUEST_TIMEOUT                 => 'Request Timeout',
        StatusCodeInterface::STATUS_CONFLICT                        => 'Conflict',
        StatusCodeInterface::STATUS_GONE                            => 'Gone',
        StatusCodeInterface::STATUS_LENGTH_REQUIRED                 => 'Length Required',
        StatusCodeInterface::STATUS_PRECONDITION_FAILED             => 'Precondition Failed',
        StatusCodeInterface::STATUS_PAYLOAD_TOO_LARGE               => 'Request Entity Too Large',
        StatusCodeInterface::STATUS_URI_TOO_LONG                    => 'Request-URI Too Long',
        StatusCodeInterface::STATUS_UNSUPPORTED_MEDIA_TYPE          => 'Unsupported Media Type',
        StatusCodeInterface::STATUS_RANGE_NOT_SATISFIABLE           => 'Requested Range Not Satisfiable',
        StatusCodeInterface::STATUS_EXPECTATION_FAILED              => 'Expectation Failed',
        StatusCodeInterface::STATUS_IM_A_TEAPOT                     => 'I\'m a teapot',
        StatusCodeInterface::STATUS_MISDIRECTED_REQUEST             => 'Misdirected Request',
        StatusCodeInterface::STATUS_UNPROCESSABLE_ENTITY            => 'Unprocessable Entity',
        StatusCodeInterface::STATUS_LOCKED                          => 'Locked',
        StatusCodeInterface::STATUS_FAILED_DEPENDENCY               => 'Failed Dependency',
        StatusCodeInterface::STATUS_UPGRADE_REQUIRED                => 'Upgrade Required',
        StatusCodeInterface::STATUS_PRECONDITION_REQUIRED           => 'Precondition Required',
        StatusCodeInterface::STATUS_TOO_MANY_REQUESTS               => 'Too Many Requests',
        StatusCodeInterface::STATUS_REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
        StatusCodeInterface::STATUS_UNAVAILABLE_FOR_LEGAL_REASONS   => 'Permanent Redirect',

        // 5xx
        StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR           => 'Internal Server Error',
        StatusCodeInterface::STATUS_NOT_IMPLEMENTED                 => 'Not Implemented',
        StatusCodeInterface::STATUS_BAD_GATEWAY                     => 'Bad Gateway',
        StatusCodeInterface::STATUS_SERVICE_UNAVAILABLE             => 'Service Unavailable',
        StatusCodeInterface::STATUS_GATEWAY_TIMEOUT                 => 'Gateway Timeout',
        StatusCodeInterface::STATUS_VERSION_NOT_SUPPORTED           => 'HTTP Version Not Supported',
        StatusCodeInterface::STATUS_VARIANT_ALSO_NEGOTIATES         => 'Variant Also Negotiates',
        StatusCodeInterface::STATUS_INSUFFICIENT_STORAGE            => 'Insufficient Storage',
        StatusCodeInterface::STATUS_LOOP_DETECTED                   => 'Loop Detected',
        StatusCodeInterface::STATUS_NOT_EXTENDED                    => 'Not Extended',
        StatusCodeInterface::STATUS_NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
    ];

    /**
     * @inheritDoc
     */
    public function send(ResponseInterface $response)
    {
        $this->sendStatusLine($response);
        $this->sendHeaders($response);
        $this->sendContent($response);
    }

    /**
     * @param ResponseInterface $response
     */
    private function sendStatusLine(ResponseInterface $response)
    {
        $reasonPhrase = $response->getReasonPhrase();
        $statusCode = $response->getStatusCode();

        // The reason phrase is not required and may be null
        if (null === $reasonPhrase && isset($this->reasonPhrases[$statusCode])) {
            $reasonPhrase = $this->reasonPhrases[$statusCode];
        }

        header('HTTP/' . $response->getProtocolVersion() . ' ' . $statusCode . ' ' . $reasonPhrase);
    }

    /**
     * @param ResponseInterface $response
     */
    private function sendHeaders(ResponseInterface $response)
    {
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                header($name . ': ' . $value);
            }
        }
    }

    /**
     * @param ResponseInterface $response
     */
    private function sendContent(ResponseInterface $response)
    {
        echo $response->getBody();
    }
}
