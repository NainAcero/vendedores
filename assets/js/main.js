$(document).ready(function() {
  // Cargar el carro
  function load_cart() {
    var load_wrapper = $('#load_wrapper'),
    wrapper = $('#cart_wrapper'),
    action = 'get';

    // Petición ajax
    $.ajax({
      url: 'ajax.php',
      type: 'POST',
      dataType: 'JSON',
      data:
      {
        action
      },
      beforeSend: function() {
        load_wrapper.waitMe();
      }
    }).done(function(res){
      if(res.status === 200) {
        setTimeout(() => {
          wrapper.html(res.data);
          load_wrapper.waitMe('hide');
        }, 2000);
      } else {
        swal('Upps!','Ocurrió un error','error');
        wrapper.html('¡Intenta de nuevo, por favor!');
        return true;
      }
    }).fail(function(err){
      swal('Upps!','Ocurrió un error','error');
      return false;
    }).always(function(){
      
    });
  };
  load_cart();
  
  // Agregar al carro al dar clic en botón
  // Actualizar las cantidad del carro si el producto ya existe dentro
  $('.do_add_to_cart').on('click', function(event) {
    // Pueden utilizarlo para prevenir alguna acción
    // submit / redirección
    //<i class="fas fa-spinner"></i>
    event.preventDefault();
    var boton = $(this),
    id = $(this).data('id'),
    cantidad = $(this).data('cantidad'),
    action = 'post',
    old_label = boton.html(),
    spinner = '<i class="fas fa-spinner fa-spin d-block text-center"></i>';

    $.ajax({
      url: 'ajax.php',
      type: 'POST',
      dataType: 'JSON',
      cache: false,
      data:
      {
        action,
        id,
        cantidad
      },
      beforeSend: function() {
        boton.html(spinner);
      }
    }).done(function(res) {
      if(res.status === 201) {
        swal('!Bien hecho!','Producto agregado al carrito','success');
        load_cart();
        return;
      } else {
        swal('Upps!',res.msg, 'error');
        return;
      }
    }).fail(function(err) {
      swal('Upps!','Ocurrió un error','error');
    }).always(function() {
      setTimeout(() => {
        boton.html(old_label);
      }, 1500);
    });
  });
  
  // Actualizar carro con input
  $('body').on('blur','.do_update_cart' , do_update_cart);
  function do_update_cart(event) {
    var input = $(this),
    cantidad = parseInt(input.val()),
    id = input.data('id'),
    action = 'put',
    cant_original = parseInt(input.data('cantidad'));

    

    // Validar que sea un número integro
    if(Math.floor(cantidad) !== cantidad) {
      cantidad = 1;
    }

    // Validar que el número ingresado sea mayor a 0 y menor a 99
    if(cantidad < 1) {
      cantidad = 1;
    } else if(cantidad > 99) {
      cantidad = 99;
    }

    if(cantidad === cant_original) return false;

    $.ajax({
      url: 'ajax.php',
      type: 'POST',
      dataType: 'JSON',
      data:
      {
        action,
        id,
        cantidad
      }
    }).done(function(res) {
      if(res.status === 200) {
        swal('!Bien hecho!', 'Producto actualizado con éxito', 'success');
        load_cart();
        return;
      }
      else {
        swal('Upps!', res.msg, 'error');
        return;
      }
    }).fail(function(err) {
      swal('Upps!', 'Ocurrió un error', 'error');
    }).always(function() {
    });
  }
  
  // Borrar elemento del carro
  $('body').on('click','.do_delete_from_cart',delete_from_cart);
  function delete_from_cart(event) {
    var confirmation,
    id = $(this).data('id'),
    action = 'delete';

    confirmation = confirm('¿Estás seguro?');

    if (!confirmation) return;

    $.ajax({
      url: 'ajax.php',
      type: 'POST',
      dataType: 'JSON',
      data: {
        action,
        id
      }
    }).done(function (res) {
      if (res.status === 200) {
        swal('Producto borrado con éxito');
        load_cart();
        return;
      } else {
        swal('Upps!', res.msg, 'error');
        return;
      }
    }).fail(function (err) {
      swal('Upps!', 'Hubo un error, intenta de nuevo', 'error');
    }).always(function () {
    });
  }
  
  // Vaciar carro
  $('body').on('click', '.do_destroy_cart' , destroy_cart);
  function destroy_cart(event) {
    var confirmation,
    action = 'destroy';

    confirmation = confirm('¿Estás seguro?');

    if(!confirmation) return;

    $.ajax({
      url: 'ajax.php',
      type: 'POST',
      dataType: 'JSON',
      data:
      {
        action
      }
    }).done(function(res){
      if(res.status === 200) {
        swal('Carrito borrado con éxito');
        load_cart();
        return;
      } else {
        swal('Upps!', res.msg, 'error');
        return;
      }
    }).fail(function(err){
      swal('Upps!','Hubo un error, intenta de nuevo', 'error');
    }).always(function(){

    });
  }
  
  // Realizar el pago
  $('body').on('submit','#do_pay',do_pay);
  function do_pay(event) {
    event.preventDefault();
    var form = $(this),
    data = form.serialize(),
    action = 'pay';

    $.ajax({
      url: 'ajax.php',
      type: 'POST',
      dataType: 'JSON',
      data: {
        action,
        data
      },
      beforeSend: function() {
      }
    }).done(function (res) {
      if (res.status === 200) {
        $('body').waitMe();
        setTimeout(() => {
          $('body').waitMe('hide');
          load_cart();
          load_order_resume();
        }, 4500);
        return;
      } else {
        swal('Upps!', res.msg, 'error');
        return;
      }
    }).fail(function (err) {
      swal('Upps!', 'Hubo un error, intenta de nuevo', 'error');
    }).always(function () {
    });
  }

  // Resumen de compra
  function load_order_resume() {
    var action = 'order_resume';
    $.ajax({
      url: 'ajax.php',
      type: 'POST',
      dataType: 'json',
      data:
      {
        action
      }
    }).done(function(res){
      if(res.status === 200) {
        $('body').append(res.data);
        $('#order_resume').modal('show');
      }
    }).fail(function(err){

    });
  }
});