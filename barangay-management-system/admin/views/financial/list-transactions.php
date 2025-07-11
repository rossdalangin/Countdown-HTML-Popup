<?php
/**
 * View for Financial Transactions List & Overview.
 * Variables available:
 * @var array $transactions Array of transaction objects.
 * @var object $summary Object with total_income, total_expenses, net_amount.
 * @var array $categories Array of unique category strings.
 * @var array $filter_args Current filter arguments.
 */
if ( ! defined( 'WPINC' ) ) {
    die;
}

$current_type_filter = $filter_args['transaction_type'] ?? '';
$current_category_filter = $filter_args['category'] ?? '';
$current_date_from = $filter_args['date_from'] ?? '';
$current_date_to = $filter_args['date_to'] ?? '';
$current_search = $filter_args['search'] ?? '';

?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Financials', 'barangay-management-system' ); ?></h1>
    <?php if ( current_user_can( BMS_MANAGE_FINANCIALS_CAP ) ) : ?>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-financial-add' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add New Transaction', 'barangay-management-system' ); ?>
    </a>
    <?php endif; ?>

    <?php
    if (isset($_GET['transaction_added']) && $_GET['transaction_added'] == 'true' && isset($_GET['id'])) {
        echo '<div id="message" class="updated notice is-dismissible"><p>' . sprintf(esc_html__('Transaction added successfully. %sEdit this transaction%s.', 'barangay-management-system'), '<a href="' . esc_url(admin_url('admin.php?page=bms-financial-add&transaction_id=' . absint($_GET['id']))) . '">', '</a>') . '</p></div>';
    }
    settings_errors();
    ?>

    <div id="financial-summary" style="margin-top: 20px; margin-bottom: 20px; padding: 15px; background-color: #fff; border: 1px solid #ccd0d4;">
        <h2><?php esc_html_e('Financial Summary (Filtered)', 'barangay-management-system'); ?></h2>
        <p><strong><?php esc_html_e('Total Income:', 'barangay-management-system'); ?></strong> <?php echo esc_html(number_format($summary->total_income, 2)); ?></p>
        <p><strong><?php esc_html_e('Total Expenses:', 'barangay-management-system'); ?></strong> <?php echo esc_html(number_format($summary->total_expenses, 2)); ?></p>
        <p><strong><?php esc_html_e('Net Amount:', 'barangay-management-system'); ?></strong> <span style="font-weight:bold; color: <?php echo ($summary->net_amount >= 0 ? 'green' : 'red'); ?>;"><?php echo esc_html(number_format($summary->net_amount, 2)); ?></span></p>
    </div>

    <form method="get" id="bms-financials-filter">
        <input type="hidden" name="page" value="bms-financials" />
        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="type">
                    <option value=""><?php esc_html_e('All Types', 'barangay-management-system'); ?></option>
                    <option value="income" <?php selected($current_type_filter, 'income'); ?>><?php esc_html_e('Income', 'barangay-management-system'); ?></option>
                    <option value="expense" <?php selected($current_type_filter, 'expense'); ?>><?php esc_html_e('Expense', 'barangay-management-system'); ?></option>
                </select>

                <select name="category">
                    <option value=""><?php esc_html_e('All Categories', 'barangay-management-system'); ?></option>
                    <?php foreach ($categories as $category) : ?>
                        <option value="<?php echo esc_attr($category); ?>" <?php selected($current_category_filter, $category); ?>><?php echo esc_html($category); ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="date_from" class="screen-reader-text"><?php esc_html_e('Date From:', 'barangay-management-system'); ?></label>
                <input type="date" name="date_from" id="date_from" value="<?php echo esc_attr($current_date_from); ?>" placeholder="<?php esc_attr_e('Date From', 'barangay-management-system'); ?>">

                <label for="date_to" class="screen-reader-text"><?php esc_html_e('Date To:', 'barangay-management-system'); ?></label>
                <input type="date" name="date_to" id="date_to" value="<?php echo esc_attr($current_date_to); ?>" placeholder="<?php esc_attr_e('Date To', 'barangay-management-system'); ?>">

                <input type="submit" class="button" value="<?php esc_attr_e('Filter', 'barangay-management-system'); ?>">
            </div>
             <div class="alignleft actions">
                <label for="bms-financial-search-input" class="screen-reader-text"><?php esc_html_e('Search Transactions', 'barangay-management-system'); ?></label>
                <input type="search" id="bms-financial-search-input" name="s" value="<?php echo esc_attr($current_search); ?>" placeholder="<?php esc_attr_e('Search desc/cat/ref...', 'barangay-management-system'); ?>">
                <input type="submit" class="button" value="<?php esc_attr_e('Search', 'barangay-management-system'); ?>">
            </div>
            <br class="clear">
        </div>


        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-date"><?php esc_html_e( 'Date', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-type"><?php esc_html_e( 'Type', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-description"><?php esc_html_e( 'Description', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-category"><?php esc_html_e( 'Category', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-amount"><?php esc_html_e( 'Amount', 'barangay-management-system' ); ?></th>
                    <th scope="col" class="manage-column column-ref"><?php esc_html_e( 'Reference #', 'barangay-management-system' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( ! empty( $transactions ) ) : ?>
                    <?php foreach ( $transactions as $transaction ) : ?>
                        <tr>
                            <td class="column-date"><?php echo esc_html( gmdate('M j, Y', strtotime($transaction->transaction_date)) ); ?></td>
                            <td class="column-type">
                                <span style="color: <?php echo ($transaction->transaction_type === 'income' ? 'green' : 'red'); ?>;">
                                    <?php echo esc_html( ucfirst($transaction->transaction_type) ); ?>
                                </span>
                            </td>
                            <td class="column-description">
                                <?php if ( current_user_can( BMS_MANAGE_FINANCIALS_CAP ) ) : ?>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-financial-add&transaction_id=' . $transaction->id ) ); ?>">
                                    <?php echo esc_html( $transaction->description ); ?>
                                </a>
                                <?php else: ?>
                                    <?php echo esc_html( $transaction->description ); ?>
                                <?php endif; ?>
                                <?php if ( !empty($transaction->notes) ) : ?>
                                    <p class="description"><em><?php echo esc_html($transaction->notes); ?></em></p>
                                <?php endif; ?>
                                <?php if ( current_user_can( BMS_MANAGE_FINANCIALS_CAP ) ) : ?>
                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=bms-financial-add&transaction_id=' . $transaction->id ) ); ?>">
                                            <?php esc_html_e( 'Edit', 'barangay-management-system' ); ?></a> |
                                    </span>
                                    <span class="delete">
                                        <?php
                                        $delete_url = add_query_arg(
                                            array(
                                                'action'          => 'delete_transaction',
                                                'transaction_id'  => $transaction->id,
                                                '_wpnonce'        => wp_create_nonce( 'bms_delete_transaction_' . $transaction->id ),
                                            ),
                                            admin_url( 'admin.php?page=bms-financials' ) // Adjust if filters need to be preserved
                                        );
                                        ?>
                                        <a href="<?php echo esc_url( $delete_url ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete this transaction?', 'barangay-management-system' ); ?>');" style="color:red;">
                                            <?php esc_html_e( 'Delete', 'barangay-management-system' ); ?></a>
                                    </span>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td class="column-category"><?php echo esc_html( $transaction->category ); ?></td>
                            <td class="column-amount" style="text-align:right; color: <?php echo ($transaction->transaction_type === 'income' ? 'green' : 'red'); ?>;">
                                <?php echo esc_html( number_format($transaction->amount, 2) ); ?>
                            </td>
                            <td class="column-ref"><?php echo esc_html( $transaction->reference_number ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6"><?php esc_html_e( 'No transactions found for the current filters.', 'barangay-management-system' ); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </form>
    <?php // Add pagination here later if using WP_List_Table or manual pagination ?>
</div>
