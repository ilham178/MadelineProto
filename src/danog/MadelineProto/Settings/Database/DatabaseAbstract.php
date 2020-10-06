<?php

namespace danog\MadelineProto\Settings\Database;

use danog\MadelineProto\Settings\DatabaseAbstract as SettingsDatabaseAbstract;

/**
 * Base class for database backends.
 */
abstract class DatabaseAbstract extends SettingsDatabaseAbstract
{
    /**
     * For how long to keep records in memory after last read, for cached backends.
     * @var string //serialization issues
     */
    protected $cacheTtl = "+5 minutes";
    /**
     * Database password.
     */
    protected string $password = '';

    public function mergeArray(array $settings): void
    {
        foreach (self::toCamel([
            'database',
            'password',
            'cache_ttl'
        ]) as $object => $array) {
            if (isset($settings[$array])) {
                $this->{$object}($settings[$array]);
            }
        }
    }

    /**
     * Get DB key.
     *
     * @return string
     */
    public function getKey(): string
    {
        $uri = \parse_url($this->getUri());
        $host = $uri['host'] ?? '';
        $port = $uri['port'] ?? '';
        return "$host:$port:".$this->getDatabase();
    }

    /**
     * Get for how long to keep records in memory after last read, for cached backends.
     *
     * @return string
     */
    public function getCacheTtl(): string
    {
        return $this->cacheTtl;
    }

    /**
     * Set for how long to keep records in memory after last read, for cached backends.
     *
     * @param string $cacheTtl For how long to keep records in memory after last read, for cached backends.
     *
     * @return self
     */
    public function setCacheTtl(string $cacheTtl): self
    {
        $this->cacheTtl = $cacheTtl;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set password.
     *
     * @param string $password Password.
     *
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get database name/ID.
     *
     * @return string|int
     */
    abstract public function getDatabase();
    /**
     * Get database URI.
     *
     * @return string
     */
    abstract public function getUri(): string;

    /**
     * Set database name/ID.
     *
     * @param int|string $database
     * @return self
     */
    abstract public function setDatabase($database): self;
    /**
     * Set database URI.
     *
     * @param string $uri
     * @return self
     */
    abstract public function setUri(string $uri): self;
}
