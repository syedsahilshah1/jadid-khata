<div class="row catalogue-banner-row" style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e5e7eb;">
    {!! Form::hidden('catalogue_banner_keys[]', $banner_key) !!}
    {!! Form::hidden("existing_catalogue_banner_image[$banner_key]", $banner['image'] ?? null) !!}
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label("catalogue_banner_image_$banner_key", 'Banner Image:') !!}
            {!! Form::file("catalogue_banner_image[$banner_key]", ['accept' => 'image/*', 'class' => 'form-control', 'id' => "catalogue_banner_image_$banner_key"]) !!}
            @if(!empty($banner['image']))
                <div class="existing-banner-preview" style="margin-top: 10px;">
                    <img src="{{ asset('uploads/productcatalogue_banners/' . $banner['image']) }}" alt="Banner" style="max-width: 100%; max-height: 120px; border-radius: 8px;">
                </div>
                <div class="checkbox remove-existing-banner-wrap" style="margin-top: 8px;">
                    <label>
                        {!! Form::checkbox("remove_catalogue_banner[$banner_key]", 1, false, ['class' => 'input-icheck']) !!} Remove current image
                    </label>
                </div>
            @endif
        </div>
    </div>
    <div class="col-md-7">
        <div class="form-group">
            {!! Form::label("catalogue_banner_title_$banner_key", 'Title:') !!}
            {!! Form::text("catalogue_banner_title[$banner_key]", $banner['title'] ?? null, ['class' => 'form-control', 'placeholder' => 'Optional banner title', 'id' => "catalogue_banner_title_$banner_key"]) !!}
        </div>
        <div class="form-group">
            {!! Form::label("catalogue_banner_subtitle_$banner_key", 'Subtitle:') !!}
            {!! Form::text("catalogue_banner_subtitle[$banner_key]", $banner['subtitle'] ?? null, ['class' => 'form-control', 'placeholder' => 'Optional short subtitle', 'id' => "catalogue_banner_subtitle_$banner_key"]) !!}
        </div>
        <div class="form-group">
            {!! Form::label("catalogue_banner_link_$banner_key", 'Link:') !!}
            {!! Form::text("catalogue_banner_link[$banner_key]", $banner['link'] ?? null, ['class' => 'form-control', 'placeholder' => 'Optional URL or #category target', 'id' => "catalogue_banner_link_$banner_key"]) !!}
        </div>
    </div>
    <div class="col-md-1" style="padding-top: 30px;">
        <button type="button" class="tw-dw-btn tw-dw-btn-error tw-dw-btn-sm remove-catalogue-banner-row">Remove</button>
    </div>
</div>
