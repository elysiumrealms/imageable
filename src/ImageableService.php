<?php

namespace Elysiumrealms\Imageable;


class ImageableService
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * Create a new ImageableService instance.
     *
     * @param \Illuminate\Foundation\Application $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Resolve the URL to the new domain.
     *
     * @param string $url
     * @return string
     */
    public function resolve($url)
    {
        if (!config('imageable.proxy.enabled'))
            return $url;

        $proxy = config('imageable.proxy.url');
        $host = parse_url($proxy, PHP_URL_HOST);
        $port = parse_url($proxy, PHP_URL_PORT);
        $scheme = parse_url($proxy, PHP_URL_SCHEME);
        $path = implode(
            '/',
            [
                parse_url($proxy, PHP_URL_PATH),
                config('imageable.directory'),
                basename($url)
            ]
        );

        return "{$scheme}://{$host}" . ($port ? ":{$port}" : '') . $path;
    }
}
