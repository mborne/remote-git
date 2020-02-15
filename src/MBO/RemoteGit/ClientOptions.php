<?php

namespace MBO\RemoteGit;

/**
 * Git connection options
 *
 * @author mborne
 */
class ClientOptions
{
    /**
     * Allows to force a given client type and avoid
     * detection based on URL
     *
     * @var string
     */
    private $type;

    /**
     * Base URL (ex : https://gitlab.com)
     *
     * @var string
     */
    private $url;

    /**
     * Access token
     *
     * @var string
     */
    private $token;

    /**
     * Bypass SSL certificate checks for self signed certificates
     *
     * @var bool
     */
    private $unsafeSsl;

    public function __construct()
    {
        $this->unsafeSsl = false;
    }

    /**
     * True if client type is specificied
     *
     * @return bool
     */
    public function hasType()
    {
        return !empty($this->type);
    }

    /**
     * Get client type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set client type
     *
     * @param string $type gitlab,github,gogs,...
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set URL
     *
     * @param string $url
     *
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Is token defined?
     *
     * @return bool
     */
    public function hasToken()
    {
        return !empty($this->token);
    }

    /**
     * Get access token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set access token
     *
     * @param string $token Access token
     *
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUnsafeSsl()
    {
        return $this->unsafeSsl;
    }

    /**
     * Set unsafeSsl
     *
     * @param bool $unsafeSsl
     *
     * @return self
     */
    public function setUnsafeSsl($unsafeSsl)
    {
        $this->unsafeSsl = $unsafeSsl;

        return $this;
    }
}
