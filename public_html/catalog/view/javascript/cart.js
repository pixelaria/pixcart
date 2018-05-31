/**
* Class provides all functionality for Cart
* @author: Nikita Kruchinkin
* @e-mail: nikita.kruchinkin@gmail.com
* @version: 0.0.1
* @date: 30.05.18
**/

var Cart = {
  baron: false,
  init: function() {
    console.log("Cart.init");
    var $baron = $('.baron');
    var height = $baron.data('height');
    if (height>0) 
      $baron.css('height',height+'px')

    Cart.baron = baron({
      root: '.baron',
      scroller: '.baron__scroller',
      bar: '.baron__bar',
      scrollingCls: '_scrolling',
      draggingCls: '_dragging'
    });

    Cart.baron.controls({
      track: '.baron__track',
      forward: '.baron__down',
      backward: '.baron__up'
    });
    
  },
  add: function(product_id, quantity) {
    $.ajax({
      url: 'index.php?route=common/cart/add',
      type: 'post',
      data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
      dataType: 'json',
      beforeSend: function() {},
      complete: function() {},
      success: function(json) {
        $('.alert').remove();
        
        if (json['redirect']) {
          location = json['redirect'];
        }

        if (json['success']) {
          $('#panel').before('<div class="alert alert--active"><div class="container"><div class="alert__text">' + json['success'] + '</div><div class="alert__closer"></div></div></div>');
          
          setTimeout(function () {
            var $alert = $('.alert');
            $alert.removeClass('alert--active');
            setTimeout(function() {
              $alert.remove()
            }, 350);
          },3000);

          // Need to set timeout otherwise it wont update the total
          setTimeout(function () {
            $('.cart__toggler span').html(json['total']);
          }, 100);

          $('html, body').animate({ scrollTop: 0 }, 'slow');

          $('#cart .baron__scroller').load('index.php?route=common/cart/info .baron__scroller > .cart__row');

          setTimeout(function () {
            Cart.baron.update();
          }, 100);

          
        }
      },
      error: function(xhr, ajaxOptions, thrownError) {
        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
    });
  },
  update: function(key, quantity) {
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
  }
}

$(function (){
  console.log('pre-init');
  Cart.init();
  console.log('after-init');
});