<?php declare(strict_types = 1);

namespace Vairogs\Component\Utils\Helper;

use InvalidArgumentException;
use ReflectionException;
use Symfony\Component\HttpFoundation\Request;
use Vairogs\Component\Utils\Annotation;
use function preg_match;
use function sprintf;
use function str_starts_with;

class Http
{
    /**
     * @var int
     */
    public const HTTP = 80;
    /**
     * @var int
     */
    public const HTTPS = 443;
    /**
     * @var string
     */
    private const HEADER_HTTPS = 'HTTPS';
    /**
     * @var string
     */
    private const HEADER_PORT = 'SERVER_PORT';
    /**
     * @var string
     */
    private const HEADER_SSL = 'HTTP_X_FORWARDED_SSL';
    /**
     * @var string
     */
    private const HEADER_PROTO = 'HTTP_X_FORWARDED_PROTO';

    /**
     * @param Request $request
     *
     * @return string
     * @Annotation\TwigFilter()
     * @Annotation\TwigFunction()
     */
    public static function getSchema(Request $request): string
    {
        return self::isHttps($request) ? 'https://' : 'http://';
    }

    /**
     * @param Request $request
     *
     * @return bool
     * @Annotation\TwigFunction()
     */
    public static function isHttps(Request $request): bool
    {
        $checks = [
            self::HEADER_HTTPS,
            self::HEADER_PORT,
            self::HEADER_SSL,
            self::HEADER_PROTO,
        ];
        foreach ($checks as $check) {
            $function = sprintf('check%s', Text::toCamelCase($check));
            if (self::{$function}($request)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Request $request
     * @param bool $trust
     * @return string
     * @Annotation\TwigFunction()
     */
    public static function getRemoteIp(Request $request, bool $trust = false): string
    {
        if (!$trust) {
            return $request->server->get('REMOTE_ADDR');
        }

        $parameters = [
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
        ];

        foreach ($parameters as $parameter) {
            if ($request->server->has($parameter)) {
                return $request->server->get($parameter);
            }
        }

        return $request->server->get('REMOTE_ADDR');
    }

    /**
     * @param Request $request
     * @return string
     * @Annotation\TwigFunction()
     */
    public static function getRemoteIpCF(Request $request): string
    {
        if ($request->server->has('HTTP_CF_CONNECTING_IP')) {
            return $request->server->get('HTTP_CF_CONNECTING_IP');
        }

        return $request->server->get('REMOTE_ADDR');
    }

    /**
     * @param string $path
     *
     * @return bool
     * @Annotation\TwigFunction()
     */
    public static function isAbsolute(string $path): bool
    {
        return str_starts_with($path, '//') || preg_match('#^[a-z-]{3,}://#i', $path);
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @Annotation\TwigFunction()
     */
    public static function getMethods(): array
    {
        return Iter::arrayValuesFiltered(Php::getClassConstants(Request::class), 'METHOD_');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected static function checkHttps(Request $request): bool
    {
        return $request->server->has(self::HEADER_HTTPS) && 'on' === $request->server->get(self::HEADER_HTTPS);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected static function checkServerPort(Request $request): bool
    {
        return $request->server->has(self::HEADER_PORT) && self::HTTPS === (int)$request->server->get(self::HEADER_PORT);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected static function checkHttpXForwardedSsl(Request $request): bool
    {
        return $request->server->has(self::HEADER_SSL) && 'on' === $request->server->get(self::HEADER_SSL);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    protected static function checkHttpXForwardedProto(Request $request): bool
    {
        return $request->server->has(self::HEADER_PROTO) && 'https' === $request->server->get(self::HEADER_PROTO);
    }
}
