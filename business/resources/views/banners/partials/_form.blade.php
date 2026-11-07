<div class="row">
    <div class="col-sm-4">
        {!! Form::label('upload_image', __('core.upload_banner_image')) !!}
    </div>
    <div class="col-sm-8">
        <div class="form-group">
            {!! Form::file('image', ['class' => 'form-control', 'accept' => 'image/*']) !!}
        </div>

        <div id="imagePreview" style="margin-top: 10px;">
            <img id="previewImage" 
                 src="{{ asset($banner->image ?? '') }}" 
                 alt="Image Preview" 
                 style="margin-bottom: 16px;max-width: 100%; {{ $banner->image_url ? '' : 'display: none;' }}">
        </div>
    </div>
    <div class="col-sm-4">
      {!! Form::label('status', __('core.status') . ':*') !!}
  </div>
  <div class="col-sm-8">
      <div class="form-group">
          {!! Form::select('is_active', ['1' => 'Active', '0' => 'Inactive'], old('status', $banner->is_active), ['class' => 'form-control', 'required',
      'placeholder' => __('core.status')]); !!}
      </div>
  </div>
    <div class="text-center">
        <button type="submit" class="btn btn-primary text-center">@lang('core.save')</button>
    </div>
</div>