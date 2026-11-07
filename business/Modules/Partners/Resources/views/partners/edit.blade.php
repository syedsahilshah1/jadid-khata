<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open(['url' => action('\Modules\Partners\Http\Controllers\PartnersController@update', $partner->id), 'method' => 'PUT' ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('partners::lang.edit_partner')</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __('partners::lang.partner_name') . ':*') !!}
                {!! Form::text('name', $partner->name, ['class' => 'form-control', 'required', 'placeholder' => __('partners::lang.name_placeholder')]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('address', __('partners::lang.address') . ':*') !!}
                {!! Form::text('address', $partner->address, ['class' => 'form-control', 'required', 'placeholder' => __('partners::lang.address_placeholder')]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('mobile', __('partners::lang.mobile_number') . ':') !!}
                {!! Form::text('mobile', $partner->mobile, ['class' => 'form-control', 'placeholder' => __('partners::lang.mobile_placeholder')]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('capital', __('partners::lang.capital_value') . ':') !!}
                {!! Form::text('capital', $partner->capital, ['class' => 'form-control', 'placeholder' => __('partners::lang.capital_placeholder')]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('share', __('partners::lang.number_of_shares') . ':') !!}
                {!! Form::text('share', $partner->share, ['class' => 'form-control', 'placeholder' => __('partners::lang.shares_placeholder')]); !!}
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
        format:'yyyy-m-d',
    });
</script>
