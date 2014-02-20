<?

define( 'EVW_SETTINGS_SLUG', 'einsatzvw-settings' );

/**
 * Fügt die Einstellungsseite zum Menü hinzu
 */
function einsatzverwaltung_settings_menu()
{
    add_options_page( 'Einstellungen', 'Einsatzverwaltung', 'manage_options', EVW_SETTINGS_SLUG, 'einsatzverwaltung_settings_page');
}
add_action('admin_menu', 'einsatzverwaltung_settings_menu');


/**
 * Macht Einstellungen im System bekannt und regelt die Zugehörigkeit zu Abschnitten auf Einstellungsseiten
 */
function einsatzverwaltung_register_settings()
{
    // Sections
    add_settings_section( 'einsatzvw_settings_view',
        'Darstellung',
        function() {
            echo '<p>Mit diesen Einstellungen kann das Aussehen der Einsatzberichte beeinflusst werden.</p>';
        },
        EVW_SETTINGS_SLUG
    );
    
    // Fields
    add_settings_field( 'einsatzvw_einsatz_hideemptydetails',
        'Einsatzdetails',
        'einsatzverwaltung_echo_settings_checkbox',
        EVW_SETTINGS_SLUG,
        'einsatzvw_settings_view',
        array('einsatzvw_einsatz_hideemptydetails', 'Nicht ausgef&uuml;llte Details ausblenden (z.B. wenn <em>Weitere Kr&auml;fte</em> leer ist)')
    );
    
    // Registration
    register_setting( 'einsatzvw_settings', 'einsatzvw_einsatz_hideemptydetails', 'einsatzverwaltung_sanitize_checkbox' );
}
add_action( 'admin_init', 'einsatzverwaltung_register_settings' );


/**
 *
 */
function einsatzverwaltung_echo_settings_checkbox($args)
{
    $id = $args[0];
    $text = $args[1];
    printf('<input type="checkbox" value="1" id="%1$s" name="%1$s" %2$s/><label for="%1$s">%3$s</label>', $id, einsatzverwaltung_checked(get_option($id)), $text);
}


/**
 * Generiert den Inhalt der Einstellungsseite
 */
function einsatzverwaltung_settings_page()
{
    if ( ! current_user_can( 'manage_options' ) )
    wp_die( __( 'You do not have sufficient permissions to manage options for this site.' ) );
    
    echo '<div class="wrap">';
    echo '<h2>Einstellungen &rsaquo; Einsatzverwaltung</h2>';
    
    echo '<form method="post" action="options.php">';
    echo settings_fields( 'einsatzvw_settings' );
    echo do_settings_sections( EVW_SETTINGS_SLUG );
    submit_button();
    echo '</form>';
}

?>