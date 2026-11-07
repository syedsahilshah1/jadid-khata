<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action('\Modules\FieldForce\Http\Controllers\FieldForceController@update', [$visit->id]), 'method' => 'put', 'id' => 'add_visit_form']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">@lang( 'fieldforce::lang.edit_visit' ) (#{{$visit->visit_id}})</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <h4>@lang( 'fieldforce::lang.whom_you_will_be_visiting' )*</h4>
                       </div>
                       <div class="checkbox">
                           <label class="radio-inline">{!! Form::radio('visit_to_type', 'contact', !empty($visit->contact_id), ['class' => 'input-icheck']) !!} @lang('contact.contact')</label>
                           <label class="radio-inline">{!! Form::radio('visit_to_type', 'did_not_meet_contact', empty($visit->contact_id), ['class' => 'input-icheck']) !!} @lang('lang_v1.others')</label>
                       </div>
                    </div>
                    <div style="margin-left: 15px">
    @php
        $contacts = !empty($visit->contact) ? [$visit->contact->id => $visit->contact->name] : [];
    @endphp

    <div class="form-group">
        {!! Form::label('contact_id', __('report.contact') . ':') !!}
        <br>
        <input type="hidden" name="contact_id" id="contact_id" value="{{ !empty($visit->contact) ? $visit->contact->id : '' }}">
        <input type="text" class="form-control" id="contact_select" placeholder="{{ __('messages.please_select') }}" autocomplete="off" style="width: 100%" value="{{ !empty($visit->contact) ? $visit->contact->name : '' }}">
        <div id="contact_suggestions" class="d-none"></div>
    </div>
</div>


                    <div id="others_div" @if(!empty($visit->contact_id)) class="hide" @endif >
                        <div class="clearfix"></div>
                        <div class="col-md-6">
                           <div class="form-group">
                                {!! Form::label('visit_to', __('fieldforce::lang.person_company') . ':' )!!}
                                {!! Form::text('visit_to', $visit->visit_to, ['class' => 'form-control']) !!}
                           </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {!! Form::label('visit_address', __('fieldforce::lang.visit_address') . ':') !!}
                                {!! Form::textarea('visit_address', $visit->visit_address, ['class' => 'form-control ', 'id' => 'visit_address', 'rows' => 3]); !!}
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('assigned_to', __('lang_v1.assigned_to') .':*') !!}
                            {!! Form::select('assigned_to', $users, $visit->assigned_to, ['class' => 'form-control select2', 'required', 'style' => 'width: 100%;', 'placeholder' => __('messages.please_select')]); !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('visit_on', __('fieldforce::lang.visit_on') . ':*' )!!}
                            {!! Form::text('visit_on', @format_datetime($visit->visit_on), ['class' => 'form-control', 'required', 'readonly']) !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('visit_for', __('fieldforce::lang.visit_for') . ':') !!}
                            {!! Form::textarea('visit_for', $visit->visit_for, ['class' => 'form-control ', 'id' => 'visit_for', 'rows' => 3]); !!}
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