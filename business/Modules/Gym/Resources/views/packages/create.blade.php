<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\Modules\Gym\Http\Controllers\PackageController::class, 'store']),
            'method' => 'post',
            'id' => 'add_package',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('gym::lang.packages')</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __('gym::lang.name') . '*') !!}
                {!! Form::text('name', null, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('gym::lang.name'),
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('amount', __('gym::lang.amount') . '*') !!}
                {!! Form::number('amount', null, [
                    'class' => 'form-control',
                    'required',
                    'step' => '0.01',
                    'placeholder' => __('gym::lang.amount'),
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('duration', __('gym::lang.duration') . '*') !!}
                {!! Form::select('duration', $durations, '', [
                    'class' => 'form-control',
                    'required',
                ]) !!}
            </div>
            {!! Form::label('duration', __('gym::lang.classes')) !!}
            <div class="row">
                @foreach ($classes as $key => $class)
                    <div class="col-md-4">
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="classes[]" value="{{ $class->id }}">
                                    {{ $class->name }}  ({{$class->start_time ? @format_time($class->start_time) : ''}} {{ $class->start_time || $class->end_time ? ' - ' : '' }} {{$class->end_time ? @format_time($class->end_time) : ''}})
                                </label>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="form-group">
                {!! Form::label('notes', __('gym::lang.notes')) !!}
                {!! Form::textarea('notes', null, [
                    'class' => 'form-control',
                    'placeholder' => __('gym::lang.notes'),
                    'rows' => 3,
                ]) !!}
            </div>
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('enable', 1, ['class' => 'input-icheck']) !!}
                        @lang('gym::lang.enable')
                    </label>
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
