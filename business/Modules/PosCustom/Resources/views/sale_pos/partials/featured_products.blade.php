{{-- {{dump($featured_products)}} --}}
@foreach($featured_products as $variation)
	<div class="pos_product_list no-print"> {{-- #JCN change col-md-2 col-xs-3 for css pos_product_list--}}
		<div class="product_box btn-default_ff" data-toggle="tooltip" data-placement="bottom" data-variation_id="{{$variation->id}}" title="{{$variation->full_name}}">

		<div class="image-container" 
			style="background-image: url(
					@if(count($variation->media) > 0)
						{{$variation->media->first()->display_url}}
					@elseif(!empty($variation->product->image_url))
						{{$variation->product->image_url}}
					@else
						{{asset('/img/default.png')}}
					@endif
				);
			background-repeat: no-repeat; background-position: center;
			background-size: contain;">
			
		</div>

		<div class="text_div">
			<small class="text text-muted">{{$variation->product->name}} 
			@if($variation->product->type == 'variable')
				- {{$variation->name}}
			@endif
			</small>

			<small class="text-muted">
				({{$variation->sub_sku}})
				- {{$variation->quantity}}
			</small>
		</div>
			
		</div>
	</div>
@endforeach