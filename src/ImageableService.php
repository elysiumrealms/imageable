<?php

namespace Elysiumrealms\Imageable;

use Illuminate\Http\Request;

class ImageableService
{
    /**
     * The redirects.
     *
     * @var array
     */
    protected $redirects = [];

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

        $this->redirects[env('APP_URL')] = request()->header('host');
    }

    /**
     * Advertise a new domain.
     *
     * @param string|array $to
     * @param string|null $from
     * @return $this
     */
    public function advertise($to, $from = null)
    {
        $from = $from ?? $this->app
            ->config->get('app.url');

        $from = preg_replace(
            '/^https?:\/\//',
            '',
            $from
        );

        if (is_array($to)) {
            $this->redirects = array_merge(
                $this->redirects,
                $to,
            );
        } else {
            $this->redirects[$from] = $to;
        }

        return $this;
    }

    /**
     * Resolve the URL to the new domain.
     *
     * @param string $url
     * @return string
     */
    public function resolve($url)
    {
        foreach ($this->redirects as $from => $to) {
            if ($to instanceof \Closure)
                $to = $to();
            if (!str_contains($url, $from))
                continue;
            $path = parse_url($url, PHP_URL_PATH);
            $query = parse_url($url, PHP_URL_QUERY);
            $scheme = parse_url($url, PHP_URL_SCHEME);
            $fragment = parse_url($url, PHP_URL_FRAGMENT);
            return $scheme . '://' . $to . $path
                . ($query ? '?' . $query : '')
                . ($fragment ? '#' . $fragment : '');
        }

        return $url;
    }
}
