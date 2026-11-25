<?php

// Orden por posicion para los paneles (productos, hornos, residentes, galerias, imagenes, Nosotros).
// Va en utilities/orden.php. La posicion 1 es la primera que se ve en la web.
// En la base se guarda en la columna `top` (el valor mas alto va primero, como lee la API),
// numerando seguido todas las del mismo grupo.

// Ids del grupo en el orden en que los muestra la web. Se usa la misma consulta que la API
// (ORDER BY top DESC, sin desempate propio) para que, al numerar por primera vez, el orden
// que ya se ve en la web no cambie. $tabla y $where son fijos del panel, los valores van como parametros.
function orden_ids($pdo, $tabla, $where, $params = [])
{
  $statement = $pdo->prepare("SELECT * FROM $tabla WHERE $where ORDER BY top DESC");
  $statement->execute($params);
  return array_map('intval', array_column($statement->fetchAll(PDO::FETCH_ASSOC), 'id'));
}

// Pone el registro $id en la posicion elegida y numera seguido todo el grupo.
function orden_mover($pdo, $tabla, $where, $params, $id, $posicion)
{
  $id = intval($id);
  $ids = array_values(array_filter(orden_ids($pdo, $tabla, $where, $params), function ($x) use ($id) {
    return $x !== $id;
  }));
  $posicion = max(1, min(count($ids) + 1, intval($posicion)));
  array_splice($ids, $posicion - 1, 0, [$id]);
  $n = count($ids);
  $statement = $pdo->prepare("UPDATE $tabla SET top = ? WHERE id = ?");
  foreach ($ids as $i => $rid) {
    $statement->execute([$n - $i, $rid]);
  }
}

// Selector de posicion (1 a $n). $sel es la posicion marcada.
function orden_selector($n, $sel)
{
  $n = max(1, intval($n));
  $html = '<select name="top" id="top" class="form-select bg-transparent text-white mb-2">';
  for ($p = 1; $p <= $n; $p++) {
    $texto = $p . '&deg;';
    if ($p == 1) $texto .= ' (la primera)';
    else if ($p == $n) $texto .= ' (la última)';
    $html .= '<option value="' . $p . '"' . ($p == intval($sel) ? ' selected' : '') . '>' . $texto . '</option>';
  }
  return $html . '</select>';
}
