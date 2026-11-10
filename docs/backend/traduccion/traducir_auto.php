<?php

// Traduccion automatica espanol -> ingles en los paneles que tienen casillas en los dos idiomas.
// Va en includes/traducir_auto.php y se carga desde includes/footer.php (ver footer-snippet.txt),
// solo en los paneles: products, horns, about y presentaciones. NO en voluntarios (ahi la casilla
// "_en" guarda una cuenta de Instagram) ni en galleries (no tiene textos).
//
// Detecta solo los pares: toda casilla "algo_en" con su casilla "algo" en la misma pagina.
// Reglas: se traduce solo si la casilla en ingles esta vacia (nunca pisa lo escrito);
// al salir de la casilla en espanol se traduce sola, y hay un boton "Traducir" al lado.
// La traduccion la hace traducir.php (DeepL), que solo responde con la sesion del panel iniciada.
?>
<script>
  (function() {
    var pares = [];
    document.querySelectorAll('input[name$="_en"], textarea[name$="_en"]').forEach(function(en) {
      var base = en.name.slice(0, -3);
      var es = document.querySelector('[name="' + base + '"]');
      if (es && es !== en) pares.push({
        es: es,
        en: en
      });
    });

    pares.forEach(function(par) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'btn btn-sm btn-outline-secondary btn-traducir mb-2';
      btn.textContent = 'Traducir';
      var msg = document.createElement('span');
      msg.className = 'small text-secondary ms-2';
      par.en.insertAdjacentElement('afterend', msg);
      par.en.insertAdjacentElement('afterend', btn);

      function vacio() {
        return par.en.value.trim() === '';
      }

      function actualizar() {
        btn.disabled = !vacio();
        btn.title = vacio() ? '' : 'Para volver a traducir, borrá el texto en inglés.';
      }

      function traducir() {
        if (!vacio() || par.es.value.trim() === '') return;
        msg.textContent = 'Traduciendo...';
        fetch('traducir.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'fetch'
            },
            body: 'text=' + encodeURIComponent(par.es.value)
          })
          .then(function(r) {
            return r.json();
          })
          .then(function(data) {
            if (data.ok) {
              // Si mientras tanto alguien escribio en ingles, no se pisa.
              if (vacio()) par.en.value = data.text;
              msg.textContent = 'Traducido. Revisalo antes de guardar.';
            } else {
              msg.textContent = data.error || 'No se pudo traducir.';
            }
            actualizar();
          })
          .catch(function() {
            msg.textContent = 'No se pudo traducir.';
          });
      }

      actualizar();
      btn.addEventListener('click', traducir);
      par.en.addEventListener('input', function() {
        actualizar();
        msg.textContent = '';
      });
      // Al salir de la casilla en espanol, se traduce sola si el ingles esta vacio.
      par.es.addEventListener('blur', traducir);
    });
  })();
</script>
