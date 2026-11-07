@extends('layouts.app')
@section('title', __('gym::lang.diets'))
@section('content')
    @include('gym::layouts.nav')
    <section class="content">
        @component('components.widget')
            <div class="row">
                <div class="col-md-12">
                    {!! Form::open(['url' => action([\Modules\Gym\Http\Controllers\MemberDietController::class, 'store']), 'method' => 'post', 'id' => 'create_diet_plan']) !!}
                <input type="hidden" value="{{ $contact->id }}" name="contact_id" id="contact_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('morning', __('gym::lang.morning') . ':') !!}
                                {!! Form::text('morning', $diet->morning ?? null, ['class' => 'form-control', 'required']) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('breakfast', __('gym::lang.breakfast') . ':') !!}
                                {!! Form::text('breakfast', $diet->breakfast ?? null, ['class' => 'form-control', 'required']) !!}
                            </div>
                        </div>
                    </div>
                
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('before_lunch', __('gym::lang.before_lunch') . ':') !!}
                                {!! Form::text('before_lunch', $diet->before_lunch ?? null, ['class' => 'form-control', 'required']) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('lunch', __('gym::lang.lunch') . ':') !!}
                                {!! Form::text('lunch', $diet->lunch ?? null, ['class' => 'form-control', 'required']) !!}
                            </div>
                        </div>
                    </div>
                
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('afternoon', __('gym::lang.afternoon') . ':') !!}
                                {!! Form::text('afternoon', $diet->afternoon ?? null, ['class' => 'form-control', 'required']) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('evening', __('gym::lang.evening') . ':') !!}
                                {!! Form::text('evening', $diet->evening ?? null, ['class' => 'form-control', 'required']) !!}
                            </div>
                        </div>
                    </div>
                
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('dinner', __('gym::lang.dinner') . ':') !!}
                                {!! Form::text('dinner', $diet->dinner ?? null, ['class' => 'form-control', 'required']) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('before_sleep', __('gym::lang.before_sleep') . ':') !!}
                                {!! Form::text('before_sleep', $diet->before_sleep ?? null, ['class' => 'form-control', 'required']) !!}
                            </div>
                        </div>
                    </div>
                
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('before_workout', __('gym::lang.before_workout') . ':') !!}
                                {!! Form::text('before_workout', $diet->before_workout ?? null, ['class' => 'form-control', 'required']) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('after_workout', __('gym::lang.after_workout') . ':') !!}
                                {!! Form::text('after_workout', $diet->after_workout ?? null, ['class' => 'form-control', 'required']) !!}
                            </div>
                        </div>
                    </div>
                
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('remarks', __('gym::lang.remarks') . ':') !!}
                                {!! Form::textarea('remarks', $diet->remarks ?? null, ['class' => 'form-control', 'rows' => 3, 'required']) !!}
                            </div>
                        </div>
                    </div>
                
                    <div class="col-md-12 text-center">
                        <button type="submit" name="submit_action" value="save" class="tw-dw-btn tw-dw-btn-primary tw-text-white">
                            @lang('messages.save')
                        </button>
                    </div>
                
                    {!! Form::close() !!}
                </div>
            </div>
        @endcomponent
    </section>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $("form#create_diet_plan").validate();
        });
    </script>
@endsection
