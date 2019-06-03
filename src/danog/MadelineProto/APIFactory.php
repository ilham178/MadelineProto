<?php

/**
 * APIFactory module.
 *
 * This file is part of MadelineProto.
 * MadelineProto is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * MadelineProto is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU General Public License along with MadelineProto.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2019 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPLv3
 *
 * @link      https://docs.madelineproto.xyz MadelineProto documentation
 */

namespace danog\MadelineProto;

use Amp\Promise;
use danog\MadelineProto\Async\AsyncConstruct;

class APIFactory extends AsyncConstruct
{
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var langpack
     */
    public $langpack;
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var phone
     */
    public $phone;
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var stickers
     */
    public $stickers;
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var payments
     */
    public $payments;
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var bots
     */
    public $bots;
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var channels
     */
    public $channels;
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var help
     */
    public $help;
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var upload
     */
    public $upload;
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var photos
     */
    public $photos;
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var updates
     */
    public $updates;
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var messages
     */
    public $messages;
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var contacts
     */
    public $contacts;
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var users
     */
    public $users;
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var account
     */
    public $account;
    /**
     * @internal this is a internal property generated by build_docs.php, don't change manually
     *
     * @var auth
     */
    public $auth;

    use Tools;
    public $namespace = '';
    public $API;
    public $lua = false;
    public $async = false;
    public $asyncAPIPromise;

    protected $methods = [];

    public function __construct($namespace, $API, &$async)
    {
        $this->namespace = $namespace.'.';
        $this->API = $API;
        $this->async = &$async;
    }

    public function __call($name, $arguments)
    {
        $yielded = $this->call($this->__call_async($name, $arguments));
        $async = $this->lua === false && (is_array(end($arguments)) && isset(end($arguments)['async']) ? end($arguments)['async'] : ($this->async && $name !== 'loop'));
        if ($async) {
            return $yielded;
        }
        if (!$this->lua) {
            return $this->wait($yielded);
        }

        try {
            $yielded = $this->wait($yielded);
            Lua::convert_objects($yielded);

            return $yielded;
        } catch (\Throwable $e) {
            return ['error_code' => $e->getCode(), 'error' => $e->getMessage()];
        }
    }

    public function __call_async($name, $arguments)
    {
        if ($this->asyncInitPromise) {
            yield $this->initAsync();
            $this->API->logger->logger("Finished init asynchronously");
        }
        if (Magic::is_fork() && !Magic::$processed_fork) {
            throw new Exception("Forking not supported, use async logic, instead: https://docs.madelineproto.xyz/docs/ASYNC.html");
        }
        if (isset($this->session) && !is_null($this->session) && time() - $this->serialized > $this->API->settings['serialization']['serialization_interval']) {
            Logger::log("Didn't serialize in a while, doing that now...");
            $this->serialize($this->session);
        }
        if ($this->API->setdem) {
            $this->API->setdem = false;
            $this->API->__construct($this->API->settings);
            yield $this->API->initAsync();
        }
        if ($this->API->asyncInitPromise) {
            yield $this->API->initAsync();
            $this->API->logger->logger("Finished init asynchronously");
        }

        $lower_name = strtolower($name);
        if ($this->namespace !== '' || !isset($this->methods[$lower_name])) {
            $name = $this->namespace.$name;
            $aargs = isset($arguments[1]) && is_array($arguments[1]) ? $arguments[1] : [];
            $aargs['apifactory'] = true;
            $aargs['datacenter'] = $this->API->datacenter->curdc;
            $args = isset($arguments[0]) && is_array($arguments[0]) ? $arguments[0] : [];

            return yield $this->API->method_call_async_read($name, $args, $aargs);
        } else {
            return yield $this->methods[$lower_name](...$arguments);
        }
    }

    public function &__get($name)
    {
        if ($this->asyncAPIPromise) {
            $this->wait($this->asyncAPIPromise);
        }
        if ($name === 'settings') {
            $this->API->setdem = true;

            return $this->API->settings;
        }
        if ($name === 'logger') {
            return $this->API->logger;
        }

        return $this->API->storage[$name];
    }

    public function __set($name, $value)
    {
        if ($this->asyncAPIPromise) {
            $this->wait($this->asyncAPIPromise);
        }
        if ($name === 'settings') {
            if ($this->API->asyncInitPromise) {
                $this->API->init();
            }
            return $this->API->__construct(array_replace_recursive($this->API->settings, $value));
        }

        return $this->API->storage[$name] = $value;
    }

    public function __isset($name)
    {
        if ($this->asyncAPIPromise) {
            $this->wait($this->asyncAPIPromise);
        }
        return isset($this->API->storage[$name]);
    }

    public function __unset($name)
    {
        if ($this->asyncAPIPromise) {
            $this->wait($this->asyncAPIPromise);
        }
        unset($this->API->storage[$name]);
    }

}
