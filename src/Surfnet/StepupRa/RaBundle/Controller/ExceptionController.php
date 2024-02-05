<?php

/**
 * Copyright 2018 SURFnet bv
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

namespace Surfnet\StepupRa\RaBundle\Controller;

use Exception;
use Surfnet\StepupBundle\Controller\ExceptionController as BaseExceptionController;
use Surfnet\StepupRa\RaBundle\Exception\LoaTooLowException;
use Surfnet\StepupRa\RaBundle\Exception\MissingRequiredAttributeException;
use Surfnet\StepupRa\RaBundle\Exception\UserNotRaException;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class ExceptionController extends BaseExceptionController
{
    /**
     * @return array View parameters 'title' and 'description'
     */
    protected function getPageTitleAndDescription(Exception $exception): array
    {
        $translator = $this->getTranslator();

        if ($exception instanceof HttpException) {
            $parent = $exception->getPrevious();

            if ($parent instanceof UserNotRaException) {
                $title = $translator->trans('stepup.error.user_not_ra.title');
                $description = $translator->trans('stepup.error.user_not_ra.description');
            } elseif ($parent instanceof LoaTooLowException) {
                $title = $translator->trans('stepup.error.loa_too_low.title');
                $description = $translator->trans('stepup.error.loa_too_low.description');
            } elseif ($parent instanceof MissingRequiredAttributeException) {
                $title = $translator->trans('stepup.error.missing_required_attribute.title');
                $description = $parent->getMessage();
            }
        }

        if (isset($title) && isset($description)) {
            return [
                'title' => $title,
                'description' => $description,
            ];
        }

        return parent::getPageTitleAndDescription($exception);
    }
}
