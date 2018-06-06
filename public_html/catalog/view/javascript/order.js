/**
* Class provides all functionality for creating order
* @author: Nikita Kruchinkin
* @e-mail: nikita.kruchinkin@gmail.com
* @version: 0.0.1
* @date: 30.05.18
**/

var Order = {
  
  payment_status: false, // Payment status
  total: 0, // Curent price of order
  sub_total: 0,
  sale:0, // Sale amount
  payment_method: '', // Selected payment method
  shipping_method: '', // Selected shipping method
  fields: {},

  /**
  * Initialization of Order class. 
  * Need to be called on window.ready to register all callbacks and hooks
  **/
  init: function() {
    Order = this; 
    order_table = $('#order-table');

    Order.payment_method = $('#payment_method').val();
    Order.shipping_method = $('#shipping_method').val();

    Order.fields.firstname = $('.order__fields').find('input[name="firstname"]');
    Order.fields.lastname = $('.order__fields').find('input[name="lastname"]');
    Order.fields.phone = $('.order__fields').find('input[name="telephone"]');
    Order.fields.address = $('.order__fields').find('textarea[name="address"]');
    
    Order.$total = $('.order__total span');
    Order.$submit = $('#order__submit');
    Order.$products = $('.order__products');


    // Changing product count
    
    Order.$products.on('change', '.spinner__input', function(e){
      console.log('spinner__input change');
      var $row = $(this).closest('.order__table-row'),
          cart_id = $row.data('cart-id'),
          price = $row.data('price'),
          quantity = $(this).val();

      var total = price*quantity;
      
      Order.setProductQuantity(cart_id,quantity);
      Order.updateRow($row,price,quantity);
      
      return false;
    });
    
    // Removing product from order
    $('.order__products .order__remove').click(function(e) {
      console.log('btn remove click');
      Order.deleteProduct($(this).closest('.order__table-row').data('cart'));
      return false;
    });
    

    $('.order').on('change', '.radioblock__input[name="shipping_method"]', function(e){
      console.log('shipping_method changed');
      var method = $(this).val();
      Order.setShippingMethod(method);
    });

    $('.order').on('change', '.radioblock__input[name="payment_method"]', function(e){
      console.log('payment_method changed')
      var method = $(this).val();
      Order.setPaymentMethod(method);
    });


    // Submitting order
    Order.$submit.click(function(e){
      if (Order.validate()) {
        Order.submit();
      }
      return false;
    });
  },


  /**
  * Setting shipping method (delivery or pickup)
  * @param code - shipping method code
  */
  setShippingMethod: function(code) {
    console.log('setShippingMethod '+code);
    
    $.ajax({
      method: 'POST',
      url: '/index.php?route=checkout/checkout/setShippingMethod',
      data: {
        code: code,
      },
      dataType: 'json',
      beforeSend: function(xhr, opts){
        Order.disable();
      },
      success: function (data) {
        console.log(data);
        if (data.status) {
           Order.updateTotals();
           Order.showAddress(data.delivery);
        } else {
          Order.error(data.error);  
        }
        Order.enable();
      },
    });
  },

  /**
  * Setting payment method (delivery or pickup)
  * @param code - shipping method code
  */
  setPaymentMethod: function(code) {
    console.log('setPaymentMethod '+code);
    
    $.ajax({
      method: 'POST',
      url: '/index.php?route=checkout/checkout/setPaymentMethod',
      data: {
        code: code,
      },
      dataType: 'json',
      beforeSend: function(xhr, opts){
        Order.disable();
      },
      success: function (data) {
        console.log(data);
        
        if (data.status) {
        } else {
          Order.error(data.error);  
        }

        Order.enable();
      },
    });
  },

  /**
  * Setting shipping method (delivery or pickup)
  * @param code - shipping method code
  */
  confirmPayment: function(method,order_id) {
    $.ajax({ 
      type: 'get',
      url: '/index.php?route=/extension/payment/'+method+'/confirm',
      success: function() {
        location = '/index.php?route=checkout/success';
      }
    });
  },
  
  /**
  * Setting product quantity in cart
  * @param cart_id - uniq identifier of product in card
  * @param quantity - new quantity of product
  */ 
  setProductQuantity: function (cart_id, quantity) {
    console.log('set product quantity: '+cart_id+', quantity: '+quantity);
    
    $.ajax({
      method: 'POST',
      url: '/index.php?route=common/cart/edit',
      data: {
        key: cart_id,
        quantity: quantity,
      },

      dataType: 'json',
      beforeSend: function(xhr, opts){
        Order.disable();
      },
      success: function (data) {
        
        console.log(data);
       
        Cart.refresh(data.total,true); // Refresh cart
        Order.updateTotals(); // Update order totals
        Order.enable();
        
      },
      error: function(xhr, ajaxOptions, thrownError) {
        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
    });
  },
  updateRow: function(row,price,quantity) {
    console.log('updateRow');
    var $price = row.find('.order__cell--total');
    var _total = price*quantity;
    _total = Cart.formatPrice(_total);
    $price.html(_total+Cart.currency);
  },
  
  /**
  * DeleteProduct from cart
  * @param id 
  */ 
  deleteProduct: function (id) {
    //console.log('call delete product '+id);
    block = this.getCartBlockById(id);
    
    $.ajax({
      method: 'POST',
      url: '/index.php?route=common/cart/remove',
      data: {
        key: id,
      },
      dataType: 'json',
      beforeSend: function(xhr, opts){
        Order.disable();
      },
      success: function (data) {
        block.hide('slow', function(){
          $(this).remove();
          cart.refresh();
          Order.updateTotals();
          Order.enable();
        });
      },
      error: function(xhr, ajaxOptions, thrownError) {
        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
    });
  },

    // Counting totals of order
  updateTotals: function() {
    console.log('updateTotals');
    
    $.ajax({
      method: 'POST',
      url: '/index.php?route=checkout/checkout/getOrderTotals',
      dataType: 'json',
      success: function (json) {
        console.log(json['totals']);
        $order__totals=$('#order__totals');
        
        $order__totals.empty();
        if (json['totals']) {
          for (i in json['totals']) {
            var total = json['totals'][i];
            var _class ='';

            if (total['code']=='sub_total') {
              Order.sub_total=total['value'];
            } else if (total['code']=='total') {
              Order.total=total['value'];
              _class=' order__total--total';
            } else if (total['code']=='total') {
              Order.sale=total['value'];
              _class=' order__total--sale';
            }

            if (total['value']!=0) { // Display in table only not zero-values 
              $('<div/>',{
                appendTo: $order__totals,
                'class': 'order__total'+_class,
                html: total['title']+': <span>'+total['text']+'</span>'
              });
            }
          }
        }
        
      },error: function(xhr, ajaxOptions, thrownError) {
        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
    });
  },

  // Clear current order and reload page
  clear: function () {
    $.ajax({
      method: 'POST',
      url: '/index.php?route=checkout/checkout/clear',
      dataType: 'json',
      success: function (data) {  
        if (data.status) {
          location.reload(); 
        }
      },
    });
  },

  // Order validation
  validate: function() {
    $('.input-group__input--error').removeClass('input-group__input--error');
    
    if (Order.fields.firstname.val() == '') {
      $('#firstname').addClass('input-group__input--error');
    }

    if (Order.fields.lastname.val() == '') {
      $('#lastname').addClass('input-group__input--error');
    }

    if (Order.fields.phone.val() == '') {
      $('#telephone').addClass('input-group__input--error');
    }


    if ($('.input-group__input--error').length) {
      Order.error('Заполните обязательные поля');
      return false;
    }

    if (Order.fields.phone.val().length  != 18) {
      Order.error('Полe "Телефон" заполнено не коррертно!');
      $('#telephone').addClass('input-group__input--error');
      return false;
    }

    if (Order.shipping_method=='courier') {
      if (Order.fields.address.val() == '') {
        Order.error('Заполните поле "Адрес"');
        Order.fields.address.addClass('input-group__input--error');
        return false;
      }
    } 

    return true;
  },

  showAddress: function(display) {
    console.log('showAddress: '+display);
    Order.fields.address.parent().toggleClass('input-group--hidden',!display);
  },

  disable: function() {
    Order.$submit.prop('disabled', true);
    Order.$submit.addClass('btn--disable');
  },
  
  enable: function() {
    Order.$submit.prop('disabled', false);
    Order.$submit.removeClass('btn--disable');
  },

  // Submit funcion
  submit: function() {
    $.ajax({
      method: 'POST',
      url: '/index.php?route=checkout/confirm',
      data: $('.order__form').serialize(),
      dataType: 'json',
      success: function (data) {
        if (!data.status) {
          $.each(data.errors, function(k,v) {
            Order.error(v);
          });
          return false;
        }
        
        order_id = data.order_id;
        
        if (!Order.payment_status) {
          Order.confirmPayment(Order.payment_method, order_id);
          return false;
        }

      },
      error: function(xhr, ajaxOptions, thrownError) {
        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
    });
  },

  success: function(_text) {
    console.log('cart.alert success');
    $('.alert').remove();
    
    $('<div/>',{
      appendTo: $('body'),
      'class' : 'alert alert--success',
      html : [
        $('<div/>',{
          'class' : 'container',
          html: [
            $('<div/>',{
              'class': 'alert__text',
              html: _text
            }),
            $('<div/>',{
              'class': 'alert__closer'
            }),
          ]
        }),
      ]
    });
    $('.alert').addClass('alert--active');
  },

  error: function(_text) {
    console.log('cart.alert error');
    $('.alert').remove();
    
    $('<div/>',{
      appendTo: $('body'),
      'class' : 'alert alert--error',
      html : [
        $('<div/>',{
          'class' : 'container',
          html: [
            $('<div/>',{
              'class': 'alert__text',
              html: _text
            }),
            $('<div/>',{
              'class': 'alert__closer'
            }),
          ]
        }),
      ]
    });
    $('.alert').addClass('alert--active');
  },
}

$(function (){
  console.log('pre-init');
  var $buttons = $('.order__buttons');
  var $products = $('.order__products');
  $products.after($buttons);

  Order.init();
  console.log('after-init');
});