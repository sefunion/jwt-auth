<?php

namespace Easy\JWTAuth\Middleware;

use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Easy\JWTAuth\Util\JWTUtil;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Easy\JWTAuth\JWT;
use Easy\JWTAuth\Exception\TokenValidException;

/**
 * Class JWTAuthSceneDefaultMiddleware
 * @package Easy\JWTAuth\Middleware
 */
class JWTAuthSceneDefaultMiddleware implements MiddlewareInterface
{
    /**
     * @var HttpResponse
     */
    protected $response;

    protected $prefix = 'Bearer';

    protected $jwt;

    public function __construct(HttpResponse $response, JWT $jwt)
    {
        $this->response = $response;
        $this->jwt = $jwt;
    }

    /**
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Throwable
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $isValidToken = false;
        // 根据具体业务判断逻辑走向，这里假设用户携带的token有效
        $token = $request->getHeaderLine('Authorization') ?? '';
        if (strlen($token) > 0) {
            $token = JWTUtil::handleToken($token);
            // 验证该token是否为default场景配置生成的
            if ($token !== false && $this->jwt->setScene('default')->checkToken($token, true, true, true)) {
                $isValidToken = true;
            }
        }
        if ($isValidToken) {
            return $handler->handle($request);
        }

        throw new TokenValidException('Token authentication does not pass', 401);
    }
}
