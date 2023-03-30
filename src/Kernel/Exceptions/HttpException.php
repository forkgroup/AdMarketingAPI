<?php

declare(strict_types=1);
/**
 * @license  https://github.com/xingzhi11/AdMarketingAPI/blob/master/LICENSE
 */
namespace AdMarketingAPI\Kernel\Exceptions;

use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpException.
 */
class HttpException extends Exception
{
    /**
     * @var null|\Psr\Http\Message\ResponseInterface
     */
    public $response;

    /**
     * @var null|\AdMarketingAPI\Kernel\Support\Collection|array|object|\Psr\Http\Message\ResponseInterface|string
     */
    public $formattedResponse;

    /**
     * HttpException constructor.
     *
     * @param string $message
     * @param null $formattedResponse
     * @param null|int $code
     */
    public function __construct($message, ResponseInterface $response = null, $formattedResponse = null, $code = null)
    {
        parent::__construct($message, (int) $code);

        $this->response = $response;
        $this->formattedResponse = $formattedResponse;

        if ($response) {
            $response->getBody()->rewind();
        }
    }
}
