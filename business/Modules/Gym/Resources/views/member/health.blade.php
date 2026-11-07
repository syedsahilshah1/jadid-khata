@extends('layouts.app')
@section('title', __('gym::lang.members'))
@section('content')
    @include('gym::layouts.nav')
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"> @lang('gym::lang.health')
            <small>{{ $member->name }}</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget')
            <div class="row">
                <div class="col-md-12">
                    {!! Form::open([
                        'url' => action([\Modules\Gym\Http\Controllers\MemberController::class, 'store_health'], ['id' => $member->id]),
                        'method' => 'post',
                        'id' => 'create_health_tracking',
                    ]) !!}

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('date', __('lang_v1.date') . ':') !!}
                                {!! Form::text('date', null, [
                                    'class' => 'form-control health_date',
                                    'placeholder' => __('lang_v1.date'),
                                    'readonly',
                                    'required',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('neck', __('gym::lang.neck') . ':') !!}
                                {!! Form::number('neck', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('left_arm', __('gym::lang.left_arm') . ':') !!}
                                {!! Form::number('left_arm', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('right_arm', __('gym::lang.right_arm') . ':') !!}
                                {!! Form::number('right_arm', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('chest', __('gym::lang.chest') . ':') !!}
                                {!! Form::number('chest', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('upper_waist', __('gym::lang.upper_waist') . ':') !!}
                                {!! Form::number('upper_waist', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('lower_waist', __('gym::lang.lower_waist') . ':') !!}
                                {!! Form::number('lower_waist', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('hips', __('gym::lang.hips') . ':') !!}
                                {!! Form::number('hips', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('left_thigh', __('gym::lang.left_thigh') . ':') !!}
                                {!! Form::number('left_thigh', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('right_thigh', __('gym::lang.right_thigh') . ':') !!}
                                {!! Form::number('right_thigh', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('calf', __('gym::lang.calf') . ':') !!}
                                {!! Form::number('calf', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('height', __('gym::lang.height') . ':*') !!}
                                {!! Form::number('height', null, ['class' => 'form-control', 'required', 'step' => '0.1']) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('weight', __('gym::lang.weight') . ':*') !!}
                                {!! Form::number('weight', null, ['class' => 'form-control', 'required', 'step' => '0.1']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('shoulders', __('gym::lang.shoulders') . ':') !!}
                                {!! Form::number('shoulders', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('body_fat_percentage', __('gym::lang.body_fat_percentage') . ':') !!}
                                {!! Form::number('body_fat_percentage', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">

                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('visceral_fat', __('gym::lang.visceral_fat') . ':') !!}
                                {!! Form::number('visceral_fat', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('subcutaneous_fat', __('gym::lang.subcutaneous_fat') . ':') !!}
                                {!! Form::number('subcutaneous_fat', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('bmi', __('gym::lang.bmi') . ':') !!}
                                {!! Form::number('bmi', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('muscle_mass_percentage', __('gym::lang.muscle_mass_percentage') . ':') !!}
                                {!! Form::number('muscle_mass_percentage', null, ['class' => 'form-control', 'step' => '0.01']) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('remarks', __('gym::lang.remarks') . ':') !!}
                                {!! Form::textarea('remarks', null, ['class' => 'form-control', 'rows' => 3]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 text-center">
                        <button type="submit" name="submit_action" value="save"
                            class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang('messages.save')</button>
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>
        @endcomponent
    </section>
    <section class="content-header">
        <h3>@lang('gym::lang.previous_health_record_of') {{ $member->name }}</h3>
    </section>
    <section class="content">
        @component('components.widget')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="health_table">
                    <thead>
                        <tr>
                            <th>@lang('gym::lang.date')</th>
                            <th>@lang('gym::lang.weight')</th>
                            <th>@lang('gym::lang.height')</th>
                            <th>@lang('gym::lang.bmi')</th>
                            <th>@lang('gym::lang.body_fat_percentage')</th>
                            <th>@lang('gym::lang.muscle_mass_percentage')</th>
                            <th>@lang('gym::lang.neck')</th>
                            <th>@lang('gym::lang.left_arm')</th>
                            <th>@lang('gym::lang.right_arm')</th>
                            <th>@lang('gym::lang.chest')</th>
                            <th>@lang('gym::lang.upper_waist')</th>
                            <th>@lang('gym::lang.lower_waist')</th>
                            <th>@lang('gym::lang.hips')</th>
                            <th>@lang('gym::lang.left_thigh')</th>
                            <th>@lang('gym::lang.right_thigh')</th>
                            <th>@lang('gym::lang.calf')</th>
                            <th>@lang('gym::lang.shoulders')</th>
                            <th>@lang('gym::lang.visceral_fat')</th>
                            <th>@lang('gym::lang.subcutaneous_fat')</th>
                            <th>@lang('gym::lang.remarks')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($health as $record)
                            <tr>
                                <td>{{ @format_date($record->date) }}</td>
                                <td>{{ $record->weight }}</td>
                                <td>{{ $record->height }}</td>
                                <td>{{ $record->bmi }}</td>
                                <td>{{ $record->body_fat_percentage }}</td>
                                <td>{{ $record->muscle_mass_percentage }}</td>
                                <td>{{ $record->neck }}</td>
                                <td>{{ $record->left_arm }}</td>
                                <td>{{ $record->right_arm }}</td>
                                <td>{{ $record->chest }}</td>
                                <td>{{ $record->upper_waist }}</td>
                                <td>{{ $record->lower_waist }}</td>
                                <td>{{ $record->hips }}</td>
                                <td>{{ $record->left_thigh }}</td>
                                <td>{{ $record->right_thigh }}</td>
                                <td>{{ $record->calf }}</td>
                                <td>{{ $record->shoulders }}</td>
                                <td>{{ $record->visceral_fat }}</td>
                                <td>{{ $record->subcutaneous_fat }}</td>
                                <td>{{ $record->remarks }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endcomponent

    </section>

    <!-- /.content -->
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var currentDate = new Date();
            var currentDateTime = moment(currentDate).format(moment_date_format);

            $('.health_date').datepicker({
                defaultDate: currentDateTime,
            }).datepicker('setDate', currentDateTime);

            $("form#create_health_tracking").validate();

            $('#health_table').DataTable({

            });
        });
    </script>
@endsection
