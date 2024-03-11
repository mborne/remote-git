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
     */
    private string $type;

    /**
     * Base URL (ex : https://gitlab.com)
     */
    private string $url;

    /**
     * Access token
     */
    private ?string $token;

    /**
     * Bypass SSL certificate checks for self signed certificates
     */
    private bool $unsafeSsl;

    public function __construct()
    {
        $this->unsafeSsl = false;
    }

    /**
     * True if client type is specified
     */
    public function hasType(): bool
    {
        return !empty($this->type);
    }

    /**
     * Get client type
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set client type (ex : github, gitlab-v4,...)
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get URL
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Set URL
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Is token defined?
     */
    public function hasToken(): bool
    {
        return !empty($this->token);
    }

    /**
     * Get access token
     */
    public function getToken(): ?string
    {
        return $this->token;
    }

    /**
     * Set access token
     */
    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Is unsafeSsl
     */
    public function isUnsafeSsl(): bool
    {
        return $this->unsafeSsl;
    }

    /**
     * Set unsafeSsl
     */
    public function setUnsafeSsl(bool $unsafeSsl): self
    {
        $this->unsafeSsl = $unsafeSsl;

        return $this;
    }
}
