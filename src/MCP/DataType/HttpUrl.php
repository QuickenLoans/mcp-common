<?php
/**
 * @copyright ©2005—2013 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace MCP\DataType;

/**
 * Represents an HTTP URL.
 *
 * This object has support for http and https urls.
 *
 * @api
 */
class HttpUrl
{
    /**
     * @var boolean|null
     */
    private $secure;

    /**
     * @var string|null
     */
    private $host;

    /**
     * @var int|null
     */
    private $port;

    /**
     * @var array
     */
    private $path;

    /**
     * @var array|null
     */
    private $query;

    /**
     * Creates a new HttpUrl instance
     *
     * This is the method used parse and operate on a URL defined by RFC 2396.
     * This code is further restricting what is defined in RFC 2396 and really
     * only accepts http and https urls. As an additional limiter, absolute
     * URL's are accepted, but only relative urls that start with / or // are
     * accepted.
     *
     * Also note that a url string passed to HttpUrl::create() may not always
     * be exactly the same URL that comes from HttpUrl->asString(). URL
     * normalization happens per RFC 3986 and a few other edge cases that are
     * not related to any spec may cause these to be different.
     *
     * @param string $url
     * @return HttpUrl|null
     */
    public static function create($url)
    {
        $hack = false;
        if ($url[0] === '/' && $url[1] === '/') {
            $url = 'http:' . $url;
            $hack = true;
        }

        $bits = parse_url($url);
        if (false === $bits) {
            return null;
        }

        $scheme = isset($bits['scheme']) ? $bits['scheme'] : null;
        $secure = self::determineSecurity($scheme, $hack);

        if ($secure === 0) {
            return null;
        }

        $res = self::handleHostPort($bits);
        if (null == $res) {
            return null;
        }
        list ($host, $port) = $res;

        if ($scheme && null === $host) {
            return null;
        }

        $path = self::handlePath($bits, $host);
        if (null == $path) {
            return null;
        }

        $query = self::handleQuery($bits, $url);

        return new self($secure, $host, $port, $path, $query);
    }

    /**
     * @param $scheme
     * @param $hack
     * @return bool|int|null
     */
    private static function determineSecurity($scheme, $hack)
    {
        if ($scheme === 'https') {
            $secure = true;

            return $secure;
        } elseif (true === $hack) {
            $secure = null;

            return $secure;
        } elseif ($scheme === 'http') {
            $secure = false;

            return $secure;
        } elseif (null === $scheme) {
            $secure = null;

            return $secure;
        } else {
            $secure = 0;

            return $secure;
        }
    }


    /**
     * @param string[] $bits
     * @return array|null
     */
    private static function handleHostPort(array $bits)
    {
        $host = isset($bits['host']) ? $bits['host'] : null;
        if (!preg_match('@^([A-Za-z0-9-]+\.?)*$@', $host)) {
            return null;
        }

        $port = isset($bits['port']) ? $bits['port'] : null;
        if ($host && null === $port) {
            $port = 80;
        } elseif (null === $host) {
            $port = null;
        } else {
            $port = (int) $port;
        }

        return array($host, $port);
    }

    /**
     * @param array $bits
     * @param string|null $host
     * @return string[]|null
     */
    private static function handlePath(array $bits, $host)
    {
        $path = isset($bits['path']) ? $bits['path'] : null;
        if (null === $path && $host) {
            $path = array('', '');
        } elseif (null === $path && !$host) {
            return null;
        } else {
            $path = explode('/', $bits['path']);
        }
        if ($path[0] !== '') {
            return null;
        }
        foreach ($path as &$pathSegment) {
            if (!preg_match('@^([A-Za-z0-9_.!~*\'();:\@&=+$,-]|%[A-Fa-f0-9]{2})*$@', $pathSegment)) {
                return null;
            }
            $pathSegment = rawurldecode($pathSegment);
        }
        return $path;
    }

    /**
     * @param array $bits
     * @param string $url
     * @return string[]|null
     */
    private static function handleQuery(array $bits, $url)
    {
        $rawQuery = isset($bits['query']) ? $bits['query'] : null;
        $query = null;
        if ($rawQuery) {
            parse_str($rawQuery, $query);
        }
        if (null === $query && substr($url, -1) === '?') {
            $query = array();
        }
        return $query;
    }

    /**
     * Returns 'http' or 'https'
     *
     * @return string|null
     */
    public function protocol()
    {
        if (true === $this->secure) {
            return 'https';
        } elseif (false === $this->secure) {
            return 'http';
        } else {
            return null;
        }
    }

    /**
     * Returns true if the url is secure
     *
     * Note for relative urls, this will return null.
     *
     * @return boolean|null
     */
    public function secure()
    {
        return $this->secure;
    }

    /**
     * @return string|null
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * The port this URL is on
     *
     * This will be null if the URL does not have a host component.
     *
     * @return int|null
     */
    public function port()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function path()
    {
        $path = $this->path;
        $path = array_map('rawurlencode', $path);
        $path = implode('/', $path);
        return $path;
    }

    /**
     * The URL path separated out by path segment
     *
     * The most interesting thing about the return of this function is that it
     * will return the URL path segments that are completely url decoded.
     *
     * @return array
     */
    public function segments()
    {
        $segments = $this->path;
        array_shift($segments);
        return $segments;
    }

    /**
     * @return array|null
     */
    public function queryData()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function asString()
    {
        $url = '';

        if (true === $this->secure) {
            $url .= 'https://' . $this->host;
        } elseif (false === $this->secure) {
            $url .= 'http://' . $this->host;
        } elseif (null === $this->secure && null !== $this->host) {
            $url .= '//' . $this->host;
        }

        if ($url && 80 !== $this->port) {
            $url .= ':' . $this->port;
        }

        $url .= $this->path();

        if (null !== $this->query) {
            $url .= '?' . $this->buildQuery($this->query);
        }
        return $url;
    }

    /**
     * @param array $query
     * @return string
     */
    private function buildQuery(array $query)
    {
        $ret = array();
        foreach ($query as $name => $value) {
            if (!$value) {
                $ret[] = rawurlencode($name);
            } else {
                $ret[] = rawurlencode($name) . '=' . rawurlencode($value);
            }
        }
        return implode('&', $ret);
    }

    /**
     * @param boolean|null $secure
     * @param string|null $host
     * @param int|null $port
     * @param array $path
     * @param array|null $query
     */
    private function __construct(
        $secure = null,
        $host = null,
        $port = null,
        array $path = array(),
        array $query = null
    ) {
        $this->secure = $secure;
        $this->host = $host;
        $this->port = $port;
        $this->path = $path;
        $this->query = $query;
    }
}
