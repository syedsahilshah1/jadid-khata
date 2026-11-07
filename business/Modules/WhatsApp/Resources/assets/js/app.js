$(document).ready(function () {

    loadAccounts = $("#accounts").DataTable({
            processing: true,
            serverSide: true,
            ajax:{
                url: '/whatsapp/dashboard',
                "data": function ( d ) {
                  d.wa = 'xssdp'
                }
            },
            columns:[
                { data: 'action', name: 'action' },
                { data: 'sources', name: 'sources' },
                { data: 'wa_server', name: 'wa_server'},
                { data: 'sender', name: 'sender'},
                { data: 'is_default',
                   name: 'is_default'
                },
                { data: 'app_key', name: 'app_key'},
                { data: 'auth_key', name: 'auth_key'},
            ]
    });


    $('.whatsapp_add_modal, .whatsapp_edit_modal').on('shown.bs.modal', function(e) {
        $('form#whatsapp_accounts_add_form')
            .submit(function(e) {
                e.preventDefault();
            })
            .validate({
                messages: {
                    whatsapp_id: {
                        remote: 'Exists!',
                    },
                },
                submitHandler: function(form) {
                    e.preventDefault();
                    var data = $(form).serialize();
                    $.ajax({
                        method: 'POST',
                        url: $(form).attr('action'),
                        dataType: 'json',
                        data: data,
                        beforeSend: function(xhr) {
                            __disable_submit_button($(form).find('button[type="submit"]'));
                        },
                        success: function(result) {
                            if (result.success == true) {
                                $('div.whatsapp_add_modal').modal('hide');
                                $('div.whatsapp_edit_modal').modal('hide');
                                toastr.success(result.msg);
                                loadAccounts.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                },
            });
    });

    $(document).on('click', '.delete-whatsapp-accounts', function(){
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then(willDelete => {
             if (willDelete) {
                $.ajax({
                    url: $(this).data('href'),
                    method: 'DELETE',
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            loadAccounts.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        });
    });
});