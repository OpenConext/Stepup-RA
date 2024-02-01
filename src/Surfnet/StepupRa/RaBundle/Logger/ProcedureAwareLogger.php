<?php

/**
 * Copyright 2014 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Logger;

use Psr\Log\LoggerInterface;
use Surfnet\StepupRa\RaBundle\Exception\InvalidArgumentException;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class ProcedureAwareLogger implements LoggerInterface
{
    /**
     * @var string|null
     */
    private $procedure;

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function forProcedure($procedure)
    {
        if (!is_string($procedure)) {
            throw InvalidArgumentException::invalidType('string', 'procedure', $procedure);
        }

        $logger            = new self($this->logger);
        $logger->procedure = $procedure;

        return $logger;
    }

    public function emergency($message, array $context = [])
    {
        $this->logger->emergency($message, $this->enrichContext($context));
    }

    public function alert($message, array $context = [])
    {
        $this->logger->alert($message, $this->enrichContext($context));
    }

    public function critical($message, array $context = [])
    {
        $this->logger->critical($message, $this->enrichContext($context));
    }

    public function error($message, array $context = [])
    {
        $this->logger->error($message, $this->enrichContext($context));
    }

    public function warning($message, array $context = [])
    {
        $this->logger->warning($message, $this->enrichContext($context));
    }

    public function notice($message, array $context = [])
    {
        $this->logger->notice($message, $this->enrichContext($context));
    }

    public function info($message, array $context = [])
    {
        $this->logger->info($message, $this->enrichContext($context));
    }

    public function debug($message, array $context = [])
    {
        $this->logger->debug($message, $this->enrichContext($context));
    }

    public function log($level, $message, array $context = [])
    {
        $this->logger->log($message, $this->enrichContext($context));
    }


    /**
     * Adds the procedure to the log context.
     *
     * @return array
     * @throws RuntimeException
     */
    private function enrichContext(array $context)
    {
        if (!$this->procedure) {
            throw new RuntimeException('Authentication logging context is unknown');
        }

        $context['procedure'] = $this->procedure;

        return $context;
    }
}
