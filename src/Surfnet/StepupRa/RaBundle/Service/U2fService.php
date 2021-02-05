<?php

/**
 * Copyright 2015 SURFnet B.V.
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

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use RuntimeException as CoreRuntimeException;
use Surfnet\StepupBundle\Http\JsonHelper;
use Surfnet\StepupRa\RaBundle\Command\CreateU2fSignRequestCommand;
use Surfnet\StepupRa\RaBundle\Command\VerifyU2fAuthenticationCommand;
use Surfnet\StepupRa\RaBundle\Service\U2f\AuthenticationVerificationResult;
use Surfnet\StepupRa\RaBundle\Service\U2f\SignRequestCreationResult;
use Surfnet\StepupU2fBundle\Dto\SignRequest;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity) -- We're verifying a JSON format. Not much to do towards reducing the
 *     complexity.
 */
final class U2fService
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzleClient;

    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    private $validator;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(Client $guzzleClient, ValidatorInterface $validator, LoggerInterface $logger)
    {
        $this->guzzleClient = $guzzleClient;
        $this->validator    = $validator;
        $this->logger       = $logger;
    }

    /**
     * @param CreateU2fSignRequestCommand $command
     * @return SignRequestCreationResult
     */
    public function createSignRequest(CreateU2fSignRequestCommand $command): SignRequestCreationResult
    {
        $this->logger->info('Create U2F sign request');

        $body = [
            'requester' => ['institution' => $command->institution, 'identity' => $command->identityId],
            'key_handle' => ['value' => $command->keyHandle],
        ];

        $response = $this->guzzleClient->post('api/u2f/create-sign-request', ['json' => $body, 'http_errors' => false]);
        $statusCode = $response->getStatusCode();

        try {
            $result = JsonHelper::decode((string) $response->getBody());
        } catch (CoreRuntimeException $e) {
            $this->logger->error('U2F sign request creation failed; JSON decoding failed.');

            return SignRequestCreationResult::apiError();
        }

        $hasErrors = isset($result['errors'])
            && is_array($result['errors'])
            && $result['errors'] === array_filter($result['errors'], 'is_string');

        if ($hasErrors && $statusCode >= 400 && $statusCode < 600) {
            $this->logger->critical(
                sprintf(
                    'U2F sign request creation failed; HTTP %d with errors "%s"',
                    $statusCode,
                    join(', ', $result['errors'])
                )
            );

            return SignRequestCreationResult::apiError();
        }

        $actualKeys = array_keys($result);
        $expectedKeys = ['app_id', 'challenge', 'key_handle', 'version'];
        if ($statusCode != 200 || array_diff($actualKeys, $expectedKeys) !== array_diff($expectedKeys, $actualKeys)) {
            $this->logger->critical(
                sprintf(
                    'U2F API behaving nonconformingly, returned response or status code (%d) unexpected',
                    $statusCode
                )
            );

            return SignRequestCreationResult::apiError();
        }

        $signRequest = new SignRequest();
        $signRequest->appId = $result['app_id'];
        $signRequest->challenge = $result['challenge'];
        $signRequest->keyHandle = $result['key_handle'];
        $signRequest->version = $result['version'];

        $violations = $this->validator->validate($signRequest);
        if (count($violations) > 0) {
            $this->logger->critical(
                sprintf(
                    'U2F API behaving nonconformingly, returned sign request does not validate',
                    $statusCode
                ),
                ['errors' => $this->mapViolationsToErrorStrings($violations, 'sign_request')]
            );

            return SignRequestCreationResult::apiError();
        }

        return SignRequestCreationResult::success($signRequest);
    }

    /**
     * @param VerifyU2fAuthenticationCommand $command
     * @return AuthenticationVerificationResult
     */
    public function verifyAuthentication(VerifyU2fAuthenticationCommand $command): AuthenticationVerificationResult
    {
        $this->logger->info('Create U2F sign request');

        $body = [
            'requester' => ['institution' => $command->institution, 'identity' => $command->identityId],
            'authentication' => [
                'request' => [
                    'key_handle' => $command->signRequest->keyHandle,
                    'version'    => $command->signRequest->version,
                    'challenge'  => $command->signRequest->challenge,
                    'app_id'     => $command->signRequest->appId,
                ],
                'response' => [
                    'error_code'     => $command->signResponse->errorCode,
                    'key_handle'     => $command->signResponse->keyHandle,
                    'client_data'    => $command->signResponse->clientData,
                    'signature_data' => $command->signResponse->signatureData,
                ],
            ],
        ];

        $response = $this->guzzleClient->post(
            'api/u2f/verify-authentication',
            ['json' => $body, 'http_errors' => false]
        );
        $statusCode = $response->getStatusCode();

        try {
            $result = JsonHelper::decode((string) $response->getBody());
        } catch (CoreRuntimeException $e) {
            $this->logger->error('U2F authentication verification failed; JSON decoding failed.');

            return AuthenticationVerificationResult::apiError();
        }

        $hasErrors = isset($result['errors'])
            && is_array($result['errors'])
            && $result['errors'] === array_filter($result['errors'], 'is_string');

        if ($hasErrors && $statusCode >= 400 && $statusCode < 600) {
            $this->logger->critical(
                sprintf(
                    'U2F authentication verification failed; HTTP %d with errors "%s"',
                    $statusCode,
                    join(', ', $result['errors'])
                )
            );

            return AuthenticationVerificationResult::apiError();
        }

        $hasStatus = isset($result['status']) && is_string($result['status']);

        if ($statusCode == 200 && $hasStatus && $result['status'] === 'SUCCESS') {
            return AuthenticationVerificationResult::success();
        }

        if ($statusCode >= 400 && $statusCode < 500 && $hasStatus) {
            return AuthenticationVerificationResult::error($result['status']);
        }

        $this->logger->critical(
            sprintf(
                'U2F API behaving nonconformingly, returned response or status code (%d) unexpected',
                $statusCode
            )
        );

        return AuthenticationVerificationResult::apiError();
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @param string $rootName
     * @return string[]
     */
    private function mapViolationsToErrorStrings(ConstraintViolationListInterface $violations, string $rootName): array
    {
        $errors = [];

        foreach ($violations as $violation) {
            /** @var ConstraintViolationInterface $violation */
            $errors[] = sprintf(
                '%s.%s: %s',
                $rootName,
                $violation->getPropertyPath(),
                (string) $violation->getMessage()
            );
        }

        return $errors;
    }
}
