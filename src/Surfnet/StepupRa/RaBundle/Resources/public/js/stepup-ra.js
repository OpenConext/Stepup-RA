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

        $(document).on('click', '.not-yet-implemented', function (event) {
            event.preventDefault();

            alert('Sorry, this functionality has not yet been implemented...');
        })
    });
})(jQuery);
