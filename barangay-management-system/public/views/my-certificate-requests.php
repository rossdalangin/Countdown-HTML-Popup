<?php
/**
 * My Certificate Requests View
 *
 * @package BarangayManagementSystem
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}
?>

<table>
    <thead>
        <tr>
            <th><?php _e( 'Certificate Type', 'barangay-management-system' ); ?></th>
            <th><?php _e( 'Purpose', 'barangay-management-system' ); ?></th>
            <th><?php _e( 'Date Requested', 'barangay-management-system' ); ?></th>
            <th><?php _e( 'Status', 'barangay-management-system' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if ( ! empty( $requests ) ) : ?>
            <?php foreach ( $requests as $request ) : ?>
                <tr>
                    <td><?php echo esc_html( $request->template_name ); ?></td>
                    <td><?php echo esc_html( $request->purpose ); ?></td>
                    <td><?php echo esc_html( $request->request_date ); ?></td>
                    <td><?php echo esc_html( $request->status ); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="4"><?php _e( 'You have not made any certificate requests.', 'barangay-management-system' ); ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
