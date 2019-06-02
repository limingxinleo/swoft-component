<?php declare(strict_types=1);

namespace SwoftTest\Http\Server\Testing\Controller;

use Swoft\Http\Message\Response;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;

/**
 * Class TestController
 *
 * @Controller("/fixture/test")
 */
class TestController
{
    /**
     * @RequestMapping()
     * @return string
     */
    public function hello(): string
    {
        return 'hello';
    }

    /**
     * @RequestMapping()
     *
     * @param Response $response
     *
     * @return Response
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function cookie(Response $response): Response
    {
        return $response->setCookie('ck', 'val')->withContent('hello');
    }
}
