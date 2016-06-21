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

(function ($) {
    'use strict';
    $(document).ready(function() {
        $('#revocationModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget),
                data = button.data(),
                sf = {
                    id: data.sfid,
                    identifier: data.sfidentifier,
                    type: data.sftype,
                    identityId: data.sfidentityid,
                    name: data.sfname,
                    email: data.sfemail
                },
                modal = $(this);

            modal.find('.modal-body td.identifier').text(sf.identifier);
            modal.find('.modal-body td.type').text(sf.type);
            modal.find('.modal-body td.name').text(sf.name);
            modal.find('.modal-body td.email').text(sf.email);

            modal.on('click', 'button.revoke', function (event) {
                var form = $('form[name="ra_revoke_second_factor"]'),
                    secondFactorIdInput = $('#ra_revoke_second_factor_secondFactorId'),
                    identityIdInput = $('#ra_revoke_second_factor_identityId');

                modal.find('button').prop('disabled', true);
                modal.on('hide.bs.modal', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                });

                secondFactorIdInput.val(sf.id);
                identityIdInput.val(sf.identityId);

                form.submit();
            });
        });

        $('#removalModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget),
                data = button.data(),
                sf = {
                    id: data.sfid,
                    institution: data.sfinstitution,
                    name: data.sfname,
                    location: data.sflocation,
                    contactInformation: data.sfcontactinformation
                },
                modal = $(this);

            modal.find('.modal-body td.name').text(sf.name);
            modal.find('.modal-body td.location').text(sf.location);
            modal.find('.modal-body td.contactInformation').text(sf.contactInformation);

            modal.on('click', 'button.remove', function (event) {
                var form = $('form[name="ra_remove_ra_location"]'),
                    locationIdInput = $('#ra_remove_ra_location_locationId'),
                    intitutionInput = $('#ra_remove_ra_location_institution');

                modal.find('button').prop('disabled', true);
                modal.on('hide.bs.modal', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                });

                locationIdInput.val(sf.id);
                intitutionInput.val(sf.institution);

                form.submit();
            });
        });

        $(document).on('click', 'form[name="ra_management_create_ra"] button.create-ra', function (event) {
            var form = $('form[name="ra_management_create_ra"]'),
                modal = $('#create_ra_confirmation_modal');

            if (typeof form[0].checkValidity === 'function' && !form[0].checkValidity()) {
                // Allow native validation behaviour.
                return;
            }

            event.preventDefault();

            modal
                .find('.modal-body td.location')
                .text($('textarea#ra_management_create_ra_location').val());
            modal
                .find('.modal-body td.contact-information')
                .text($('textarea#ra_management_create_ra_contactInformation').val());
            modal
                .find('.modal-body td.role')
                .text($('select#ra_management_create_ra_role option:selected').text());

            modal.modal();

            modal.on('click', 'button.confirm', function (event) {
                var confirmationButton = modal.find('button.confirm');
                modal.on('hide.bs.modal', function (event) {
                    event.preventDefault();
                });

                event.preventDefault();

                modal.find('button').prop('disabled', true);
                confirmationButton.html('<i class="fa fa-circle-o-notch fa-spin"></i>');

                form.submit();
            })
        });

        $(document).on('click', 'form[name="ra_management_change_ra_role"] button.change-ra-role', function (event) {
            var form = $('form[name="ra_management_change_ra_role"]'),
                modal = $('#change_ra_role_confirmation_modal');

            if (typeof form[0].checkValidity === 'function' && !form[0].checkValidity()) {
                // Allow native validation behaviour.
                return;
            }

            event.preventDefault();

            modal
                .find('.modal-body td.role')
                .text($('select#ra_management_change_ra_role_role option:selected').text());

            modal.modal();

            modal.on('click', 'button.confirm', function (event) {
                var confirmationButton = modal.find('button.confirm');
                modal.on('hide.bs.modal', function (event) {
                    event.preventDefault();
                });

                event.preventDefault();

                modal.find('button').prop('disabled', true);
                confirmationButton.html('<i class="fa fa-circle-o-notch fa-spin"></i>');

                form.submit();
            })
        });

        $(document).on('click', '.not-yet-implemented', function (event) {
            event.preventDefault();

            alert('Sorry, this functionality has not yet been implemented...');
        });

        // Format all <time> elements to local timezone.
        $("time[datetime]").each(function () {
            $(this).text(
                moment($(this).attr("datetime")).format('Y-MM-DD HH:mm')
            );
        });
    });
})(jQuery);
