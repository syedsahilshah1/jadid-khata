<div class="pos-tab-content">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('tax_label_1', __('business.tax_1_name') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    {!! Form::text('tax_label_1', $business->tax_label_1, ['class' => 'form-control','placeholder' => __('business.tax_1_placeholder')]); !!}
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('tax_number_1', __('business.tax_1_no') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    {!! Form::text('tax_number_1', $business->tax_number_1, ['class' => 'form-control']); !!}
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('tax_label_2', __('business.tax_2_name') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    {!! Form::text('tax_label_2', $business->tax_label_2, ['class' => 'form-control','placeholder' => __('business.tax_1_placeholder')]); !!}
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('tax_number_2', __('business.tax_2_no') . ':') !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    {!! Form::text('tax_number_2', $business->tax_number_2, ['class' => 'form-control']); !!}
                </div>
            </div>
        </div>
        <div class="col-sm-8">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('enable_inline_tax', 1, $business->enable_inline_tax , 
                    [ 'class' => 'input-icheck']); !!} {{ __( 'lang_v1.enable_inline_tax' ) }}
                  </label>
                </div>
            </div>
        </div>
    </div>
<div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <label for="enable_fbr_taxpayer_system">Enable FBR TaxPayer System:*</label>
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-info"></i>
                    </span>
                    <select class="form-control select2_register fbr_taxpayer_check valid" required="" style="width:100%;" onchange="showFields();" name="enable_fbr_taxpayer" aria-required="true" aria-invalid="false" fdprocessedid="5d8arr">
                        <option value="1" {{$business->enable_fbr_taxpayer==1 ? 'selected' : ''}}>Yes</option>
                        <option value="0" {{$business->enable_fbr_taxpayer==1 ? '' : 'selected'}} >No</option>
                    </select>
                </div>
            </div>
        </div>
      
            <div class="col-sm-4 fbr_details">
                <div class="form-group">
                    <label for="pos_id_label">POS ID:</label>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-suitcase"></i>
                        </span>
                        <input class="form-control" placeholder="Given by FBR" name="pos_id" type="text" value="{{$business->pos_id}}" fdprocessedid="wdj5xa">
                    </div>
                </div>
            </div>
            <div class="col-sm-4 fbr_details">
                <div class="form-group">
                    <label for="pos_token_label">POS Token:</label>
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-lock"></i>
                        </span>
                        <input class="form-control" placeholder="Given by FBR" name="pos_token" type="text" value="{{$business->pos_token}}" fdprocessedid="2ercwa">
                    </div>
                </div>
            </div>
      
    </div>
</div>

@section('javascript')
<script>
    function showFields() {
        if($('.fbr_taxpayer_check').val() == 1) {
            $('.fbr_details').css({'display': 'block'});
        } else {
            $('.fbr_details').css({'display': 'none'});
        }
    }
    $(document).ready(function() {
            showFields();
    });
</script>
@endsection

