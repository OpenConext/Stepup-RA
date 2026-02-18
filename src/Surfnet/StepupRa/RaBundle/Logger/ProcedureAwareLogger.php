<?php

/**
 * Copyright 2015 SURFnet bv
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
use Stringable;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
final class ProcedureAwareLogger implements LoggerInterface
{
    private string $procedure;

    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function forProcedure(string $procedure): ProcedureAwareLogger
    {
        $logger            = new self($this->logger);
        $logger->procedure = $procedure;

        return $logger;
    }

    public function emergency(string|Stringable $message, array $context = []): void
    {
        $this->logger->emergency($message, $this->enrichContext($context));
    }

    public function alert(string|Stringable $message, array $context = []): void
    {
        $this->logger->alert($message, $this->enrichContext($context));
    }

    public function critical(string|Stringable $message, array $context = []): void
    {
        $this->logger->critical($message, $this->enrichContext($context));
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->logger->error($message, $this->enrichContext($context));
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->logger->warning($message, $this->enrichContext($context));
    }

    public function notice(string|Stringable $message, array $context = []): void
    {
        $this->logger->notice($message, $this->enrichContext($context));
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->logger->info($message, $this->enrichContext($context));
    }

    public function debug(string|Stringable $message, array $context = []): void
    {
        $this->logger->debug($message, $this->enrichContext($context));
    }

    public function log($level, string|Stringable $message, array $context = []): void
    {
        $this->logger->log($level, $message, $this->enrichContext($context));
    }

    /**
     * Adds the procedure to the log context.
     * @throws RuntimeException
     */
    private function enrichContext(array $context): array
    {
        if (!$this->procedure) {
            throw new RuntimeException('Authentication logging context is unknown');
        }

        $context['procedure'] = $this->procedure;

        return $context;
    }
}
