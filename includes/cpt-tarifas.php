<?php
if (!defined('ABSPATH')) exit;

/**
 * ==========================================================
 * CPT: Tarifas — versión con precios variables dinámicos
 * ==========================================================
 */
function tureserva_register_tarifas_cpt() {

    $labels = array(
        'name'               => 'Tarifas',
        'singular_name'      => 'Tarifa',
        'menu_name'          => 'Tarifas',
        'add_new'            => 'Añadir nueva',
        'add_new_item'       => 'Añadir nueva tarifa',
        'edit_item'          => 'Editar tarifa',
        'new_item'           => 'Nueva tarifa',
        'view_item'          => 'Ver tarifa',
        'search_items'       => 'Buscar tarifas',
        'not_found'          => 'No se encontraron tarifas',
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=tureserva_alojamiento',
        'supports'           => array('title'),
        'menu_position'      => 8,
        'show_in_rest'       => true,
    );

    register_post_type('tarifa', $args);
}
add_action('init', 'tureserva_register_tarifas_cpt');

/**
 * ==========================================================
 * METABOX
 * ==========================================================
 */
function tureserva_add_tarifas_metabox() {
    add_meta_box(
        'tureserva_tarifas_metabox',
        'Configuración de tarifas y precios variables',
        'tureserva_render_tarifas_metabox',
        'tarifa',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'tureserva_add_tarifas_metabox');

function tureserva_render_tarifas_metabox($post) {

    $temporadas = get_posts([
        'post_type'      => 'temporada',
        'posts_per_page' => -1,
        'post_status'    => 'publish'
    ]);

    $precios = get_post_meta($post->ID, '_tureserva_precios_variables', true);
    if (!is_array($precios)) $precios = [];

    wp_nonce_field('tureserva_save_tarifas', 'tureserva_tarifas_nonce');
?>
<style>
.tureserva-precios-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.tureserva-precio-item {
  background: #f7f7f7;
  border: 1px solid #ddd;
  border-radius: 10px;
  padding: 16px;
  position: relative;
}
.tureserva-grid {
  display: grid;
  grid-template-columns: 1fr 1.3fr 1.3fr 1.3fr 60px;
  gap: 14px;
  align-items: start;
}
.tureserva-box label {
  display: block;
  font-weight: 600;
  font-size: 13px;
  margin-bottom: 6px;
  color: #222;
}
.tureserva-box input,
.tureserva-box select {
  width: 100%;
  padding: 6px 10px;
  border: 1px solid #ccc;
  border-radius: 6px;
  background: #fff;
  font-size: 14px;
}
.tureserva-box-inline {display: flex; gap: 10px;}
.tureserva-box-inline div {flex: 1;}

.tureserva-especial {
  background: #f7f7f7;
  border-radius: 8px;
  padding: 12px;
  border: 1px solid #e2e2e2;
}
.tureserva-especial input {margin-bottom: 8px; width: 100%;}

.tureserva-delete {
  background: transparent;
  border: none;
  cursor: pointer;
  margin-top: 24px;
  padding: 0;
  transition: transform 0.2s ease;
}
.tureserva-delete svg {
  width: 22px; height: 22px; fill: #999;
  transition: fill 0.2s ease;
}
.tureserva-delete:hover svg { fill: #c00; transform: scale(1.1); }

.tureserva-add-variable {
  margin-top: 10px;
  background: #0073aa;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 16px;
  font-size: 14px;
  cursor: pointer;
  transition: background 0.2s ease;
}
.tureserva-add-variable:hover { background: #005f8d; }
</style>

<div id="tureserva-precios-wrapper" class="tureserva-precios-container">
  <?php if (!empty($precios)): ?>
    <?php foreach ($precios as $index => $precio): ?>
      <?php tureserva_render_precio_block($index, $precio, $temporadas); ?>
    <?php endforeach; ?>
  <?php else: ?>
    <?php tureserva_render_precio_block(0, [], $temporadas); ?>
  <?php endif; ?>
</div>

<button type="button" id="tureserva-add-variable" class="tureserva-add-variable">+ Agregar más precios variables</button>

<script>
jQuery(document).ready(function($){
  let blockIndex = <?php echo count($precios); ?>;

  function initBlockEvents(container){
    container.find('.activar-especial').on('change', function(){
      const enabled = $(this).is(':checked');
      $(this).closest('.tureserva-especial').find('input[type="number"]').prop('disabled', !enabled);
    });

    container.find('.tureserva-delete').on('click', function(){
      $(this).closest('.tureserva-precio-item').slideUp(200, function(){ $(this).remove(); });
    });
  }

  initBlockEvents($('#tureserva-precios-wrapper'));

  $('#tureserva-add-variable').on('click', function(){
    const template = $('#tureserva-precios-wrapper .tureserva-precio-item:first').clone();
    template.find('input, select').each(function(){
      const name = $(this).attr('name');
      const cleanName = name.replace(/\[\d+\]/, '[' + blockIndex + ']');
      $(this).attr('name', cleanName);
      if ($(this).is(':checkbox')) { $(this).prop('checked', false); }
      else { $(this).val(''); }
      if ($(this).is('[disabled]')) $(this).prop('disabled', true);
    });
    template.hide().appendTo('#tureserva-precios-wrapper').slideDown(250);
    initBlockEvents(template);
    blockIndex++;
  });

  $('#tureserva-precios-wrapper').sortable({
    handle: '.tureserva-precio-item',
    placeholder: 'sortable-placeholder',
    tolerance: 'pointer'
  });
});
</script>

<?php
}

function tureserva_render_precio_block($index, $precio, $temporadas){
  $temporada = $precio['temporada'] ?? '';
  $adultos = $precio['adultos'] ?? '';
  $ninos = $precio['ninos'] ?? '';
  $noches = $precio['noches'] ?? '';
  $precio_noche = $precio['precio_noche'] ?? '';
  $activar_especial = !empty($precio['activar_especial']);
  $noche_especial = $precio['noche_especial'] ?? '';
  $precio_especial = $precio['precio_especial'] ?? '';
?>
<div class="tureserva-precio-item">
  <div class="tureserva-grid">
    <!-- Temporada -->
    <div class="tureserva-box">
      <label>Temporada</label>
      <select name="tureserva_precios_variables[<?php echo $index; ?>][temporada]">
        <option value="">Seleccionar temporada</option>
        <?php foreach ($temporadas as $t): ?>
          <option value="<?php echo esc_attr($t->post_title); ?>" <?php selected($temporada, $t->post_title); ?>>
            <?php echo esc_html($t->post_title); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Adultos y Niños -->
    <div class="tureserva-box">
      <div class="tureserva-box-inline">
        <div>
          <label>Adultos</label>
          <input type="number" name="tureserva_precios_variables[<?php echo $index; ?>][adultos]" value="<?php echo esc_attr($adultos); ?>" min="0">
        </div>
        <div>
          <label>Niños</label>
          <input type="number" name="tureserva_precios_variables[<?php echo $index; ?>][ninos]" value="<?php echo esc_attr($ninos); ?>" min="0">
        </div>
      </div>
    </div>

    <!-- Noche y Precio por noche -->
    <div class="tureserva-box">
      <label>Noche</label>
      <input type="number" name="tureserva_precios_variables[<?php echo $index; ?>][noches]" value="<?php echo esc_attr($noches); ?>" min="1">
      <label style="margin-top:10px;">Precio por noche</label>
      <input type="number" name="tureserva_precios_variables[<?php echo $index; ?>][precio_noche]" value="<?php echo esc_attr($precio_noche); ?>" min="0">
    </div>

    <!-- Precio especial -->
    <div class="tureserva-especial">
      <label><input type="checkbox" class="activar-especial" name="tureserva_precios_variables[<?php echo $index; ?>][activar_especial]" <?php checked($activar_especial); ?>> Activar precio especial</label>
      <label>Noche</label>
      <input type="number" name="tureserva_precios_variables[<?php echo $index; ?>][noche_especial]" value="<?php echo esc_attr($noche_especial); ?>" min="1" <?php disabled(!$activar_especial); ?>>
      <label>Precio especial</label>
      <input type="number" name="tureserva_precios_variables[<?php echo $index; ?>][precio_especial]" value="<?php echo esc_attr($precio_especial); ?>" min="0" <?php disabled(!$activar_especial); ?>>
    </div>

    <!-- Botón eliminar -->
    <button type="button" class="tureserva-delete" title="Eliminar bloque">
      <svg viewBox="0 0 24 24"><path d="M9 3h6a1 1 0 0 1 1 1v1h5v2H3V5h5V4a1 1 0 0 1 1-1zm1 5h4v12h-4V8z"/></svg>
    </button>
  </div>
</div>
<?php
}

/**
 * ==========================================================
 * GUARDAR DATOS
 * ==========================================================
 */
function tureserva_save_tarifas_metabox($post_id) {
    if (!isset($_POST['tureserva_tarifas_nonce']) || !wp_verify_nonce($_POST['tureserva_tarifas_nonce'], 'tureserva_save_tarifas')) return;

    $precios = $_POST['tureserva_precios_variables'] ?? [];
    $sanitized = [];

    if (is_array($precios)) {
        foreach ($precios as $bloque) {
            $sanitized[] = [
                'temporada'       => sanitize_text_field($bloque['temporada'] ?? ''),
                'adultos'         => intval($bloque['adultos'] ?? 0),
                'ninos'           => intval($bloque['ninos'] ?? 0),
                'noches'          => intval($bloque['noches'] ?? 1),
                'precio_noche'    => floatval($bloque['precio_noche'] ?? 0),
                'activar_especial'=> !empty($bloque['activar_especial']),
                'noche_especial'  => intval($bloque['noche_especial'] ?? 0),
                'precio_especial' => floatval($bloque['precio_especial'] ?? 0)
            ];
        }
    }

    update_post_meta($post_id, '_tureserva_precios_variables', $sanitized);
}
add_action('save_post_tarifa', 'tureserva_save_tarifas_metabox');
