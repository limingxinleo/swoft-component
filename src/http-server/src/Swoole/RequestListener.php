<?php

namespace Swoft\Http\Server\Swoole;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\BeanFactory;
use Swoft\Http\Message\Request as ServerRequest;
use Swoft\Http\Message\Response as ServerResponse;
use Swoft\Http\Server\HttpDispatcher;
use Swoft\Server\Swoole\RequestInterface;
use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * Class RequestListener
 *
 * @Bean()
 *
 * @since 2.0
 */
class RequestListener implements RequestInterface
{
    /**
     * @param Request  $request
     * @param Response $response
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \ReflectionException
     */
    public function onRequest(Request $request, Response $response): void
    {
        // $response->end('<h1>Hello Swoole. </h1>');
        // \Swoft::trigger('some.event');
        // return;

        $psrRequest  = ServerRequest::new($request);
        // return; // QPS: 2.3w -> 3.32w
        $psrResponse = ServerResponse::new($response);
        // return; // QPS: 2.3w -> 3.31w

        /* @var HttpDispatcher $dispatcher */
        $dispatcher = BeanFactory::getSingleton('httpDispatcher');
        // return; // QPS: 3.2w
        $dispatcher->dispatch($psrRequest, $psrResponse);
    }
}
