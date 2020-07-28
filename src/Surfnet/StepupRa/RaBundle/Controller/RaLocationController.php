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
use Surfnet\StepupRa\RaBundle\Command\SelectInstitutionCommand;
use Surfnet\StepupRa\RaBundle\Form\Type\ChangeRaLocationType;
use Surfnet\StepupRa\RaBundle\Form\Type\CreateRaLocationType;
use Surfnet\StepupRa\RaBundle\Form\Type\RemoveRaLocationType;
use Surfnet\StepupRa\RaBundle\Form\Type\SelectInstitutionType;
use Surfnet\StepupRa\RaBundle\Service\ProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) By making the Form Type classes explicit, MD now realizes couping
 *                                                 is to high.
 */
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

        $institutionParameter = $request->get('institution');

        $identity = $this->getCurrentUser();
        $this->get('logger')->notice('Starting search for locations');

        $profile = $this->getProfileService()->findByIdentityId($identity->id);

        if ($this->isGranted('ROLE_SRAA')) {
            $institution = $identity->institution;
            $choices = $this->getInstitutionListingService()->getAll();
        } else {
            $choices = $profile->getRaaInstitutions();
            $institution = reset($choices);
        }

        if (in_array($institutionParameter, $choices)) {
            $institution = $institutionParameter;
        }

        // Only show the form if more than one institutions where found.
        if (count($choices) > 1) {
            $command = new SelectInstitutionCommand();
            $command->institution = $institution;
            $command->availableInstitutions = $choices;

            $form = $this->createForm(SelectInstitutionType::class, $command);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $institution = $command->institution;
            }
        }


        $command = new SearchRaLocationsCommand();
        $command->institution = $institution;
        $command->orderBy = $request->get('orderBy');
        $command->orderDirection = $request->get('orderDirection');

        $locations = $this->getRaLocationService()->search($command);

        $removalForm = $this->createForm(RemoveRaLocationType::class, new RemoveRaLocationCommand());

        $this->get('logger')->notice(sprintf(
            'Searching for RA locations yielded "%d" results',
            $locations->getTotalItems()
        ));

        return [
            'form'                  => isset($form) ? $form->createView() : null,
            'institution'           => $institution,
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

        $institution = $request->get('institution');

        $identity = $this->getCurrentUser();
        $command = new CreateRaLocationCommand();
        $command->institution = $institution;
        $command->currentUserId = $identity->id;

        $form = $this->createForm(CreateRaLocationType::class, $command)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logger->debug('RA Location form submitted, start processing command');

            $success = $this->getRaLocationService()->create($command);

            if ($success) {
                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('ra.create_ra_location.created')
                );

                $logger->debug('RA Location added, redirecting to the RA location overview');
                return $this->redirectToRoute('ra_locations_manage', ['institution' => $command->institution]);
            }

            $logger->debug('RA Location creation failed, adding error to form');
            $this->addFlash('error', 'ra.create_ra_location.error.middleware_command_failed');
        }

        return $this->render('SurfnetStepupRaRaBundle:ra_location:create.html.twig', [
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

        $command = new ChangeRaLocationCommand();
        $command->institution = $raLocation->institution;
        $command->currentUserId = $identity->id;
        $command->id = $raLocation->id;
        $command->name = $raLocation->name;
        $command->location = $raLocation->location;
        $command->contactInformation = $raLocation->contactInformation;

        $form = $this->createForm(ChangeRaLocationType::class, $command)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $logger->debug('RA Location form submitted, start processing command');

            $success = $this->getRaLocationService()->change($command);

            if ($success) {
                $this->addFlash(
                    'success',
                    $this->get('translator')->trans('ra.create_ra_location.changed')
                );

                $logger->debug('RA Location added, redirecting to the RA location overview');
                return $this->redirectToRoute('ra_locations_manage', ['institution' => $command->institution]);
            }

            $logger->debug('RA Location creation failed, adding error to form');
            $this->addFlash('error', 'ra.create_ra_location.error.middleware_command_failed');
        }

        return $this->render('SurfnetStepupRaRaBundle:ra_location:change.html.twig', [
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

        $form = $this->createForm(RemoveRaLocationType::class, $command);
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

        return $this->redirectToRoute('ra_locations_manage', ['institution' => $command->institution]);
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

    /**
     * @return ProfileService
     */
    private function getProfileService()
    {
        return $this->get('ra.service.profile');
    }

    /**
     * @return InstitutionListingService
     */
    private function getInstitutionListingService()
    {
        return $this->get('ra.service.institution_listing');
    }
}
