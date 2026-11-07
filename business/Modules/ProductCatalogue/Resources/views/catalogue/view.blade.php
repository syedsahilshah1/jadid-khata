@extends('layouts.guest')
@section('title', 'View Cart')


@section('content')
<style>
    .cart-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
        border-bottom: 1px solid #ddd;
        padding-bottom: 10px;
    }
  
    .cart-item img {
        width: 100px;
        height: auto;
    }
  
    .details {
        flex-grow: 1;
        margin-left: 20px;
    }
  
    .text-right {
        display: flex;
        align-items: center;
    }
  
    .quantity-control {
        display: flex;
        align-items: center;
        margin-right: 15px;
    }
  
    .quantity-control button {
        border: 1px solid #ccc;
        background-color: #fff;
        padding: 5px;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
  
    .quantity-control span {
        display: inline-block;
        width: 30px;
        text-align: center;
    }
  
    .remove-btn {
        background: none;
        border: none;
        color: red;
        font-size: 20px;
        cursor: pointer;
    }
  
    .cart-summary {
        text-align: right;
        margin-top: 20px;
    }
  
    .checkout-btn {
        text-align: right;
        margin-top: 20px;
    }
  </style>

  <div class="container mt-5">
    <h2>Shopping Cart</h2>
    @foreach($itemss as $product)
    <div class="cart-item" data-variation-id="{{ $product['variation_id'] }}" data-location-id="{{ $product['location_id'] }}" data-business-id="{{ $product['business_id'] }}">
        <img src="{{ $product['image'] }}" alt="Product Image">
        <div class="details">
            <h5>{{ $product['name'] }}</h5>
            <p>{{$business->currency->symbol}} {{ number_format($product['price'], 2) }}</p>
        </div>
        <div class="text-right">
            <div class="quantity-control">
                <button class="minus" data-variation-id="{{ $product['variation_id'] }}"><i class="fa fa-minus"></i></button>
                <span>{{ $product['quantity'] }}</span>
                <button class="plus" data-variation-id="{{ $product['variation_id'] }}"><i class="fa fa-plus"></i></button>
            </div>

            <a class="update-cart-btn" data-variation-id="{{ $product['variation_id'] }}">
                <i class="fa fa-sync"></i>
                <span class="spinner" style="display: none;">
                    <i class="fa fa-spinner fa-spin"></i>
                </span>
            </a>
            
            <!-- Delete Button -->
            <button class="remove-btn" data-variation-id="{{ $product['variation_id'] }}">
                <i class="fa fa-trash"></i>
                <span class="spinner" style="display: none;">
                    <i class="fa fa-spinner fa-spin"></i>
                </span>
            </button>
        </div>
    </div>
@endforeach

    

    <!-- Add the Update Cart button -->

    {{-- <form id="main-form" action="{{ route('submit-order') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="contact_id" id="contact-id">
        <input type="hidden" name="business_id" value="{{ $business->id }}">

        <div id="cart-items-container"></div> 
        <button type="submit" style="display: none;">Submit</button>
    </form> --}}

    <form id="main-form" action="{{ route('submit-order') }}" style="display: none;" method="POST">
        @csrf
    
        <!-- Hidden Inputs -->
        <input type="hidden" name="type" value="sales_order">
        <input type="hidden" name="location_id" value="{{ $business_location->id }}">
        <input type="hidden" name="business_id" value="{{ $business->id }}">
        @foreach($cartItemsWithDetails as $index => $product)
        <input type="hidden" name="products[{{ $index }}][product_type]" value="{{ $product['product_type'] }}">
        <input type="hidden" name="products[{{ $index }}][product_id]" value="{{ $product['product_id'] }}">
        <input type="hidden" name="products[{{ $index }}][variation_id]" value="{{ $product['variation_id'] }}">
        <input type="hidden" name="products[{{ $index }}][quantity]" value="{{ $product['quantity'] }}">
        <input type="hidden" name="products[{{ $index }}][unit_price]" value="{{ $product['unit_price'] }}">
        <input type="hidden" name="products[{{ $index }}][unit_price_inc_tax]" value="{{ $product['unit_price_inc_tax'] }}">
        <input type="hidden" name="products[{{ $index }}][line_discount_amount]" value="{{ $product['line_discount_amount'] }}">
        <input type="hidden" name="products[{{ $index }}][line_discount_type]" value="{{ $product['line_discount_type'] }}">
        <input type="hidden" name="products[{{ $index }}][item_tax]" value="{{ $product['item_tax'] }}">
        <input type="hidden" name="products[{{ $index }}][tax_id]" value="{{ $product['tax_id'] }}">
    @endforeach
        <input type="hidden" name="hidden_price_group" value="0">
        <input type="hidden" name="price_group" value="0">
        <input type="hidden" name="sale" value="Shop Order">
        <input type="hidden" name="default_price_group" value="">
        <input type="hidden" name="contact_id" id="contact-id">
        <input type="hidden" name="pay_term_number" value="12">
        <input type="hidden" name="pay_term_type" value="months">
        <input type="hidden" name="transaction_date" value="01/08/2025 02:43">
        <input type="hidden" name="status" value="ordered">
        <input type="hidden" name="invoice_no" value="">
        <input type="hidden" name="search_product" value="">
        <input type="hidden" name="sell_price_tax" value="includes">
        <input type="hidden" name="discount_type" value="percentage">
        <input type="hidden" name="discount_amount" value="0.00">
        <input type="hidden" name="rp_redeemed" value="0">

        <input type="hidden" name="rp_redeemed_amount" value="0">
        <input type="hidden" name="tax_rate_id" value="">
        <input type="hidden" name="tax_calculation_amount" value="0.00">
        <input type="hidden" name="sale_note" value="">
        <input type="hidden" name="is_direct_sale" value="1">
        <input type="hidden" name="shipping_details" value="">
        <input type="hidden" name="shipping_address" value="">
        <input type="hidden" name="shipping_charges" value="0.00">
        <input type="hidden" name="shipping_status" value="">
        <input type="hidden" name="delivered_to" value="">
        <input type="hidden" name="delivery_person" value="">
        <input type="hidden" name="additional_expense_key_1" value="">
        <input type="hidden" name="additional_expense_value_1" value="0">
        <input type="hidden" name="additional_expense_key_2" value="">
        <input type="hidden" name="additional_expense_value_2" value="0">
        <input type="hidden" name="additional_expense_key_3" value="">
        <input type="hidden" name="additional_expense_value_3" value="0">
        <input type="hidden" name="additional_expense_key_4" value="">
        <input type="hidden" name="additional_expense_value_4" value="0">
        <input type="hidden" name="final_total" value="{{ $total }}">
        <input type="hidden" name="is_save_and_print" value="1">
        <input type="hidden" name="recur_interval" value="">
        <input type="hidden" name="recur_interval_type" value="days">
        <input type="hidden" name="recur_repetitions" value="">
        <input type="hidden" name="subscription_repeat_on" value="">
    
        <!-- Cart Items Container -->
        <div id="cart-items-container"></div>
    
        <!-- Submit Button -->
        <button style="display: none;" type="submit">Submit</button>
    </form>
    
    


  <div class="cart-summary">
      <p>Subtotal: {{$business->currency->symbol}} {{ $total }}</p>
      <h4>Grand Total: {{$business->currency->symbol}} {{ $total }}</h4>
  </div>

  <div class="checkout-btn">
    <button class="btn btn-success" id="checkout-btn">Submit</button>
  </div>
</div>



<div id="contact-modal" class="modal" tabindex="-1" role="dialog" aria-labelledby="contact-modal-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title" id="contact-modal-label">Enter Contact Information</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="modal-body">
              <form id="contact-form">
                  <div class="form-group">
                      <label for="contact-name">Name</label>
                      <input type="text" class="form-control" id="contact-name" required>
                  </div>
                  <div class="form-group">
                      <label for="contact-email">Email</label>
                      <input type="email" class="form-control" id="contact-email" required>
                  </div>
                  <input type="hidden" name="business_id" value="{{ $business->id }}" id="businessid">
                  <div class="form-group">
                    <label for="contact-name">Address</label>
                    <input type="text" class="form-control" id="contact-adress" required>
                </div>
                  <div class="form-group">
                      <label for="contact-phone">Phone</label>
                      <input type="text" class="form-control" id="contact-phone" required>
                  </div>
                  <div class="form-group">
                    <label for="payment-type">Payment Type</label><br>
                    <input type="radio" id="cash-on-delivery" name="payment-type" value="cash-on-delivery">
                    <label for="cash-on-delivery">Cash on Delivery</label><br>
                    <input type="radio" id="bank-transfer" name="payment-type" value="bank-transfer">
                    <label for="bank-transfer">Bank Transfer</label>
                </div>
            
                <!-- Bank Account Number (Hidden initially) -->
                <div class="form-group" id="bank-account-field" style="display: none;">
                    <label for="bank-account">Bank Account Number</label>
                <p id="bank-account">{{ $business->bank_account }}</p> <!-- Static Bank Account Number -->
                </div>
                  <button type="button" class="save-contact-btn btn btn-primary">Save</button>
              </form>
          </div>
      </div>
  </div>
</div>


<!-- /.content -->
<!-- Add currency related field-->
<input type="hidden" id="__code" value="{{$business->currency->code}}">
<input type="hidden" id="__symbol" value="{{$business->currency->symbol}}">
<input type="hidden" id="__thousand" value="{{$business->currency->thousand_separator}}">
<input type="hidden" id="__decimal" value="{{$business->currency->decimal_separator}}">
<input type="hidden" id="__symbol_placement" value="{{$business->currency->currency_symbol_placement}}">
<input type="hidden" id="__precision" value="{{$business->currency_precision}}">
<input type="hidden" id="__quantity_precision" value="{{$business->quantity_precision}}">
<div class="modal fade product_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
@stop
@section('javascript')



<script>


$(document).ready(function() {


  console.log(2);
    // Open modal when checkout button is clicked
    $('#checkout-btn').on('click', function() {
        $('#contact-modal').modal('show');
    });


    $('input[name="payment-type"]').on('change', function() {
        if ($(this).val() === 'bank-transfer') {
            // Show the bank account field with the number when Bank Transfer is selected
            $('#bank-account-field').show();
        } else {
            // Hide the bank account field if Cash on Delivery is selected
            $('#bank-account-field').hide();
        }
    });
    // Handle saving the contact information when 'Save' button is clicked
    $('.save-contact-btn').on('click', function(event) {
        event.preventDefault(); // Prevent default form submission
        console.log(3);

        var name = $('#contact-name').val();
        var email = $('#contact-email').val();
        var phone = $('#contact-phone').val();
        var businessid = $('#businessid').val();
        var address = $('#contact-adress').val();


        if (name && email && phone) {
            $.ajax({
                url: '/save-contact', 
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}", 
                    name: name,
                    email: email,
                    phone: phone,
                    business_id: businessid,
                    address_line_1: address,

                },
                success: function(response) {
                    var contactId = response.contact_id;

                    $('<input>').attr({
                        type: 'hidden',
                        id: 'contact-id',
                        name: 'contact_id',
                        value: contactId
                    }).appendTo('#main-form');

                    // Close the modal
                    $('#contact-modal').modal('hide');

                    $('#main-form').submit();
                },
                error: function(response) {
                    toastr.error('Error saving contact information.');
                }
            });
        } else {
            toastr.warning('Please fill out all fields.');
        }
    });
});



</script>
<script type="text/javascript">

    (function($) {
    $(document).ready( function() {
        //Set global currency to be used in the application
        __currency_symbol = $('input#__symbol').val();
        __currency_thousand_separator = $('input#__thousand').val();
        __currency_decimal_separator = $('input#__decimal').val();
        __currency_symbol_placement = $('input#__symbol_placement').val();
        if ($('input#__precision').length > 0) {
            __currency_precision = $('input#__precision').val();
        } else {
            __currency_precision = 2;
        }

        if ($('input#__quantity_precision').length > 0) {
            __quantity_precision = $('input#__quantity_precision').val();
        } else {
            __quantity_precision = 2;
        }

        //Set page level currency to be used for some pages. (Purchase page)
        if ($('input#p_symbol').length > 0) {
            __p_currency_symbol = $('input#p_symbol').val();
            __p_currency_thousand_separator = $('input#p_thousand').val();
            __p_currency_decimal_separator = $('input#p_decimal').val();
        }

        __currency_convert_recursively($('.content'));
    });

    $(document).on('click', '.show-product-details', function(e){
        e.preventDefault();
        $.ajax({
            url: $(this).data('href'),
            dataType: 'html',
            success: function(result) {
                $('.product_modal')
                    .html(result)
                    .modal('show');
                __currency_convert_recursively($('.product_modal'));
            },
        });
    });

    $(document).on('click', '.menu', function(e){
        e.preventDefault();
        $('.navbar-toggle').addClass('collapsed');
        $('.navbar-collapse').removeClass('in');

        var cat_id = $(this).attr('href');
        if ($(cat_id).length) {
            $('html, body').animate({
                scrollTop: $(cat_id).offset().top
            }, 1000);
        }
    });

    })(jQuery);

    $(window).scroll(function() {
        var height = $(window).scrollTop();

        if(height  > 180) {
            $('nav').addClass('navbar-fixed-top');
            $('.scrolltop:hidden').stop(true, true).fadeIn();
        } else {
            $('nav').removeClass('navbar-fixed-top');
            $('.scrolltop').stop(true, true).fadeOut();
        }
    });

    $(document).on('click', '.scroll', function(e){
        $("html,body").animate({scrollTop:$("#top").offset().top},"1000");
        return false;
    });

    
</script>




<script>
$(document).ready(function() {
    // Handle quantity change (minus/plus buttons)
    $('.quantity-control button').on('click', function() {
        var $button = $(this);
        var $quantitySpan = $button.siblings('span');
        var quantity = parseInt($quantitySpan.text());
        
        // Update quantity
        if ($button.hasClass('plus')) {
            quantity++;
        } else if ($button.hasClass('minus')) {
            quantity = quantity > 1 ? quantity - 1 : 1;
        }
        $quantitySpan.text(quantity);
    });


    $('.remove-btn').on('click', function() {
    var $button = $(this);
    var variationId = $button.data('variation-id');

    // Show the spinner and hide the trash icon
    $button.find('i.fa-trash').hide(); // Hide trash icon
    $button.find('.spinner').show(); // Show spinner

    $.ajax({
        url: '/delete-cart-item', // Change to your delete URL
        method: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            variation_id: variationId
        },
        success: function(response) {
            toastr.success(response.message);

            // After success, stop the spinner and reload the page after 2 seconds
            setTimeout(function() {
                location.reload();
            }, 2000);
        },
        error: function(response) {
            if (response.status === 400) {
                toastr.error(response.responseJSON.error);
            } else {
                toastr.error('Error deleting the cart item');
            }
        },
        complete: function() {
            // Hide the spinner and show the trash icon again after the request is complete
            $button.find('.spinner').hide(); // Hide spinner
            $button.find('i.fa-trash').show(); // Show trash icon
        }
    });
});

    // Handle Update Cart button click
    $('.update-cart-btn').on('click', function() {
    var $button = $(this);
    var variationId = $button.data('variation-id');
    var quantity = $button.closest('.cart-item').find('.quantity-control span').text();

    // Show the spinner and hide the sync icon
    $button.find('i.fa-sync').hide(); // Hide sync icon
    $button.find('.spinner').show(); // Show spinner

    $.ajax({
        url: '/update-cart',
        method: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            variation_id: variationId,
            quantity: quantity
        },
        success: function(response) {
            toastr.success(response.message);

            // After success, stop the spinner and reload the page after 2 seconds
            setTimeout(function() {
                location.reload();
            }, 2000);
        },
        error: function(response) {
            if (response.status === 400) {
                toastr.error(response.responseJSON.error);
            } else {
                toastr.error('Error updating the cart');
            }
        },
        complete: function() {
            // Hide the spinner and show the sync icon again after the request is complete
            $button.find('.spinner').hide(); // Hide spinner
            $button.find('i.fa-sync').show(); // Show sync icon
        }
    });
});

});



</script>
@endsection
