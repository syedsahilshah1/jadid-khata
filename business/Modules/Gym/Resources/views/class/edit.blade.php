<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\Modules\Gym\Http\Controllers\ClassController::class, 'update'],
             ['class'=> $class->id]), 'method' => 'put', 'id' => 'edit_class'
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('gym::lang.classes')</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __('gym::lang.name') . '*') !!}
                {!! Form::text('name', $class->name, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('gym::lang.name'),
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('start_time', __('gym::lang.start_time')) !!}
                {!! Form::text('start_time', $class->start_time ? @format_time($class->start_time) : null , [
                    'class' => 'form-control time_picker',
                    'readonly',
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('end_time', __('gym::lang.end_time')) !!}
                {!! Form::text('end_time', $class->end_time ? @format_time($class->end_time) : null, [
                    'class' => 'form-control time_picker',
                    'readonly',
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('description', __('gym::lang.description')) !!}
                {!! Form::textarea('description', $class->description, [
                    'class' => 'form-control',
                    'placeholder' => __('gym::lang.description'),
                    'rows' => 3,
                ]) !!}
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.save')</button>
            <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white"
                data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div>