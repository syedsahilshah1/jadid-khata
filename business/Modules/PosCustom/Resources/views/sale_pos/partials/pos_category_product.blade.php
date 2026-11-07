@forelse($categories as $category)
		<button id="product_category" name="bt{{$category['id']}}" class="btn btn-prni btn-default_ff" type="button" value="{{$category['id']}}"  tabindex="-1" title="{{$category['name']}}">
			<img src="{{asset('/img/' . $category['id'] .'.jpg')}}" class="img-tumbnail">
			<div class="text_div" >      
				<small class="text text-muted" style="color:#e04b59">{{$category['name']}}</small>
			</div>
		</button>	
@empty
	<input type="hidden" id="no_products_found">
		<div class="col-md-12">
			<h4 class="text-center">
				@lang('lang_v1.no_products_to_display')
			</h4>
		</div>
@endforelse



