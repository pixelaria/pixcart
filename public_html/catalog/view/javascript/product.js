/**
* Class provides all functionality for Product page
* @author: Nikita Kruchinkin
* @e-mail: nikita.kruchinkin@gmail.com
* @version: 0.0.1
* @date: 30.05.18
**/

var Product = {
  product_id:0,
  quantity:1,
  options,
  init: function() {
    console.log("product.init");
    
    Product.product_id=$('input[name="product_id"]').val();
    Product.quantity=1;
    Product.options=$('#options');

    $('#button-cart').click(function(e){
      Product.addToCart();
    });

    $('input[name="quantity"]').change(function(e){
        Product.quantity=$(this).val();
    });
  },

  addToCart: function() {
    console.log("product.addToCart");
    
    var _options =  Product.options.serialize();
    console.log(_options);

    $.ajax({
      url: 'index.php?route=common/cart/add',
      type: 'post',
      //data: $('#product input[type=\'hidden\']'),
      data: 'product_id=' + Product.product_id + '&quantity=' + Product.quantity,
      dataType: 'json',
      success: function(json) {
        console.log(json);
        
        /*
        if (json['error']) {
          if (json['error']['option']) {
            for (i in json['error']['option']) {
              var element = $('#input-option' + i.replace('_', '-'));
              element.parent().addClass('select--error');
              
              if (element.parent().hasClass('input-group')) {
                element.parent().after('<div class="text-danger">' + json['error']['option'][i] + '</div>');
              } else {
                element.after('<div class="text-danger">' +  + '</div>');
              }
            }
          }
          // Highlight any found errors
          $('.text-danger').parent().addClass('has-error');
        }
        */

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
  }
}

$(function (){
  console.log('pre-init');
  Product.init();
  console.log('after-init');
});