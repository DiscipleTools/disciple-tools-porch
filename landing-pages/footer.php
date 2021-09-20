<script>
    let jsObject = [<?php echo json_encode([
        'map_key' => DT_Mapbox_API::get_key(),
        'root' => esc_url_raw( rest_url() ),
        'nonce' => wp_create_nonce( 'wp_rest' ),
        'parts' => $this->parts,
        'translations' => [
            'add' => __( 'Add Magic', 'disciple_tools' ),
        ],
    ]) ?>][0]
</script>
