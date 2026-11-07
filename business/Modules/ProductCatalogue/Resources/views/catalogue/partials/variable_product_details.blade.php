<div class="row">
	<div class="col-md-12">
		<h4 class="tw-text-lg tw-font-bold tw-text-gray-900 tw-mb-4">@lang('product.variations'):</h4>
	</div>
	<div class="col-md-12">
		@php
			$settings = json_decode($business->productcatalogue_settings, true);
			$is_show = $settings['is_show'] ?? 1;
			$enable_whatsapp_ordering = $settings['enable_whatsapp_ordering'] ?? 0;
		@endphp
		@foreach($product->variations as $variation)
			@if ($is_show == 1  ||  ((!empty(json_decode($variation->variation_location_details, true) && $variation->variation_location_details->first()->qty_available > 0))))
				<div class="col-md-3 mb-12">
					@php
						$variation_image = !empty($variation->media->first()) ? $variation->media->first()->display_url : $product->image_url;
					@endphp
					@php
						$is_variation_out_of_stock = $product->enable_stock && ((empty(json_decode($variation->variation_location_details, true))) || (!empty(json_decode($variation->variation_location_details, true) && $variation->variation_location_details->first()->qty_available == 0)));
					@endphp

					<div class="tw-relative tw-bg-white tw-rounded-2xl tw-border tw-border-gray-200 tw-shadow-sm hover:tw-shadow-md tw-transition-all tw-overflow-hidden">
						<div class="tw-bg-gray-50 tw-border-b tw-border-gray-200 tw-p-3 tw-flex tw-items-center tw-gap-3">
							@if(!empty($variation_image))
								<div class="tw-w-12 tw-h-12 tw-rounded-xl tw-bg-white tw-border tw-border-gray-200 tw-overflow-hidden tw-flex tw-items-center tw-justify-center tw-flex-shrink-0">
									<img src="{{$variation_image}}" class="tw-w-full tw-h-full tw-object-contain tw-p-1" alt="Product image">
								</div>
							@endif

							<div class="tw-min-w-0">
								<div class="tw-font-bold tw-text-gray-900 tw-text-sm tw-truncate">
									{{$variation->product_variation->name}} - {{ $variation->name }}
								</div>
								<div class="tw-text-xs tw-text-gray-500 tw-mt-0.5 tw-truncate">
									@lang('product.sku'): {{$variation->sub_sku}}
								</div>
							</div>

							@if(!empty($discounts[$variation->id]))
								<span class="tw-absolute tw-top-3 tw-right-3 tw-inline-flex tw-items-center tw-rounded-full tw-bg-yellow-500 tw-text-white tw-text-xs tw-font-bold tw-px-2.5 tw-py-1 tw-shadow">
									- {{@num_format($discounts[$variation->id]->discount_amount)}}%
								</span>
							@endif
						</div>

						<div class="tw-p-4">
							<div class="tw-flex tw-items-end tw-justify-between tw-gap-3">
								<div>
									<div class="tw-text-xs tw-text-gray-500">@lang('lang_v1.price')</div>
									<div class="tw-text-lg tw-font-bold tw-text-primary-600">
										<span class="display_currency" data-currency_symbol="true" data-show_code="true">{{ $variation->sell_price_inc_tax }}</span>
									</div>
								</div>

								@if ($is_variation_out_of_stock)
									<span class="tw-inline-flex tw-items-center tw-rounded-full tw-bg-red-100 tw-text-red-700 tw-text-xs tw-font-semibold tw-px-3 tw-py-1">
										@lang('productcatalogue::lang.out_of_stock')
									</span>
								@endif
							</div>

							@if(!$is_variation_out_of_stock && $enable_whatsapp_ordering == 1)
								<div class="add-to-cart-section-variation tw-mt-4 tw-pt-4 tw-border-t tw-border-gray-200">
									<div class="tw-flex tw-justify-center">
										<div class="input-group tw-inline-flex tw-items-stretch tw-justify-center tw-gap-0 tw-bg-gradient-to-r tw-from-gray-50 tw-to-gray-100 tw-rounded-xl tw-p-1 tw-border tw-border-gray-200 tw-shadow-sm hover:tw-shadow-md tw-transition-all tw-duration-200" style="max-width: 180px;">
											<button class="btn-minus-variation tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-bg-white tw-text-gray-700 tw-rounded-l-xl tw-border tw-border-gray-300 hover:tw-bg-primary-500 hover:tw-text-white hover:tw-border-primary-500 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-primary-500 focus:tw-ring-offset-1 tw-transition" 
													type="button" 
													data-product-id="{{$product->id}}" 
													data-variation-id="{{$variation->id}}"
													aria-label="Decrease quantity">
												<i class="fas fa-minus tw-text-xs"></i>
											</button>

											<input type="number" 
												   class="product-quantity-variation tw-w-16 tw-h-10 tw-text-center tw-font-bold tw-text-gray-900 tw-bg-white tw-border-y tw-border-gray-300 focus:tw-outline-none focus:tw:ring-2 focus:tw:ring-primary-500 focus:tw:ring-offset-1 tw-text-sm tw-appearance-none tw-flex-shrink-0 tw-transition" 
												   value="0" 
												   min="0" 
												   inputmode="numeric"
												   data-product-id="{{$product->id}}" 
												   data-variation-id="{{$variation->id}}" 
												   data-product-name="{{$product->name}}" 
												   data-variation-name="{{$variation->product_variation->name}} - {{$variation->name}}" 
												   data-price="{{$variation->sell_price_inc_tax}}" 
												   data-image="{{$variation_image}}" 
												   data-sku="{{$variation->sub_sku}}">

											<button class="btn-plus-variation tw-inline-flex tw-items-center tw-justify-center tw-w-10 tw-h-10 tw-bg-white tw-text-gray-700 tw-rounded-r-xl tw-border tw-border-gray-300 hover:tw-bg-primary-500 hover:tw-text-white hover:tw-border-primary-500 focus:tw-outline-none focus:tw:ring-2 focus:tw:ring-primary-500 focus:tw:ring-offset-1 tw-transition" 
													type="button" 
													data-product-id="{{$product->id}}" 
													data-variation-id="{{$variation->id}}"
													aria-label="Increase quantity">
												<i class="fas fa-plus tw-text-xs"></i>
											</button>
										</div>
									</div>
								</div>
							@endif
						</div>
					</div>
				</div>
			@endif
		@endforeach
	</div>
</div>