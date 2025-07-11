<?php
/**
 * View for Residents List.
 *
 * Variables available:
 * @var array $residents Array of resident objects.
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Residents', 'barangay-management-system' ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-resident-add' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add New Resident', 'barangay-management-system' ); ?>
    </a>

    <?php
    // Display any notices (e.g., after adding a resident)
    if (isset($_GET['resident_added']) && $_GET['resident_added'] == 'true' && isset($_GET['id'])) {
        echo '<div id="message" class="updated notice is-dismissible"><p>' . sprintf(esc_html__('Resident added successfully. %sView resident%s or %sedit this resident%s.', 'barangay-management-system'), '<a href="#">', '</a>', '<a href="' . esc_url(admin_url('admin.php?page=bms-resident-add&resident_id=' . absint($_GET['id']))) . '">', '</a>') . '</p></div>';
    }
    if (isset($_GET['deleted']) && $_GET['deleted'] == 'true') {
         // This notice is now handled by add_action( 'admin_notices', ... ) in resident-admin-pages.php
    }
    settings_errors(); // Display other notices
    ?>

    <form method="get">
        <input type="hidden" name="page" value="bms-residents" />
        <?php
        // For WP_List_Table later, we'd instantiate and call $list_table->search_box(), $list_table->display()
        // For now, a simple search (this search isn't hooked up yet in the handler, just UI)
        // $search_term = isset($_REQUEST['s']) ? sanitize_text_field($_REQUEST['s']) : '';
        // echo '<p class="search-box">';
        // echo '<label class="screen-reader-text" for="resident-search-input">' . esc_html__('Search Residents:', 'barangay-management-system') . '</label>';
        // echo '<input type="search" id="resident-search-input" name="s" value="' . esc_attr($search_term) . '">';
        // submit_button(__('Search Residents', 'barangay-management-system'), 'button', false, false, array('id' => 'search-submit'));
        // echo '</p>';
        ?>
    </form>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-name"><?php esc_html_e( 'Name', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-address"><?php esc_html_e( 'Address', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-contact"><?php esc_html_e( 'Contact', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-birthdate"><?php esc_html_e( 'Birth Date', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-registered"><?php esc_html_e( 'Date Registered', 'barangay-management-system' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $residents ) ) : ?>
                <?php foreach ( $residents as $resident ) : ?>
                    <tr>
                        <td class="column-name">
                            <strong>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-resident-add&resident_id=' . $resident->id ) ); ?>">
                                    <?php echo esc_html( trim(sprintf( '%s, %s %s', $resident->last_name, $resident->first_name, $resident->middle_name )) ); ?>
                                </a>
                            </strong>
                            <div class="row-actions">
                                <span class="edit">
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-resident-add&resident_id=' . $resident->id ) ); ?>">
                                        <?php esc_html_e( 'Edit', 'barangay-management-system' ); ?>
                                    </a> |
                                </span>
                                <span class="delete">
                                    <?php
                                    $delete_url = add_query_arg(
                                        array(
                                            'action'      => 'delete',
                                            'resident_id' => $resident->id,
                                            '_wpnonce'    => wp_create_nonce( 'bms_delete_resident_' . $resident->id ),
                                        ),
                                        admin_url( 'admin.php?page=bms-residents' )
                                    );
                                    ?>
                                    <a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this resident?', 'barangay-management-system' ); ?>');" style="color:red;">
                                        <?php esc_html_e( 'Delete', 'barangay-management-system' ); ?>
                                    </a>
                                </span>
                            </div>
                        </td>
                        <td class="column-address">
                            <?php
                            $address_parts = array_filter([
                                $resident->address_street,
                                $resident->address_barangay,
                                $resident->address_city,
                                $resident->address_province
                            ]);
                            echo esc_html( implode( ', ', $address_parts ) );
                            ?>
                        </td>
                        <td class="column-contact">
                            <?php if ( $resident->contact_phone ) echo esc_html( $resident->contact_phone ) . '<br>'; ?>
                            <?php if ( $resident->contact_email ) echo esc_html( $resident->contact_email ); ?>
                        </td>
                        <td class="column-birthdate"><?php echo esc_html( $resident->birth_date ? gmdate('M j, Y', strtotime($resident->birth_date)) : 'N/A' ); ?></td>
                        <td class="column-registered"><?php echo esc_html( $resident->date_registered ? gmdate('M j, Y H:i', strtotime($resident->date_registered)) : 'N/A' ); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5"><?php esc_html_e( 'No residents found.', 'barangay-management-system' ); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th scope="col" class="manage-column column-name"><?php esc_html_e( 'Name', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-address"><?php esc_html_e( 'Address', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-contact"><?php esc_html_e( 'Contact', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-birthdate"><?php esc_html_e( 'Birth Date', 'barangay-management-system' ); ?></th>
                <th scope="col" class="manage-column column-registered"><?php esc_html_e( 'Date Registered', 'barangay-management-system' ); ?></th>
            </tr>
        </tfoot>
    </table>
    <?php
    // Pagination would go here if we were using WP_List_Table or manual pagination
    ?>
</div>
