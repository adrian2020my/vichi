/**
 *
 * Realex payment plugin
 *
 * @author Valérie Isaksen
 * @version $Id: admin.js 8467 2014-10-16 16:07:03Z alatak $
 * @package VirtueMart
 * @subpackage payment
 * Copyright (C) 2004-2014 Virtuemart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

jQuery().ready(function ($) {

    /************/
    /* Handlers */
    /************/
    handleIntegrationParameters = function () {
        var integration = $('#paramsintegration').val();

        $('.redirect, .remote').parents('tr').hide();

        if (integration == 'redirect') {
            $('.redirect').parents('tr').show();
        } else if (integration == 'remote') {
            $('.remote').parents('tr').show();
        }

    }

    handleRealvault = function () {
        var realvault = $('#paramsrealvault').val();
        var integration = $('#paramsintegration').val();
        $('.redirect-realvault').parents('tr').hide();
        $('.redirect-norealvault').parents('tr').hide();
        $('.realvault-param').parents('tr').hide();

        $('.realvault').parents('tr').hide();
        $('.norealvault').parents('tr').hide();

        if (integration == 'redirect') {
            if (realvault == 1) {
                $('.redirect-realvault').parents('tr').show();
                $('.realvault').parents('tr').show();
                $('.realvault-param').parents('tr').show();
            } else {
                $('#paramsthreedsecure option').eq(0).attr('selected', 'selected');
                // depends on the chosen version
                $('#paramsthreedsecure').trigger("chosen:updated"); //newer
                $("#paramsthreedsecure").trigger("liszt:updated"); // our
                $('.redirect-norealvault').parents('tr').show();
            }
        } else {
            if (realvault == 1) {
                $('.realvault-param').parents('tr').show();
            }
            $('.realvault').parents('tr').show();

        }

    }

    handleSettlement = function () {
        var settlement = $('#paramssettlement').val();

        $('.settlement').parents('tr').hide();

        if (settlement == 'delayed') {
            $('.settlement').parents('tr').show();
        }
    }

    handlethreedsecure = function () {
        var threedsecure = $('#paramsthreedsecure').val();
        var realvault = $('#paramsrealvault').val();
        var integration = $('#paramsintegration').val();

        $('.threedsecure').parents('tr').hide();

        if ((threedsecure == 1 && integration == 'redirect' && realvault == 1) ||  (threedsecure == 1 && integration == 'remote')) {
            $('.threedsecure').parents('tr').show();
        }
    }

    handleDcc = function () {
        var dcc = $('#paramsdcc').val();

        $('.dcc').parents('tr').hide();
        $('.nodcc').parents('tr').hide();

        if (dcc == 1) {
            $('.dcc').parents('tr').show();
        }
        if (dcc == 0) {
            $('.nodcc').parents('tr').show();
        }
    }

    handleAutoComplete = function () {
        $('#paramsmerchant_id').attr('autocomplete', 'off');
    }
    /**********/
    /* Events */
    /**********/
    $('#paramsintegration').change(function () {
        handleIntegrationParameters();
        handleRealvault();

    });
    $('#paramsrealvault').change(function () {
        handleRealvault();
    });
    $('#paramssettlement').change(function () {
        handleSettlement();
    });
    $('#paramsdcc').change(function () {
        handleDcc();
    });
    $('#paramsthreedsecure').change(function () {
        handlethreedsecure();
    });

    /*****************/
    /* Initial calls */
    /*****************/

    handleIntegrationParameters();
    handleRealvault();
    handleSettlement();
    handleDcc();
    handlethreedsecure();
    handleAutoComplete();
});
