<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open(['url' => action('\Modules\Partners\Http\Controllers\PaymentsController@store'), 'method' => 'post','id' =>'addpayment' ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('partners::lang.register_payment')</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __('partners::lang.partner_name') . ':*') !!}
                {!! Form::select('partner_id', $partners, null, ['class' => 'form-control select2', 'style' => 'width:100%;height: 40px;']); !!}
            </div>

            <div class="form-group">
                {!! Form::label('value', __('partners::lang.value') . ':*') !!}
                {!! Form::text('value', null, ['class' => 'form-control', 'required', 'placeholder' => __('partners::lang.value_placeholder')]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('type', __('partners::lang.transaction_type')) !!}
                {!! Form::select('type', ['1' => __('partners::lang.withdrawal'), '2' => __('partners::lang.deposit')], null, ['class' => 'form-control select2', 'style' => 'width:100%;height: 40px;']); !!}
            </div>

            <div class="form-group">
                {!! Form::label('date', __('partners::lang.transaction_date') . ': ') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    {!! Form::text('date', null, ['class' => 'form-control date-picker', 'required', 'placeholder' => __('partners::lang.date_placeholder'), 'readonly']); !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('notes', __('partners::lang.notes') . ':') !!}
                {!! Form::text('notes', null, ['class' => 'form-control', 'placeholder' => __('partners::lang.notes_placeholder')]); !!}
            </div>

        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script>
    $('.date-picker').datepicker({
        autoclose: true,
        endDate: 'today',
        format: 'yyyy-mm-dd', // Ajustado a 'yyyy-mm-dd' para el formato correcto de fecha
    });
</script>
