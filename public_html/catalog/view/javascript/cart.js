/**
* Class provides all functionality for Cart
* @author: Nikita Kruchinkin
* @e-mail: nikita.kruchinkin@gmail.com
* @version: 0.0.1
* @date: 30.05.18
**/

var Cart = {
  init: function() {
    console.log("Cart.init");
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

          $('#panel').before('<div class="alert"><div class="alert__text">' + json['success'] + '</div></div>');

          // Need to set timeout otherwise it wont update the total
          setTimeout(function () {
            $('#cart > button').html('<span id="cart-total"><i class="fa fa-shopping-cart"></i> ' + json['total'] + '</span>');
          }, 100);

          $('html, body').animate({ scrollTop: 0 }, 'slow');

          $('#cart > ul').load('index.php?route=common/cart/info ul li');
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