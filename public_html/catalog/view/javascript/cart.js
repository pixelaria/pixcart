/**
* Class provides all functionality for Cart
* @author: Nikita Kruchinkin
* @e-mail: nikita.kruchinkin@gmail.com
* @version: 0.0.1
* @date: 30.05.18
**/

var Cart = {
  empty:true,
  count:0,

  init: function() {
    console.log("cart.init");
    
    Cart.$cart = $('#cart'); //get cart object
    Cart.$toggler = $('.cart__toggler span');
    
    if (Cart.$cart.find('.cart__empty').length==0) {
      Cart.empty=false;
      Cart.count = Cart.$cart.find('.cart__row').length;
      Cart.initBaron();
    }


    

  },

  initBaron: function() {
    console.log('cart.initBaron');
    Cart.$baron = $('.baron');
    
    var height = Cart.$baron.data('height');
    if (Cart.count > 4) 
      Cart.$baron.css('height','240px')

    Cart.$baron = baron({
      root: '.baron',
      scroller: '.baron__scroller',
      bar: '.baron__bar',
      scrollingCls: '_scrolling',
      draggingCls: '_dragging'
    });

    Cart.$baron.controls({
      track: '.baron__track',
      forward: '.baron__down',
      backward: '.baron__up'
    });
  },

  add: function(product_id, quantity) {
    console.log('cart.add');
    $.ajax({
      url: 'index.php?route=common/cart/add',
      type: 'post',
      data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
      dataType: 'json',
      beforeSend: function() {},
      complete: function() {},
      success: function(json) {

        if (json['redirect']) { //if product needs option to select
          location = json['redirect'];
        }

        if (json['success']) {
          Cart.count = json['count']
          Cart.alert(json['success'],'');
          
          setTimeout(function () {
            Cart.refresh(json['total']);
          }, 100);
        }
      },
      error: function(xhr, ajaxOptions, thrownError) {
        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
    });
  },
  update: function(key, quantity) {
    console.log('cart.update');
    $.ajax({
      url: 'index.php?route=common/cart/edit',
      type: 'post',
      data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
      dataType: 'json',
      beforeSend: function() {},
      complete: function() {},
      success: function(json) {
        // Need to set timeout otherwise it wont update the total
        setTimeout(function () {
          $('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
        }, 100);

        if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
          location = 'index.php?route=checkout/cart';
        } else {
          $('#cart > ul').load('index.php?route=common/cart/info ul li');
        }
      },
      error: function(xhr, ajaxOptions, thrownError) {
        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
    });
  },
  remove: function(key) {
    console.log('cart.remove');
    $.ajax({
      url: 'index.php?route=common/cart/remove',
      type: 'post',
      data: 'key=' + key,
      dataType: 'json',
      beforeSend: function() {},
      complete: function() {},
      success: function(json) {
        // Need to set timeout otherwise it wont update the total
        setTimeout(function () {
          $('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
        }, 100);

        if (getURLVar('route') == 'checkout/cart' || getURLVar('route') == 'checkout/checkout') {
          location = 'index.php?route=checkout/cart';
        } else {
          $('#cart > ul').load('index.php?route=common/cart/info ul li');
        }
      },
      error: function(xhr, ajaxOptions, thrownError) {
        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
    });
  },
  alert: function(_text,_class) {
    console.log('cart.alert');
    $('.alert').remove();
    
    $('<div/>',{
      appendTo: $('body'),
      'class' : 'alert alert--active '+_class,
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
    $('html, body').animate({ scrollTop: 0 }, 'slow');
  },

  refresh: function(_total) {
    console.log('cart.refresh');
    // Need to set timeout otherwise it wont update the total
    setTimeout(function () {
      Cart.$toggler.html(_total);
    }, 100);

    // Loads cart content into Baron or creates Baron
    if (Cart.empty) {
      $('#cart').load('index.php?route=common/cart/info #cart', function() {
        Cart.initBaron();
      });
      Cart.empty = false;
    } else {
      $( "#cart .baron__scroller" ).load('index.php?route=common/cart/info .baron__scroller > .cart__row', function() {
        if (Cart.count > 4) 
          Cart.$baron.css('height','240px')
        Cart.$baron.update();
      });
    }

  }
}

$(function (){
  console.log('pre-init');
  Cart.init();
  console.log('after-init');
});