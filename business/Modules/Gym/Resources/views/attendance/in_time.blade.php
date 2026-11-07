<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\Modules\Gym\Http\Controllers\AttendanceController::class, 'add_edit_in_time']),
            'method' => 'post',
            'id' => 'add_edit_in_time',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('gym::lang.in_time')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <input type="hidden" name="contact_id", value="{{$member->id}}">
                        {!! Form::label('in_time', __('gym::lang.in_time') . ':') !!}
                        {!! Form::text('in_time', $attendance && $attendance->in_time ? @format_time($attendance->in_time) : null, [
                            'class' => 'form-control time_picker',
                            'placeholder' => __('gym::lang.in_time'),
                            'readonly',
                            'required',
                            'id' => 'in_time',
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.save')</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
                data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
