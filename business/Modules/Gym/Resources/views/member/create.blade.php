@extends('layouts.app')
@section('title', __('gym::lang.members'))
@section('content')
    @include('gym::layouts.nav')
    <section class="content-header">
        <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black"> @lang('gym::lang.add_member')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget')
            <div class="row">
                <div class="col-md-12">
                    {!! Form::open([
                        'url' => action([\Modules\Gym\Http\Controllers\MemberController::class, 'store']),
                        'method' => 'post',
                        'id' => 'create_member',
                        'files' => true,
                    ]) !!}

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('prefix', __('business.prefix') . ':') !!}
                                {!! Form::text('prefix', null, ['class' => 'form-control', 'placeholder' => __('business.prefix_placeholder')]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('first_name', __('business.first_name') . ':*') !!}
                                {!! Form::text('first_name', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('business.first_name'),
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('middle_name', __('lang_v1.middle_name') . ':') !!}
                                {!! Form::text('middle_name', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.middle_name')]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('last_name', __('business.last_name') . ':') !!}
                                {!! Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('business.last_name')]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
                                {!! Form::text('mobile', null, ['class' => 'form-control', 'required', 'placeholder' => __('contact.mobile')]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('alternate_number', __('contact.alternate_contact_number') . ':') !!}
                                {!! Form::text('alternate_number', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('contact.alternate_contact_number'),
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('landline', __('contact.landline') . ':') !!}
                                {!! Form::text('landline', null, ['class' => 'form-control', 'placeholder' => __('contact.landline')]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('email', __('business.email') . ':') !!}
                                {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('business.email')]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('dob', __('lang_v1.dob') . ':') !!}
                                {!! Form::text('dob', null, [
                                    'class' => 'form-control dob-date-picker',
                                    'placeholder' => __('lang_v1.dob'),
                                    'readonly',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('gender', __('gym::lang.gender') . ':') !!}
                                {!! Form::select('gym_member_gender', $genders, null, ['class' => 'form-control', 'placeholder' => __('gym::lang.gender')]); !!}
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <hr>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('address_line_1', __('lang_v1.address_line_1') . ':') !!}
                                {!! Form::text('address_line_1', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => __('lang_v1.address_line_1'),
                                    'rows' => 3,
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('address_line_2', __('lang_v1.address_line_2') . ':') !!}
                                {!! Form::text('address_line_2', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('lang_v1.address_line_2'),
                                    'rows' => 3,
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('city', __('business.city') . ':') !!}
                                {!! Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('business.city')]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('state', __('business.state') . ':') !!}
                                {!! Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('business.state')]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('country', __('business.country') . ':') !!}
                                {!! Form::text('country',null, ['class' => 'form-control', 'placeholder' => __('business.country')]) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                {!! Form::label('zip_code', __('business.zip_code') . ':') !!}
                                {!! Form::text('zip_code', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('business.zip_code_placeholder'),
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Photo Upload -->
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('profile_photo', __('gym::lang.profile_photo') . ':') !!}
                                {!! Form::file('profile_photo', [
                                    'class' => 'form-control-file',
                                    'accept' => 'image/*', // Accepts only image files
                                ]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 text-center">
                        <input type="hidden" name="submit_type" id="submit_type">
                        <button type="submit" name="submit_action" value="save_and_health"
                            class="tw-dw-btn tw-text-white bg-purple submit_form">@lang('gym::lang.save_and_add_health')</button>
                        <button type="submit" name="submit_action" value="save"
                            class="tw-dw-btn tw-dw-btn-primary tw-text-white submit_form">@lang('messages.save')</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        @endcomponent
    </section>
    <!-- /.content -->
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.dob-date-picker').datepicker({
                autoclose: true,
                endDate: 'today',
            });

            $("form#create_member").validate();

            $(document).on('click', '.submit_form', function(e) {
                e.preventDefault();
                var submit_type = $(this).attr('value');
                $('#submit_type').val(submit_type);
                if ($('form#create_member').valid()) {

                    $('form#create_member').submit();
                }
            });
        });
    </script>
@endsection
