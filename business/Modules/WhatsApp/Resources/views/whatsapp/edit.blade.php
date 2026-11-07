<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
  
      {!! Form::open(['url' => action([\Modules\WhatsApp\Http\Controllers\WhatsAppController::class, 'update'], [$loadWhatsAppAccounts->id]), 'method' => 'PUT', 'id' => 'whatsapp_accounts_add_form' ]) !!}
  
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'whatsapp::lang.app_name' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="row">
          <div class="clearfix"></div>
          <div class="col-sm-6">
            <div class="form-group">
              {!! Form::label('sources', __( 'whatsapp::lang.sources' ) . ':*') !!}
            {!! Form::text('sources', $loadWhatsAppAccounts->sources, ['class' => 'form-control', 'placeholder' => __( 'whatsapp::lang.sources' ), 'required' ]); !!}
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              {!! Form::label('wa_server', __( 'whatsapp::lang.wa_server' ) . ':*') !!}
                {!! Form::text('wa_server', $loadWhatsAppAccounts->wa_server, ['class' => 'form-control', 'placeholder' => __( 'whatsapp::lang.wa_server' ), 'required' ]); !!}
            </div>
          </div>
          <div class="clearfix"></div>
          <div class="col-sm-6">
            <div class="form-group">
              {!! Form::label('is_default', __( 'whatsapp::lang.default' ) . ':*') !!}
              {!!Form::select('is_default', array('1' => 'Yes', '2' => 'No'), $loadWhatsAppAccounts->is_default, ['class' => 'form-control']) !!}
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              {!! Form::label('app_key', __( 'whatsapp::lang.app_key' ) . ':*') !!}
                {!! Form::text('app_key', $loadWhatsAppAccounts->app_key, ['class' => 'form-control', 'placeholder' => __( 'whatsapp::lang.app_key' ), 'required' ]); !!}
            </div>
          </div>

          <div class="col-sm-6">
            <div class="form-group">
              {!! Form::label('auth_key', __( 'whatsapp::lang.auth_key' ) . ':*') !!}
                {!! Form::text('auth_key', $loadWhatsAppAccounts->auth_key, ['class' => 'form-control', 'placeholder' => __( 'whatsapp::lang.auth_key'), 'required' ]); !!}
            </div>
          </div>
          <div class="col-sm-6">
            <div class="form-group">
              {!! Form::label('sender', __( 'whatsapp::lang.sender' ) . ':*') !!}
                {!! Form::number('sender', $loadWhatsAppAccounts->sender, ['class' => 'form-control', 'placeholder' => __( 'whatsapp::lang.sender'), 'required' ]); !!}
            </div>
          </div>
          <div class="clearfix"></div>
        
         
  
          <div class="clearfix"></div>
          
  
          <div class="clearfix"></div>
          
        

          <div class="clearfix"></div>
        </div>
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-yellow">@lang( 'messages.save' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->