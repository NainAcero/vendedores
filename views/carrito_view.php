<?php require_once 'includes/inc_header.php' ?>
<?php require_once 'includes/inc_navbar.php' ?>

<!-- Content -->
<div class="container-fluid py-5">
  <div class="row">
    <!-- Products -->
    <div class="col-xl-8">
      <h1>Productos</h1>
      <div class="row">
        <?php foreach ($data['products'] as $p): ?>
        <div class="col-3 mb-3">
          <div class="card">
            <img src="<?php echo IMAGES.$p['imagen']; ?>" alt="<?php echo $p['nombre']; ?>" class="card-img-top">
            <div class="card-body p-2">
              <h5 class="card-title text-truncate"><?php echo $p['nombre'] ?></h5>
              <p class="text-success"><?php echo format_currency($p['precio']); ?></p>
              <button class="btn btn-sm btn-success do_add_to_cart" data-cantidad="1" data-id="<?php echo $p['id'] ?>" data-toggle="tooltip" title="Agregar al carrito"><i class="fas fa-plus"></i> Agregar al carrito</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Cart -->
    <div class="col-xl-4 bg-light" id="load_wrapper">
      <h1>Carrito</h1>
      <!-- Cart content -->
      <div id="cart_wrapper">
      </div>
    </div>
  </div>
</div>
<!-- END Content -->

<?php require_once 'includes/inc_footer.php' ?>