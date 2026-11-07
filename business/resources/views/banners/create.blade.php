@extends('layouts.app')
@section('title', __('business.banners'))

@section('content')

<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('business.add_new_banner')</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>
<section class="content">
    {!! Form::open(['url' => route('banners.store'), 'method' => 'post', 'id' => 'add_category_form', 'class' => 'category_form', 'files' => true ]) !!}
    @csrf
    <div class="row">
        <div class="col-md-8">
            @component('components.widget')
                <div class="row">
                    <!-- Business Name (Optional) -->
                    {{-- <div class="col-sm-12">
                        <div class="form-group">
                            {!! Form::label('business_title', __('business.name')) !!}
                            {!! Form::text('name', old('name', $banner->name), ['class' => 'form-control ' . ($errors->has('name') ? 'is-invalid' : ''), 'placeholder' => __('business.business_name')]); !!}
                            @error('name')
                                <span class="error text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
    
                    <div class="col-sm-12">
                        <div class="form-group">
                            {!! Form::label('cta_text', __('business.cta_text')) !!}
                            {!! Form::text('cta_text', old('cta_text', $banner->cta_text ?? ''), ['class' => 'form-control ' . ($errors->has('cta_text') ? 'is-invalid' : ''), 'placeholder' => __('business.cta_text_placeholder')]); !!}
                            @error('cta_text')
                                <span class="error text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div> --}}

                    <!-- CTA Link (Optional) -->
                    <div class="col-sm-12">
                        <div class="form-group">
                            {!! Form::label('cta', __('business.cta_link')) !!}
                            {!! Form::text('cta', old('cta', $banner->cta ?? ''), ['class' => 'form-control ' . ($errors->has('cta') ? 'is-invalid' : ''), 'placeholder' => __('business.cta_link_placeholder')]); !!}
                            @error('cta')
                                <span class="error text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
    
                    <!-- Order (Optional) -->
                    <div class="col-sm-12">
                        <div class="form-group">
                            {!! Form::label('order', __('business.order')) !!}
                            {!! Form::number('order', old('order', $banner->order), ['class' => 'form-control ' . ($errors->has('order') ? 'is-invalid' : ''), 'placeholder' => __('business.business_order')]); !!}
                            @error('order')
                                <span class="error text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
    
                    <!-- Image (Required) -->
                    <div class="col-sm-12">
                        <div class="form-group">
                            {!! Form::label('image', __('business.image')) !!}
                            {!! Form::file('image', ['id' => 'image', 'class' => 'form-control ' . ($errors->has('image') ? 'is-invalid' : '')]); !!}
                            <img id="image-preview" style="height: 150px; margin-top: 10px;" src="{{ old('image', $banner->image ? asset($banner->image) : '') }}" alt="Image Preview" style="max-width: 100%; margin-top: 10px;">
                            @error('image')
                                <span class="error text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    
    
                </div>
            @endcomponent
        </div>
    
        <div class="col-md-4">
            @component('components.widget')
                @slot('title') @lang('business.publish') @endslot
                <div class="row">
                    <div class="col-sm-4">
                        {!! Form::label('status', __('business.status')) !!}
                    </div>
                    <div class="col-sm-8">
                        <div class="form-group">
                            {!! Form::select('is_active', ['1' => 'Active', '0' => 'Inactive'], old('is_active', $banner->is_active), ['class' => 'form-control ' . ($errors->has('is_active') ? 'is-invalid' : ''), 'placeholder' => __('business.status')]); !!}
                            @error('is_active')
                                <span class="error text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>
            @endcomponent
        </div>
    </div>
    
    
    <div class="row">
        <div class="col-sm-12 text-center">
            <button type="submit" value="submit" class="btn btn-primary btn-big">@lang('business.submit')</button>
        </div>
    </div>
    {!! Form::close() !!}

</section>


@endsection

@section('javascript')

<script>
    $(document).ready(function() {
    // Trigger image preview when a file is selected
    $('#image').on('change', function() {
        var file = this.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#image-preview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });
});

</script>

@endsection