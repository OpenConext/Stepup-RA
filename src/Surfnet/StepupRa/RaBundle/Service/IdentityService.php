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

namespace Surfnet\StepupRa\RaBundle\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Surfnet\StepupBundle\Command\SwitchLocaleCommand;
use Surfnet\StepupMiddlewareClient\Identity\Dto\IdentitySearchQuery;
use Surfnet\StepupMiddlewareClientBundle\Identity\Command\ExpressLocalePreferenceCommand;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity;
use Surfnet\StepupMiddlewareClientBundle\Identity\Dto\RegistrationAuthorityCredentials;
use Surfnet\StepupMiddlewareClientBundle\Identity\Service\IdentityService as ApiIdentityService;
use Surfnet\StepupRa\RaBundle\Exception\RuntimeException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class IdentityService implements UserProviderInterface
{
    public function __construct(
        private readonly ApiIdentityService $apiIdentityService,
        private readonly CommandService $commandService,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * For now this functionality is disabled, unsure if actually needed
     *
     * If needed, the username is the UUID of the identity so it can be fetched rather easy
     */
    public function loadUserByIdentifier(string $identifier): never
    {
        throw new RuntimeException(sprintf('Cannot Load User By Username "%s"', $identifier));
    }

    /**
     * For now this functionality is disabled, unsure if actually needed
     */
    public function refreshUser(UserInterface $user)
    {
        throw new RuntimeException(sprintf('Cannot Refresh User "%s"', $user->getUsername()));
    }

    /**
     * Whether this provider supports the given user class
     */
    public function supportsClass($class): bool
    {
        return $class === Identity::class;
    }

    /**
     * @param string $identityId the UUID of the identity to find
     */
    public function findById(string $identityId): ?Identity
    {
        return $this->apiIdentityService->get($identityId);
    }

    /**
     * @throws RuntimeException
     */
    public function findByNameIdAndInstitution(string $nameId, string $institution): ?Identity
    {
        $searchQuery = new IdentitySearchQuery();
        $searchQuery->setNameId($nameId);
        $searchQuery->setInstitution($institution);

        try {
            $result = $this->apiIdentityService->search($searchQuery);
        } catch (Exception $e) {
            $message = sprintf('Exception when searching identity: "%s"', $e->getMessage());
            $this->logger->critical($message);
            throw new RuntimeException($message, 0, $e);
        }

        $elements = $result->getElements();
        if (count($elements) === 0) {
            return null;
        }

        if (count($elements) === 1) {
            return reset($elements);
        }

        throw new RuntimeException(sprintf(
            'Got an unexpected amount of identities, expected 0 or 1, got "%d"',
            count($elements),
        ));
    }

    public function getRaCredentials(Identity $identity): ?RegistrationAuthorityCredentials
    {
        try {
            $credentials = $this->apiIdentityService->getRegistrationAuthorityCredentials($identity);
        } catch (Exception $e) {
            $message = sprintf('Exception when retrieving RA credentials: "%s"', $e->getMessage());
            $this->logger->critical($message);

            throw new RuntimeException($message, 0, $e);
        }

        return $credentials;
    }

    public function switchLocale(SwitchLocaleCommand $command): bool
    {
        $token = $this->tokenStorage->getToken();

        if (!$token) {
            throw new RuntimeException('Cannot switch locales when unauthenticated');
        }

        /** @var Identity $identity */
        $identity = $token->getUser();

        $expressLocalePreferenceCommand = new ExpressLocalePreferenceCommand();
        $expressLocalePreferenceCommand->identityId = $command->identityId;
        $expressLocalePreferenceCommand->preferredLocale = $command->locale;

        $result = $this->commandService->execute($expressLocalePreferenceCommand);

        if ($result->isSuccessful()) {
            $identity->preferredLocale = $command->locale;
        }

        return $result->isSuccessful();
    }
}
