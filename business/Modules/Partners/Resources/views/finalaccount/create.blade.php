<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open(['url' => action('\Modules\Partners\Http\Controllers\FinalAccountController@store'),'id'=>'addnew', 'method' => 'post' ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('partners::lang.add_final_account')</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('profite', __('partners::lang.profit_value')) !!}
                {!! Form::text('profite', null, ['class' => 'form-control decimal', 'required', 'placeholder' => __('partners::lang.value_in_egp') ]); !!}
            </div>
            <div class="form-group">
                {!! Form::label('sharenumber', __('partners::lang.total_shares')) !!}
                {!! Form::text('sharenumber', $totalshare, ['class' => 'form-control', 'readonly', 'placeholder' =>'' ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('shareval', __('partners::lang.share_value')) !!}
                {!! Form::text('shareval', null, ['class' => 'form-control', 'readonly', 'placeholder' =>'' ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('startdate', __('partners::lang.period_from')) !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    {!! Form::text('startdate', null, ['class' => 'form-control date-picker', 'required', 'placeholder' => __('partners::lang.start_period') ]); !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('enddate', __('partners::lang.to')) !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </span>
                    {!! Form::text('enddate', null, ['class' => 'form-control date-picker', 'required', 'placeholder' => __('partners::lang.end_period') ]); !!}
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('notes', __('partners::lang.notes')) !!}
                {!! Form::text('notes', null, ['class' => 'form-control' ]); !!}
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
        format:'yyyy-m-d',
    });

    $("#profite").on('keyup',function () {
        var total=$(this).val();
        var number=$('#sharenumber').val();
        var sharval=(total/number).toFixed(2);
        $('#shareval').val(sharval);

        $('.share').each(function (index,item) {
            var id = $(this).attr('id');
            var remval=$(this).val()*sharval -$('#value_'+id).val();
            $('#rem_'+id).val(remval.toFixed(2));
        });
    });
</script>
