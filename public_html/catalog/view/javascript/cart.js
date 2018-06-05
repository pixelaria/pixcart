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
  currency:'р.',

  init: function() {
    console.log("cart.init");
    
    Cart.$cart = $('#cart'); //get cart object
    Cart.$toolbar = $('.toolbar'); //get cart object
    Cart.$toggler = $('.cart__toggler');
    Cart.$toggler_data = $('.cart__toggler span');
    
    if (Cart.$cart.find('.cart__empty').length==0) {
      Cart.empty=false;
      Cart.count = Cart.$cart.find('.cart__row').length;
      Cart.initBaron();
    }

    Cart.$cart.on('change', '.spinner__input', function(e){
      var $row = $(this).closest('.cart__row'),
          cart_id = $row.data('cart-id'),
          price = $row.data('price'),
          quantity = $(this).val();

      var total = price*quantity;
      Cart.update(cart_id,quantity,false);
      Cart.updateRow($row,price,quantity);
      return false;
    });

    Cart.$cart.on('click', '.cart__remove', function(e){
      Cart.remove($(this).closest('.cart__row').attr('data-cart-id'));
      return false;
    });


    Cart.$cart.on('click','.spinner__button', function(e){
      console.log('.spinner__button');
      var $spinner = $(this).closest('.spinner');
      var $target = $spinner.find('.spinner__input');
      var $placeholder = $spinner.find('.spinner__placeholder');
      console.log($target);
      
      var change = 1,min = 1, max=200, uom = 'шт.';
      if ($target.data('change') != undefined) change = parseInt($target.data('change'));
      if ($target.data('min') != undefined) min = parseInt($target.data('min'));
      if ($target.data('max') != undefined) max = parseInt($target.data('max'));
      if ($target.data('uom') != undefined) uom = $target.data('uom');
      
      var val;
      
      if ($(this).hasClass('spinner__button--up')) {
        val=parseInt($target.val()) + change;
      } else {
        val=parseInt($target.val()) - change;
      }
      
      if (val<min) val=min;
      if (val>max) val=max;

      $target.val(val).change();
      $target.trigger('change');
      $placeholder.html(val+' '+uom);
      console.log(val+' '+uom);
      return false;
    });
  },

  initBaron: function() {
    console.log('cart.initBaron');
    Cart.$baron = $('.baron');
    Cart.$products = $('.cart__products');
    
    if (Cart.count > 4) 
      Cart.$products.css('height','240px')

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
          Cart.count = json['count'];
          Cart.success(json['success']);
          Cart.refresh(json['total'],true);
        }
      },
      error: function(xhr, ajaxOptions, thrownError) {
        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
    });
  },
  update: function(key, quantity,redraw) {
    console.log('cart.update');
    $.ajax({
      url: 'index.php?route=common/cart/edit',
      type: 'post',
      data: 'key=' + key + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
      dataType: 'json',
      success: function(json) {
        // Need to set timeout otherwise it wont update the total
        Cart.count = json['count'];
        Cart.refresh(json['total'],redraw);
      },
      error: function(xhr, ajaxOptions, thrownError) {
        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
    });
  },
  updateRow: function(row,price,quantity) {
    var $price = row.find('.cart__cell--total');
    var _total = price*quantity;
    _total = Cart.formatPrice(_total);
    $price.html(_total+Cart.currency);
  },
  remove: function(key) {
    console.log('cart.remove');
    $.ajax({
      url: 'index.php?route=common/cart/remove',
      type: 'post',
      data: 'key=' + key,
      dataType: 'json',
      success: function(json) {
        Cart.count = json['count'];
        
        if (Cart.count != 0)
          Cart.refresh(json['total']);
        else 
          Cart.clear(json['total']);
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

  refresh: function(_total,redraw) {
    console.log('cart.refresh');
    // Need to set timeout otherwise it wont update the total
    /*
    setTimeout(function () {
      Cart.$toggler_data.html(_total);
    }, 100);
    */
    
    Cart.$toggler_data.html(_total);
    // Loads cart content into Baron or creates Baron
    if (redraw) {
      if (Cart.empty) {
        $('#cart').load('index.php?route=common/cart/info #cart >', function() {
          Cart.initBaron();
        });
        Cart.empty = false;
        Cart.$toggler.addClass('cart__toggler--not-empty');
      } else {
        $( "#cart .baron__scroller" ).load('index.php?route=common/cart/info .baron__scroller > .cart__row', function() {
          if (Cart.count > 4) 
            Cart.$products.css('height','240px')
          Cart.$baron.update();
        });
      }
    }
  },
  clear: function(_total) {
    console.log('cart.clear');
    Cart.$toggler_data.html(_total);
    Cart.$toggler.removeClass('cart__toggler--not-empty');
    Cart.$cart.empty();
    Cart.$cart.removeClass('cart--active');
    Cart.$toolbar.removeClass('toolbar--active');
    $('<div/>',{
      appendTo: Cart.$cart,
      class: 'cart__empty',
      html: 'В корзине пусто!'
    });
  },
  formatPrice: function(price) {
    price += "";
    price = new Array(4 - price.length % 3).join("U") + price;
    return price.replace(/([0-9U]{3})/g, "$1 ").replace(/U/g, "");
  }
}

$(function (){
  console.log('pre-init');
  Cart.init();
  console.log('after-init');
});