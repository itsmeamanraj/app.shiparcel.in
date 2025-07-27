$(document).ready(function () {
    $('#toggleReturnAddress').change(function () {
        if ($(this).is(':checked')) {
            $('#returnAddressDropdown').show();
        } else {
            $('#returnAddressDropdown').hide();
        }
    }).trigger('change'); // Ensure it runs on page load
});

var i = 0;
$('#btn_add_products').click(function () {
    i++;
    var append_text = '';

    append_text += '<div class="form-group mb-4" id="row' + i + '"><div class="row"><div class="col-md-4 col-lg-4 col-xl-4 mb-3">';
    append_text += '<label for="form-label">Product Name<span class="text-danger">*</span></label>';
    append_text += '<input type="text" id="product_name" name="product_name[]" class="form-control wizard-required" placeholder="Enter product name..." required>';

    append_text += '</div><div class="col-md-2 col-lg-2 col-xl-2 mb-3"><label for="form-label">Quantity<span class="text-danger">*</span></label>';
    append_text += '<input type="text" id="product_quantity" name="product_quantity[]" class="form-control wizard-required" placeholder="Enter product Quantity..." required>';

    append_text += '</div><div class="col-md-2 col-lg-2 col-xl-2 mb-3"><label for="form-label">Product Value<span class="text-danger">*</span></label>';
    append_text += '<input type="text" id="product_value" name="product_value[]" class="form-control wizard-required" placeholder="Enter product value..." required>';

    append_text += '</div><div class="col-md-3 col-lg-3 col-xl-3 mb-3"><label for="form-label">Category<span class="text-danger">*</span></label>';
    append_text += '<input type="text" id="product_category" name="product_category[]" class="form-control wizard-required" placeholder="Enter product category..." required>';


    append_text += '</div><div class="col-md-3 col-lg-3 col-xl-3 mb-3"label for="form-label">SKU<span class="text-danger">*</span></label>';
    append_text += '<input type="text" id="product_sku" name="product_sku[]" class="form-control wizard-required" placeholder="Enter SKU..." required>';

    append_text += '</div><div class="col-md-1 col-lg-1 col-xl-1 mb-3">';
    append_text += '<label for="form-label" style="padding-top:30px;">&#160;</label><button type="button" class="btn   btn-danger btn_remove_product mt-2" data-toggle="tooltip" title="Remove Product" id="' + i + '" name="btn_remove_product"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M7 21q-.825 0-1.412-.587T5 19V6H4V4h5V3h6v1h5v2h-1v13q0 .825-.587 1.413T17 21zM17 6H7v13h10zM9 17h2V8H9zm4 0h2V8h-2zM7 6v13z"></path></svg></button>';
    append_text += '</div></div></div>';

    $('#div_add_products').append(append_text);
});

$(document).on('click', '.btn_remove_product', function () {
    var button_id = $(this).attr("id");
    $('#row' + button_id + '').remove();
    calculateSum();
});


function calculateSum() {
    var sum = 0;
    $(".valuesum").each(function () {
        if (!isNaN(this.value) && this.value.length != 0)
            sum += parseFloat(this.value);
    });
    $("#order_amount").val(sum.toFixed(2));
    $("#total_amount").val((parseFloat(sum) + parseFloat($("#extra_charges").val())).toFixed(2));
    if (sum >= parseFloat(50000)) {
        $(".ewaybill").css("display", "block");
        $(".tipinfo").css("display", "block");
    } else {
        $(".ewaybill").css("display", "none");
        $(".tipinfo").css("display", "none");
    }
    calculatecod();
}

function calculatecod() {
    if ($("#payment_mode").val() == 'cod')
        $("#cod_amount").val($("#total_amount").val()).attr('readonly', false);
    else
        $("#cod_amount").val('0').attr('readonly', true);
}


// =============================== Wizard Step Js Start ================================
$(document).ready(function () {
    // click on next button
    $('.form-wizard-next-btn').on("click", function () {
        var parentFieldset = $(this).parents('.wizard-fieldset');
        var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-list .active');
        var next = $(this);
        var nextWizardStep = true;
        parentFieldset.find('.wizard-required').each(function () {
            var thisValue = $(this).val();

            if (thisValue == "") {
                $(this).siblings(".wizard-form-error").show();
                nextWizardStep = false;
            }
            else {
                $(this).siblings(".wizard-form-error").hide();
            }
        });
        if (nextWizardStep) {
            next.parents('.wizard-fieldset').removeClass("show", "400");
            currentActiveStep.removeClass('active').addClass('activated').next().addClass('active', "400");
            next.parents('.wizard-fieldset').next('.wizard-fieldset').addClass("show", "400");
            $(document).find('.wizard-fieldset').each(function () {
                if ($(this).hasClass('show')) {
                    var formAtrr = $(this).attr('data-tab-content');
                    $(document).find('.form-wizard-list .form-wizard-step-item').each(function () {
                        if ($(this).attr('data-attr') == formAtrr) {
                            $(this).addClass('active');
                            var innerWidth = $(this).innerWidth();
                            var position = $(this).position();
                            $(document).find('.form-wizard-step-move').css({ "left": position.left, "width": innerWidth });
                        } else {
                            $(this).removeClass('active');
                        }
                    });
                }
            });
        }
    });
    //click on previous button
    $('.form-wizard-previous-btn').on("click", function () {
        var counter = parseInt($(".wizard-counter").text());;
        var prev = $(this);
        var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-list .active');
        prev.parents('.wizard-fieldset').removeClass("show", "400");
        prev.parents('.wizard-fieldset').prev('.wizard-fieldset').addClass("show", "400");
        currentActiveStep.removeClass('active').prev().removeClass('activated').addClass('active', "400");
        $(document).find('.wizard-fieldset').each(function () {
            if ($(this).hasClass('show')) {
                var formAtrr = $(this).attr('data-tab-content');
                $(document).find('.form-wizard-list .form-wizard-step-item').each(function () {
                    if ($(this).attr('data-attr') == formAtrr) {
                        $(this).addClass('active');
                        var innerWidth = $(this).innerWidth();
                        var position = $(this).position();
                        $(document).find('.form-wizard-step-move').css({ "left": position.left, "width": innerWidth });
                    } else {
                        $(this).removeClass('active');
                    }
                });
            }
        });
    });
    //click on form submit button
    $(document).on("click", ".form-wizard .form-wizard-submit", function () {
        var parentFieldset = $(this).parents('.wizard-fieldset');
        var currentActiveStep = $(this).parents('.form-wizard').find('.form-wizard-list .active');
        parentFieldset.find('.wizard-required').each(function () {
            var thisValue = $(this).val();
            if (thisValue == "") {
                $(this).siblings(".wizard-form-error").show();
            }
            else {
                $(this).siblings(".wizard-form-error").hide();
            }
        });
    });
    // focus on input field check empty or not
    $(".form-control").on('focus', function () {
        var tmpThis = $(this).val();
        if (tmpThis == '') {
            $(this).parent().addClass("focus-input");
        }
        else if (tmpThis != '') {
            $(this).parent().addClass("focus-input");
        }
    }).on('blur', function () {
        var tmpThis = $(this).val();
        if (tmpThis == '') {
            $(this).parent().removeClass("focus-input");
            $(this).siblings(".wizard-form-error").show();
        }
        else if (tmpThis != '') {
            $(this).parent().addClass("focus-input");
            $(this).siblings(".wizard-form-error").hide();
        }
    });
});
// =============================== Wizard Step Js End ================================



//=============================Cancel Order API ===========================

function showCancelConfirmModal(awbNumber) {
    $('#cancelOrderModal').data('awbNumber', awbNumber); // Store AWB number
    var modal = new bootstrap.Modal(document.getElementById('cancelOrderModal'));
    modal.show();
}

function cancelOrder() {
    let awbNumber = $('#cancelOrderModal').data('awbNumber'); // Retrieve stored AWB number
    console.log(awbNumber, 'awbNumber'); // Debugging

    if (!awbNumber) {
        showMessage("AWB number is missing!", "danger");
        return;
    }

    $.ajax({
        url: ORDER_CANCEL_URL,
        type: 'POST',
        data: JSON.stringify({
            awb_number: awbNumber
        }),
        contentType: 'application/json',
        headers: {
            'X-CSRF-TOKEN': CSRF_TOKEN
        },
        success: function (response) {
            showMessage(response.message, 'success');
            setTimeout(() => location.reload(), 2000); // Reload after success

            // Close the modal on error as well
            var modal = bootstrap.Modal.getInstance(document.getElementById('cancelOrderModal'));
            modal.hide()
        },
        error: function (xhr) {
            let response = xhr.responseJSON;
            let errorMessage = response && response.message ? response.message : 'Something went wrong!';
            showMessage(errorMessage, 'danger');

            // Close the modal on error as well
            var modal = bootstrap.Modal.getInstance(document.getElementById('cancelOrderModal'));
            modal.hide();
        }
    });
}

function showMessage(message, type) {
    let alertBox = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
    $('#alertContainer').html(alertBox);

    // Auto-close after 3 seconds
    setTimeout(() => {
        $(".alert").alert('close');
    }, 5000);
}

// function openLabelData(awbNumber) {

//     if (!awbNumber) {
//         showMessage("AWB number is missing!", "danger");
//         return;
//     }

//     $.ajax({
//         url: ORDER_LABEL_URL,
//         type: 'POST',
//         data: JSON.stringify({
//             awb_number: awbNumber
//         }),
//         contentType: 'application/json',
//         headers: {
//             'X-CSRF-TOKEN': CSRF_TOKEN
//         },
//         success: function (response) {
//             showMessage(response.message, 'success');
//             window.open(response.label_url, '_blank');
//             setTimeout(() => location.reload(), 2000); // Reload after success
//         },
//         error: function (xhr) {
//             let response = xhr.responseJSON;
//             let errorMessage = response && response.message ? response.message : 'Something went wrong!';
//             showMessage(errorMessage, 'danger');

//         }
//     });
// }

function openLabelData(awbNumber) {
    if (!awbNumber) {
        showMessage("AWB number is missing!", "danger");
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = ORDER_LABEL_URL; // this should point to your Laravel route
    form.target = '_blank';

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = CSRF_TOKEN; // make sure CSRF_TOKEN is defined globally

    const awbInput = document.createElement('input');
    awbInput.type = 'hidden';
    awbInput.name = 'awb_number';
    awbInput.value = awbNumber;

    form.appendChild(csrfInput);
    form.appendChild(awbInput);
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

