<!-- Modal Delete -->
<form action="<?= $file_main ?>" method="post">
  <div class="modal" tabindex="-1" aria-labelledby="modal" aria-hidden="true" id="modal">
    <div class="modal-dialog modal-dialog-centered text-dark">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">¡Atención!</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          ¿Está seguro de eliminar este item?
        </div>
        <div class="modal-footer">
          <input type="hidden" name="id" id="id">
          <input type="hidden" name="file" id="file">
          <input type="hidden" name="tipo" id="tipo">
          <input type="hidden" name="id_sec" id="id_sec">
          <input type="hidden" name="title" id="title">

          <input type="hidden" name="delete" value="1">
          <input type="hidden" name="action" value="Eliminar">
          <button type="submit" class="btn btn-primary">Eliminar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</form>

<footer class="p-3 p-lg-5 p-md-4 text-muted">
  <div class="row py-3 border-top border-secondary">
    <div class="col-6 small ">
      <strong> <?= $_SESSION['client'] ?> Backend </strong>
    </div>
    <div class="d-flex col-6 justify-content-end">
      <i class="bi bi-heart-fill"></i> <span class="small ms-1">Hecho durante 2025</span>
    </div>
  </div>
</footer>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="js/bootstrap.min.js"></script>

<script>
  $(document).ready(function() {
    $(".delete").click(function(e) {
      $("#modal").modal("show");
      let id = $(this).data("id");
      let id_sec = $(this).data("sec");
      let file = $(this).data("file");
      let title = $(this).data("title");
      let tipo = $(this).data("tipo");

      $("#id").val(id);
      $("#id_sec").val(id_sec);
      $("#file").val(file);
      $("#title").val(title);
      $("#tipo").val(tipo);
    });
  });
</script>
<script>
  // Campos de numero: botones - y + siempre visibles y grandes (sirven tambien en el celular,
  // donde las flechitas del navegador no aparecen). Respetan min, max y step del campo.
  (function() {
    document.querySelectorAll('input[type="number"]').forEach(function(input) {
      if (input.closest('.num-wrap')) return;
      var wrap = document.createElement('div');
      wrap.className = 'num-wrap mb-2';
      input.parentNode.insertBefore(wrap, input);
      function boton(texto, etiqueta, accion) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'num-btn';
        b.textContent = texto;
        b.setAttribute('aria-label', etiqueta);
        b.addEventListener('click', function() {
          if (input.disabled || input.readOnly) return;
          if (input.value === '') input.value = input.min !== '' ? input.min : 0;
          else accion();
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
        });
        return b;
      }
      wrap.appendChild(boton('\u2212', 'Restar', function() { input.stepDown(); }));
      wrap.appendChild(input);
      wrap.appendChild(boton('+', 'Sumar', function() { input.stepUp(); }));
    });
  })();
</script>
<?php
// ---- Agregar AL FINAL de includes/footer.php (despues del </script> que ya tiene) ----
// Traduccion automatica es -> en, solo en los paneles que tienen casillas en los dos idiomas.
if (isset($file_main) && in_array($file_main, ['products', 'horns', 'about', 'presentaciones'], true)) {
  include __DIR__ . '/traducir_auto.php';
}
?>
