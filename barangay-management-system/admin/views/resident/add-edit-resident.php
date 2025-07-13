<?php
/**
 * View for Add/Edit Resident form.
 *
 * Variables available:
 * @var bool $is_editing True if editing an existing resident, false if adding.
 * @var ?object $resident The resident object if editing, null otherwise. (Contains raw DB data)
 * @var int $resident_id The ID of the resident being edited, or 0 if adding.
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

$first_name = $resident->first_name ?? '';
$middle_name = $resident->middle_name ?? '';
$last_name = $resident->last_name ?? '';
$suffix = $resident->suffix ?? '';
$birth_date = isset($resident->birth_date) ? gmdate('Y-m-d', strtotime($resident->birth_date)) : ''; // Format for input type="date"
$gender = $resident->gender ?? '';
$civil_status = $resident->civil_status ?? '';
$address_street = $resident->address_street ?? '';
$address_barangay = $resident->address_barangay ?? get_option('bms_setting_default_barangay', 'My Barangay'); // Example default
$address_city = $resident->address_city ?? get_option('bms_setting_default_city', 'My City');
$address_province = $resident->address_province ?? get_option('bms_setting_default_province', 'My Province');
$contact_phone = $resident->contact_phone ?? '';
$contact_email = $resident->contact_email ?? '';
$family_head_id_val = $resident->family_head_id ?? null;
$occupation = $resident->occupation ?? '';
$nationality = $resident->nationality ?? 'Filipino';

$page_title = $is_editing ? __( 'Edit Resident', 'barangay-management-system' ) : __( 'Add New Resident', 'barangay-management-system' );
$submit_button_text = $is_editing ? __( 'Update Resident', 'barangay-management-system' ) : __( 'Add Resident', 'barangay-management-system' );


?>
<div class="wrap">
    <h1><?php echo esc_html( $page_title ); ?></h1>

    <?php settings_errors(); // For displaying messages from the form handler, like validation errors. ?>

    <form method="POST" action="">
        <?php wp_nonce_field( 'bms_save_resident_action', 'bms_resident_nonce_field' ); ?>
        <input type="hidden" name="resident_id" value="<?php echo esc_attr( $resident_id ); ?>">

        <table class="form-table">
            <tbody>
                <!-- Personal Information -->
                <tr>
                    <th scope="row"><label for="first_name"><?php esc_html_e( 'First Name', 'barangay-management-system' ); ?></label></th>
                    <td><input name="first_name" type="text" id="first_name" value="<?php echo esc_attr( $first_name ); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="middle_name"><?php esc_html_e( 'Middle Name', 'barangay-management-system' ); ?></label></th>
                    <td><input name="middle_name" type="text" id="middle_name" value="<?php echo esc_attr( $middle_name ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="last_name"><?php esc_html_e( 'Last Name', 'barangay-management-system' ); ?></label></th>
                    <td><input name="last_name" type="text" id="last_name" value="<?php echo esc_attr( $last_name ); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="suffix"><?php esc_html_e( 'Suffix (e.g., Jr., Sr.)', 'barangay-management-system' ); ?></label></th>
                    <td><input name="suffix" type="text" id="suffix" value="<?php echo esc_attr( $suffix ); ?>" class="short-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="birth_date"><?php esc_html_e( 'Birth Date', 'barangay-management-system' ); ?></label></th>
                    <td><input name="birth_date" type="date" id="birth_date" value="<?php echo esc_attr( $birth_date ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('Format: YYYY-MM-DD', 'barangay-management-system'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gender"><?php esc_html_e( 'Gender', 'barangay-management-system' ); ?></label></th>
                    <td>
                        <select name="gender" id="gender">
                            <option value="" <?php selected( $gender, '' ); ?>><?php esc_html_e( '-- Select Gender --', 'barangay-management-system' ); ?></option>
                            <option value="Male" <?php selected( $gender, 'Male' ); ?>><?php esc_html_e( 'Male', 'barangay-management-system' ); ?></option>
                            <option value="Female" <?php selected( $gender, 'Female' ); ?>><?php esc_html_e( 'Female', 'barangay-management-system' ); ?></option>
                            <option value="Other" <?php selected( $gender, 'Other' ); ?>><?php esc_html_e( 'Other', 'barangay-management-system' ); ?></option>
                        </select>
                    </td>
                </tr>
                 <tr>
                    <th scope="row"><label for="civil_status"><?php esc_html_e( 'Civil Status', 'barangay-management-system' ); ?></label></th>
                    <td>
                        <select name="civil_status" id="civil_status">
                            <option value="" <?php selected( $civil_status, '' ); ?>><?php esc_html_e( '-- Select Status --', 'barangay-management-system' ); ?></option>
                            <option value="Single" <?php selected( $civil_status, 'Single' ); ?>><?php esc_html_e( 'Single', 'barangay-management-system' ); ?></option>
                            <option value="Married" <?php selected( $civil_status, 'Married' ); ?>><?php esc_html_e( 'Married', 'barangay-management-system' ); ?></option>
                            <option value="Widowed" <?php selected( $civil_status, 'Widowed' ); ?>><?php esc_html_e( 'Widowed', 'barangay-management-system' ); ?></option>
                            <option value="Separated" <?php selected( $civil_status, 'Separated' ); ?>><?php esc_html_e( 'Separated', 'barangay-management-system' ); ?></option>
                            <option value="Divorced" <?php selected( $civil_status, 'Divorced' ); ?>><?php esc_html_e( 'Divorced', 'barangay-management-system' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="occupation"><?php esc_html_e( 'Occupation', 'barangay-management-system' ); ?></label></th>
                    <td><input name="occupation" type="text" id="occupation" value="<?php echo esc_attr( $occupation ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="nationality"><?php esc_html_e( 'Nationality', 'barangay-management-system' ); ?></label></th>
                    <td><input name="nationality" type="text" id="nationality" value="<?php echo esc_attr( $nationality ); ?>" class="regular-text"></td>
                </tr>


                <!-- Address Information -->
                <tr class="form-field">
                    <th colspan="2"><h3><?php esc_html_e('Address Information', 'barangay-management-system'); ?></h3></th>
                </tr>
                <tr>
                    <th scope="row"><label for="address_street"><?php esc_html_e( 'House No./Street/Purok', 'barangay-management-system' ); ?></label></th>
                    <td><input name="address_street" type="text" id="address_street" value="<?php echo esc_attr( $address_street ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="address_barangay"><?php esc_html_e( 'Barangay', 'barangay-management-system' ); ?></label></th>
                    <td><input name="address_barangay" type="text" id="address_barangay" value="<?php echo esc_attr( $address_barangay ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="address_city"><?php esc_html_e( 'City/Municipality', 'barangay-management-system' ); ?></label></th>
                    <td><input name="address_city" type="text" id="address_city" value="<?php echo esc_attr( $address_city ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="address_province"><?php esc_html_e( 'Province', 'barangay-management-system' ); ?></label></th>
                    <td><input name="address_province" type="text" id="address_province" value="<?php echo esc_attr( $address_province ); ?>" class="regular-text"></td>
                </tr>

                <!-- Contact Information -->
                 <tr class="form-field">
                    <th colspan="2"><h3><?php esc_html_e('Contact Information', 'barangay-management-system'); ?></h3></th>
                </tr>
                <tr>
                    <th scope="row"><label for="contact_phone"><?php esc_html_e( 'Phone Number', 'barangay-management-system' ); ?></label></th>
                    <td><input name="contact_phone" type="tel" id="contact_phone" value="<?php echo esc_attr( $contact_phone ); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="contact_email"><?php esc_html_e( 'Email Address', 'barangay-management-system' ); ?></label></th>
                    <td><input name="contact_email" type="email" id="contact_email" value="<?php echo esc_attr( $contact_email ); ?>" class="regular-text"></td>
                </tr>

                <!-- Family Relation -->
                <tr class="form-field">
                    <th colspan="2"><h3><?php esc_html_e('Family Relation', 'barangay-management-system'); ?></h3></th>
                </tr>
                <tr>
                    <th scope="row"><label for="family_head_id"><?php esc_html_e( 'Family Head', 'barangay-management-system' ); ?></label></th>
                    <td>
                        <select name="family_head_id" id="family_head_id" style="width: 100%;">
                            <?php if ( $family_head_id_val ) : ?>
                                <?php $family_head = bms_get_resident( $family_head_id_val ); ?>
                                <option value="<?php echo esc_attr( $family_head->id ); ?>" selected="selected"><?php echo esc_html( $family_head->first_name . ' ' . $family_head->last_name ); ?></option>
                            <?php endif; ?>
                        </select>
                        <p class="description"><?php esc_html_e('If this resident is part of a family, select the head of their family. If they are the head, leave as N/A.', 'barangay-management-system'); ?></p>
                    </td>
                </tr>

            </tbody>
        </table>

        <?php submit_button( $submit_button_text, 'primary', 'bms_submit_resident' ); ?>
    </form>
</div>
<script>
jQuery(document).ready(function($) {
    $('#family_head_id').select2({
        ajax: {
            url: '<?php echo esc_url( get_rest_url( null, 'bms/v1/residents' ) ); ?>',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term,
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true
        },
        placeholder: '<?php esc_attr_e( 'Search for a resident...', 'barangay-management-system' ); ?>',
        minimumInputLength: 1,
    });
});
</script>
