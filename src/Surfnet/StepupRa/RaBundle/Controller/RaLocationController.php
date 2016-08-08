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

namespace Surfnet\StepupRa\RaBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Surfnet\StepupRa\RaBundle\Command\ChangeRaLocationCommand;
use Surfnet\StepupRa\RaBundle\Command\CreateRaLocationCommand;
use Surfnet\StepupRa\RaBundle\Command\RemoveRaLocationCommand;
use Surfnet\StepupRa\RaBundle\Command\SearchRaLocationsCommand;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RaLocationController extends Controller
{
    /**
     * @Template
     * @param Request $request
     * @return array|Response
     */
    public function manageAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_RA']);

        $identity = $this->getCurrentUser();
        $this->get('logger')->notice('Starting search for locations');

        $command = new SearchRaLocationsCommand();
        $command->institution = $identity->institution;
        $command->orderBy = $request->get('orderBy');
        $command->orderDirection = $request->get('orderDirection');

        $locations = $this->getRaLocationService()->search($command);

        $removalForm = $this->createForm('ra_remove_ra_location', new RemoveRaLocationCommand());

        $this->get('logger')->notice(sprintf(
            'Searching for RA locations yielded "%d" results',
            $locations->getTotalItems()
        ));

        return [
            'locations'             => $locations,
            'removalForm'           => $removalForm->createView(),
            'orderBy'               => $command->orderBy,
            'orderDirection'        => $command->orderDirection ?: 'asc',
            'inverseOrderDirection' => $command->orderDirection === 'asc' ? 'desc' : 'asc',
        ];
    }

    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_RA']);
        $logger = $this->get('logger');

        $identity = $this->getCurrentUser();
        $command = new CreateRaLocationCommand();
        $command->currentUserId = $this->getCurrentUser()->id;
        $command->institution = $identity->institution;
        $command->currentUserId = $identity->id;

        $form = $this->createForm('ra_create_ra_location', $command)->handleRequest($request);

        if ($form->isValid()) {
            $logger->debug('RA Location form submitted, start processing command');

            $success = $this->getRaLocationService()->create($command);

            if ($success) {
                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('ra.create_ra_location.created')
                );

                $logger->debug('RA Location added, redirecting to the RA location overview');
                return $this->redirectToRoute('ra_locations_manage');
            }

            $logger->debug('RA Location creation failed, adding error to form');
            $form->addError(new FormError('ra.create_ra_location.error.middleware_command_failed'));
        }

        return $this->render('SurfnetStepupRaRaBundle:RaLocation:create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function changeAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_RA']);
        $logger = $this->get('logger');

        $requestedLocationId = $request->get('locationId');
        $raLocation = $this->getRaLocationService()->find($requestedLocationId);

        if (!$raLocation) {
            $logger->warning(sprintf('RaLocation for id "%s" not found', $requestedLocationId));
            throw new NotFoundHttpException();
        }

        $identity = $this->getCurrentUser();

        if ($raLocation->institution !== $identity->institution) {
            $logger->warning(
                sprintf(
                    'RaLocation "%s" found but of institution "%s" while we require "%s"',
                    $raLocation->id,
                    $raLocation->institution,
                    $identity->institution
                )
            );
        }

        $command = new ChangeRaLocationCommand();
        $command->currentUserId = $this->getCurrentUser()->id;
        $command->institution = $identity->institution;
        $command->currentUserId = $identity->id;
        $command->id = $raLocation->id;
        $command->name = $raLocation->name;
        $command->location = $raLocation->location;
        $command->contactInformation = $raLocation->contactInformation;

        $form = $this->createForm('ra_change_ra_location', $command)->handleRequest($request);

        if ($form->isValid()) {
            $logger->debug('RA Location form submitted, start processing command');

            $success = $this->getRaLocationService()->change($command);

            if ($success) {
                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('ra.create_ra_location.changed')
                );

                $logger->debug('RA Location added, redirecting to the RA location overview');
                return $this->redirectToRoute('ra_locations_manage');
            }

            $logger->debug('RA Location creation failed, adding error to form');
            $form->addError(new FormError('ra.create_ra_location.error.middleware_command_failed'));
        }

        return $this->render('SurfnetStepupRaRaBundle:RaLocation:change.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_RA']);

        $logger = $this->get('logger');

        $logger->notice('Received request to remove RA location');

        $command = new RemoveRaLocationCommand();
        $command->currentUserId = $this->getCurrentUser()->id;

        $form = $this->createForm('ra_remove_ra_location', $command);
        $form->handleRequest($request);

        $logger->info(sprintf(
            'Sending middleware request to remove RA location "%s" belonging to "%s" on behalf of "%s"',
            $command->locationId,
            $command->institution,
            $command->currentUserId
        ));

        $translator = $this->get('translator');
        $flashBag = $this->get('session')->getFlashBag();
        if ($this->getRaLocationService()->remove($command)) {
            $logger->notice('RA Location removal Succeeded');
            $flashBag->add('success', $translator->trans('ra.ra_location.revocation.removed'));
        } else {
            $logger->notice('RA Location removal Failed');
            $flashBag->add('error', $translator->trans('ra.ra_location.revocation.could_not_remove'));
        }

        $logger->notice('Redirecting back to RA Location Manage Page');

        return $this->redirectToRoute('ra_locations_manage');
    }

    /**
     * @return \Surfnet\StepupRa\RaBundle\Service\RaLocationService
     */
    private function getRaLocationService()
    {
        return $this->get('ra.service.ra_location');
    }

    /**
     * @return \Surfnet\StepupMiddlewareClientBundle\Identity\Dto\Identity
     */
    private function getCurrentUser()
    {
        return $this->get('security.token_storage')->getToken()->getUser();
    }
}
