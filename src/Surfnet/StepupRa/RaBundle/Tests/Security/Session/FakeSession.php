<?php

/**
 * Copyright 2016 SURFnet bv
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Surfnet\StepupRa\RaBundle\Tests\Security\Session;

use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;

class FakeSession implements SessionInterface
{
    /**
     * @var array
     */
    private $sessionContent = [];

    /**
     * @var string
     */
    private $sessionId = 'fake_session';

    /**
     * @var string
     */
    private $sessionName = 'fake_session';

    /**
     * @var array
     */
    private $bags = [];

    public function __construct()
    {
        $this->sessionId = bin2hex(openssl_random_pseudo_bytes(16));
    }

    /**
     * @return bool
     */
    public function start()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->sessionId;
    }

    /**
     * @param string $id
     * @return void
     */
    public function setId($id)
    {
        $this->sessionId = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->sessionName;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->sessionName = $name;
    }

    /**
     * @param int|null $lifetime
     * @return bool
     */
    public function invalidate($lifetime = null)
    {
        $this->sessionContent = [];
        $this->bags = [];
        $this->sessionId = bin2hex(openssl_random_pseudo_bytes(16));

        return true;
    }

    /**
     * @param bool $destroy
     * @param int|null $lifetime
     * @return bool
     */
    public function migrate($destroy = false, $lifetime = null)
    {
        if ($destroy) {
            $this->sessionContent = [];
            $this->bags = [];
        }

        $this->sessionId = bin2hex(openssl_random_pseudo_bytes(16));

        return true;
    }

    /**
     * @return void
     */
    public function save()
    {
        // noop
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->sessionContent);
    }

    /**
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        return $this->has($name) ? $this->sessionContent[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function set($name, $value)
    {
        $this->sessionContent[$name] = $value;
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->sessionContent;
    }

    /**
     * @param array $attributes
     * @return void
     */
    public function replace(array $attributes)
    {
        $this->sessionContent = $attributes;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function remove($name)
    {
        $return = null;
        if ($this->has($name)) {
            $return = $this->get($name);
        }

        unset($this->sessionContent[$name]);

        return $return;
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->sessionContent = [];
    }

    /**
     * @return bool
     */
    public function isStarted(): bool
    {
        return true;
    }

    public function registerBag(SessionBagInterface $bag): void
    {
        $this->bags[$bag->getName()] = $bag;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getBag($name)
    {
        if (array_key_exists($name, $this->bags)) {
            return $this->bags[$name];
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getMetadataBag()
    {
        if (isset($this->bags['_sf_meta'])) {
            return $this->bags['_sf_meta'];
        }

        return $this->bags['_sf_meta'] = new MetadataBag();
    }
}
