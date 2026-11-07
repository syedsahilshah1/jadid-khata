<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action('\Modules\FieldForce\Http\Controllers\FieldForceController@postUpdateVisitStatus', [$visit->id]), 'method' => 'post', 'id' => 'update_visit_status_form', 'files' => true ]) !!}
        {!! Form::hidden('visit_id', $visit->id) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang( 'lang_v1.update_status' ) (#{{$visit->visit_id}})</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <h4>@lang( 'fieldforce::lang.did_you_meet_with_the_contact' )</h4>
                    </div>
                    <div class="checkbox">
                        <label class="radio-inline">{!! Form::radio('status', 'met_contact', false, ['class' => 'input-icheck', 'checked' => $visit->status == 'met_contact', 'required' => 'required']) !!} @lang('messages.yes')</label>
                        <label class="radio-inline">{!! Form::radio('status', 'did_not_meet_contact', false, ['class' => 'input-icheck' , 'checked' => $visit->status == 'did_not_meet_contact', 'required' => 'required']) !!} @lang('messages.no')</label>
                    </div>
                </div>
                <div class="col-md-12 @if($visit->status != 'did_not_meet_contact') hide @endif" id="reason_to_not_meet_contact">
                    <div class="form-group">
                        {!! Form::label('reason_to_not_meet_contact', __('fieldforce::lang.reason') . ':') !!}
                        {!! Form::textarea('reason_to_not_meet_contact', $visit->reason_to_not_meet_contact, ['class' => 'form-control ', 'id' => 'reason_to_not_meet_contact', 'rows' => 3]); !!}
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-6 mt-15">
                    <div class="form-group">
                        {!! Form::label('photo', __('fieldforce::lang.take_photo_of_contact') . ':*' )!!}
                        <input type="file" name="photo" accept="image/*" capture="user" id="photo" class="form-control">

                    </div>
                </div>
                <div class="col-md-6 mt-15">
                    <div class="form-group">
                        {!! Form::label('visited_on', __('fieldforce::lang.visited_on') . ':' )!!}
                        @if(!empty($visit->visted_on))
                        {{@format_datetime($visit->visted_on)}}
                        @else
                        {{@format_datetime('now')}}
                        @endif
                    </div>
                </div>
                <div class="clearfix"></div>

                <div class="col-md-12">
                    <div class="form-group">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang('fieldforce::lang.meet_with')</th>
                                    <th>@lang('fieldforce::lang.meet_with_mobileno')</th>
                                    <th>@lang('fieldforce::lang.designation')</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1*</td>
                                    <td>{!! Form::text('meet_with', $visit->meet_with, ['class' => 'form-control ', 'id' => 'meet_with', 'required' => 'required']); !!}</td>
                                    <td>{!! Form::number('meet_with_mobileno', $visit->meet_with_mobileno, ['class' => 'form-control ', 'id' => 'meet_with_mobileno', 'required' => 'required']); !!}</td>
                                    <td>{!! Form::text('meet_with_designation', $visit->meet_with_designation, ['class' => 'form-control ', 'id' => 'meet_with_designation', 'required' => 'required']); !!}</td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>{!! Form::text('meet_with2', $visit->meet_with2, ['class' => 'form-control ', 'id' => 'meet_with2']); !!}</td>
                                    <td>{!! Form::number('meet_with_mobileno2', $visit->meet_with_mobileno2, ['class' => 'form-control ', 'id' => 'meet_with_mobileno2']); !!}</td>
                                    <td>{!! Form::text('meet_with_designation2', $visit->meet_with_designation2, ['class' => 'form-control ', 'id' => 'meet_with_designation2']); !!}</td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>{!! Form::text('meet_with3', $visit->meet_with3, ['class' => 'form-control ', 'id' => 'meet_with3']); !!}</td>
                                    <td>{!! Form::number('meet_with_mobileno3', $visit->meet_with_mobileno3, ['class' => 'form-control ', 'id' => 'meet_with_mobileno3']); !!}</td>
                                    <td>{!! Form::text('meet_with_designation3', $visit->meet_with_designation3, ['class' => 'form-control ', 'id' => 'meet_with_designation3']); !!}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('visited_address', __('fieldforce::lang.visited_address') . ':') !!}
                        <button type="button" class="btn btn-primary btn-xs" id="get_current_location_ff"> <i class="fas fa-map-marker-alt"></i> @lang('fieldforce::lang.get_current_location')</button>
                        <br>
                        <div id="visited_address_div_ff">{{$visit->visited_address ?? ''}}</div>
                        {!! Form::hidden('visited_address_latitude', $visit->visited_address_latitude, ['id' => 'visited_address_latitude']); !!}
                        {!! Form::hidden('visited_address_longitude', $visit->visited_address_longitude, ['id' => 'visited_address_longitude']); !!}
                        {!! Form::hidden('visited_address', $visit->visited_address, ['id' => 'visited_address_ff']); !!}

                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('comments', __('fieldforce::lang.discussions_with_contact') . ':') !!}
                        {!! Form::textarea('comments', $visit->comments, ['class' => 'form-control ', 'id' => 'comments', 'rows' => 3]); !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

<!-- Initialize the Awesomplete instance on the input field -->
<script>
 $(document).ready(function() {
  var inputField = $('#contact_select');
  var awesomplete = new Awesomplete(inputField[0], {
    minChars: 1,
    maxItems: 10,
    autoFirst: true,
    replace: function(suggestion) {
      this.input.value = suggestion.label;
      $('#contact_id').val(suggestion.value);
    }
  });

  inputField.on('keyup', function() {
    var query = inputField.val();
    if (query.length > 0) {
      $.ajax({
        url: '/contacts/customers?query=' + encodeURIComponent(query),
        type: 'GET',
        dataType: 'json',
        success: function(response) {
          var suggestions = response.map(function(customer) {
            return {
              value: customer.id,
              label: customer.text
            };
          });
          if (suggestions.length > 0) {
            awesomplete.list = suggestions;
          } else {
            awesomplete.list = ["No Results found"];
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          console.log('Error:', textStatus, errorThrown);
        }
      });
    } else {
      awesomplete.list = [];
    }
  });
});
</script>