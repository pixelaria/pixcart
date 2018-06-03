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
    Order.fields.phone = $('.order__fields').find('input[name="phone"]');
    Order.fields.address = $('.order__fields').find('input[name="address"]');
    
    
    // Changing product count
    $('.order__products').on('change', '.spinner__input', function(e){
      //console.log('spinner input change');
      Order.setProductQuantity($(this).closest('tr').data('cart-id'), $(this).val());
      return false;
    });

    // Removing product from order
    $('.order__products .order__remove').click(function(e) {
      //console.log('btn remove click');
      Order.deleteProduct($(this).closest('tr').data('cart-id'));
      return false;
    });
    

    $('.order__products').on('change', '.radioblock__input[name="shipping_method"]', function(e){
      var method = $(this).val();
      Order.setShippingMethod(method);
    });

    $('.order__products').on('change', '.radioblock__input[name="payment_method"]', function(e){
      var method = $(this).val();
      Order.setPaymentMethod(method);
    });

    // Submitting order
    $('.order__form').submit(function(){
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
      success: function (data) {
        console.log(data);
        if (data.status) {
           Order.updateTotals();
        } else {
          Order.error(data.error);  
        }
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
      success: function (data) {
        console.log(data);
        if (data.status) {
        } else {
          Order.error(data.error);  
        }
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
    //console.log('set product quantity');
    //Order.disableForm();
    $.ajax({
      method: 'POST',
      url: '/index.php?route=common/cart/edit',
      data: {
        cart_id: cart_id,
        quantity: quantity,
      },
      dataType: 'json',
      success: function (data) {
        //console.log(data);
        bl = Order.getCartBlockById(cart_id);
        bl.find('.cart__price').html(data.total);
        cart.refresh(); // обновляем виджет картины
        Order.updateTotals();
        Order.enableForm();
      },
    });
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
      success: function (data) {
        block.hide('slow', function(){
          $(this).remove();
          cart.refresh();
          Order.updateTotals();
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
        //console.log(json['totals']);
        order__totals=$('#order__totals');
        var html = '';

        if (json['totals']) {
              for (i in json['totals']) {
                total = json['totals'][i];

                if (total['code']=='sub_total') 
                  Order.sub_total=total['value'];
                 if (total['code']=='total') 
                  Order.total=total['value'];

                if (total['value']!=0) { // Display in table only not zero-values 
                  var $class = 'order__totals';
                  var $subclass = '';
                  if (total['value']<0) $class +=' order__sale';
                  if (total['code']=='total') $subclass = ' order__total';
                  
                  html += '<div class="'+$class+$subclass+'">';
                  html += '  <span>'+total['title']+':</span><span>'+total['text']+'</span>';
                  html += '</div>';
                }
              }
            }
        
        order__totals.html(html);
        
        map.container.fitToViewport();
        //Order.enableForm();
      },error: function(xhr, ajaxOptions, thrownError) {
        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
    });
  },

  // Clear current order and reload page
  clear: function () {
    //console.log('clear order');
    $.ajax({
      method: 'POST',
      url: '/index.php?route=checkout/fscheckout/clearOrder',
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
      Order.error('Поля "Имя" и "Телефон" обязательны для заполнения!');
      $('#name').addClass('input-group__input--error');
      return false;
    }
    
    if (Order.fields.phone.val() == '') {
      Order.error('Поля "Имя" и "Телефон" обязательны для заполнения!')
      $('#telephone').addClass('input-group__input--error');
      return false;
    }

    if (Order.fields.firstname.val().length  != 18) {
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

  // Alerts (success, errors)
  alert: function(_text,_class) {
    console.log('cart.alert');
    $('.alert').remove();
    
    $('<div/>',{
      appendTo: $('body'),
      'class' : 'alert'+_class,
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
  Order.init();
  console.log('after-init');
});