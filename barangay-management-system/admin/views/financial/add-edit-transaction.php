<?php
/**
 * View for Add/Edit Financial Transaction form.
 * Variables available:
 * @var bool $is_editing True if editing, false if adding.
 * @var ?object $transaction The transaction object if editing.
 * @var int $transaction_id The ID of the transaction being edited, or 0.
 * @var array $categories Existing categories for datalist.
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}

$transaction_type = $transaction->transaction_type ?? 'expense'; // Default to expense
$description = $transaction->description ?? '';
$amount = $transaction->amount ?? '';
$transaction_date = isset($transaction->transaction_date) ? gmdate('Y-m-d', strtotime($transaction->transaction_date)) : current_time('Y-m-d');
$category = $transaction->category ?? '';
$reference_number = $transaction->reference_number ?? '';
$notes = $transaction->notes ?? '';

$page_title = $is_editing ? __( 'Edit Transaction', 'barangay-management-system' ) : __( 'Add New Transaction', 'barangay-management-system' );
$submit_button_text = $is_editing ? __( 'Update Transaction', 'barangay-management-system' ) : __( 'Add Transaction', 'barangay-management-system' );
?>
<div class="wrap">
    <h1><?php echo esc_html( $page_title ); ?></h1>
    <?php settings_errors(); ?>

    <form method="POST" action="">
        <?php wp_nonce_field( 'bms_save_transaction_action', 'bms_financial_transaction_nonce' ); ?>
        <input type="hidden" name="transaction_id" value="<?php echo esc_attr( $transaction_id ); ?>">

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="transaction_type"><?php esc_html_e( 'Transaction Type', 'barangay-management-system' ); ?></label></th>
                    <td>
                        <select name="transaction_type" id="transaction_type" required>
                            <option value="income" <?php selected( $transaction_type, 'income' ); ?>><?php esc_html_e( 'Income', 'barangay-management-system' ); ?></option>
                            <option value="expense" <?php selected( $transaction_type, 'expense' ); ?>><?php esc_html_e( 'Expense', 'barangay-management-system' ); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="transaction_date"><?php esc_html_e( 'Date', 'barangay-management-system' ); ?></label></th>
                    <td><input name="transaction_date" type="date" id="transaction_date" value="<?php echo esc_attr( $transaction_date ); ?>" class="regular-text" required>
                         <p class="description"><?php esc_html_e('Format: YYYY-MM-DD', 'barangay-management-system'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="description"><?php esc_html_e( 'Description', 'barangay-management-system' ); ?></label></th>
                    <td><textarea name="description" id="description" rows="3" class="large-text" required><?php echo esc_textarea( $description ); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="amount"><?php esc_html_e( 'Amount', 'barangay-management-system' ); ?></label></th>
                    <td><input name="amount" type="number" step="0.01" min="0.01" id="amount" value="<?php echo esc_attr( $amount ); ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="category"><?php esc_html_e( 'Category (Optional)', 'barangay-management-system' ); ?></label></th>
                    <td>
                        <input name="category" type="text" id="category" value="<?php echo esc_attr( $category ); ?>" class="regular-text" list="bms-transaction-categories">
                        <?php if (!empty($categories)) : ?>
                        <datalist id="bms-transaction-categories">
                            <?php foreach ($categories as $cat_option) : ?>
                                <option value="<?php echo esc_attr($cat_option); ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <?php endif; ?>
                        <p class="description"><?php esc_html_e('E.g., Utilities, Salaries, Donations, Certificate Fees', 'barangay-management-system'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="reference_number"><?php esc_html_e( 'Reference # (Optional)', 'barangay-management-system' ); ?></label></th>
                    <td><input name="reference_number" type="text" id="reference_number" value="<?php echo esc_attr( $reference_number ); ?>" class="regular-text">
                    <p class="description"><?php esc_html_e('E.g., OR Number, Invoice Number, Check Number', 'barangay-management-system'); ?></p></td>
                </tr>
                 <tr>
                    <th scope="row"><label for="notes"><?php esc_html_e( 'Notes (Optional)', 'barangay-management-system' ); ?></label></th>
                    <td><textarea name="notes" id="notes" rows="3" class="large-text"><?php echo esc_textarea( $notes ); ?></textarea></td>
                </tr>
            </tbody>
        </table>

        <?php submit_button( $submit_button_text, 'primary', 'bms_submit_transaction' ); ?>
    </form>
</div>
