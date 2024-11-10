# Easy Digital Downloads Polyfills Library

The EDD Polyfills Library aims to bridge the functionality gap in Easy Digital Downloads (EDD) 3.x by providing
essential polyfills for various features that are missing or incomplete in the core EDD plugin. Designed to seamlessly
integrate with EDD's existing architecture, this library introduces a collection of globally available functions that
adhere closely to EDD's naming conventions and variable declarations, ensuring a familiar and intuitive experience for
developers.

## Installation

Ensure you have the package installed in your project. If not, you can typically include it using Composer:

```bash
composer require arraypress/edd-polyfills
```

## Usage

After installation, the polyfill functions are automatically available for use in the global namespace. This design
choice, while unconventional, ensures that the polyfills can be used with minimal setup and directly replace or
supplement the corresponding EDD core functionalities as needed.

Here's an example of using a polyfill function:

```php
if ( ! function_exists( 'edd_get_adjustment_field' ) ) {
    // Use the polyfill function provided by the library
    $value = edd_get_adjustment_field( $adjustment_id, 'field_name' );
}
```

This example checks if the `edd_get_adjustment_field` function exists before using it, which is a safe way to use
polyfills in case the original function is introduced in a future version of EDD.

## Adjustment Polyfills

### `edd_get_adjustment_field`

Retrieves a specific field from an adjustment object. This function is useful when you need to access a particular piece
of data from an adjustment without fetching the entire object.

**Parameters**:

- `$adjustment_id`: The ID of the adjustment. Default is `0`.
- `$field`: The specific field to retrieve from the adjustment object. Default is an empty string.

**Returns**: The value of the specified field if it exists, or null if the adjustment does not exist or the field is not
set.

### `edd_adjustment_exists`

Checks if an adjustment exists in the database by its ID. This function is useful for validating the existence of an
adjustment before performing operations on it.

**Parameters**:

- `$adjustment_id`: The ID of the adjustment to check.

**Returns**: `true` if the adjustment exists, `false` otherwise.

### `edd_is_adjustment_type`

Determines if a given adjustment is of a specified type. This function is helpful for filtering adjustments by type,
such as distinguishing between discounts, fees, or any custom adjustment types introduced by extensions or custom code.

**Parameters**:

- `$adjustment_id`: The ID of the adjustment to check.
- `$type`: The expected type of the adjustment. Default is an empty string.

**Returns**: `true` if the adjustment is of the specified type, `false` otherwise.

## Customer Address Polyfills

### `edd_get_customer_address_field`

Retrieves a specific field from a customer address object. Utilize this function to access individual pieces of data
from a customer's address without needing the complete address object.

**Parameters**:

- `$customer_address_id`: The ID of the customer address. Defaults to `0`.
- `$field`: The specific field to retrieve from the customer address object. Defaults to an empty string.

**Returns**: The value of the requested field if it exists, or null if the customer address does not exist or the field
is unset.

### `edd_customer_address_exists`

Determines if a specified customer address exists in the database. This function is crucial for verifying the existence
of a customer address prior to performing actions or updates on it.

**Parameters**:

- `$customer_address_id`: The ID of the customer address to check.

**Returns**: `true` if the customer address exists, `false` otherwise.

## Customer Email Address Polyfills

### `edd_get_customer_email_address_field`

Retrieves a specific field from a customer email address object. This function is essential for accessing detailed
information about a customer's email address directly without requiring the full object retrieval.

**Parameters**:

- `$customer_email_address_id`: The ID of the customer email address. Default is `0`.
- `$field`: The specific field to retrieve from the customer email address object. Default is an empty string.

**Returns**: The value of the specified field if it exists, or null if the customer email address does not exist or the
field is not set.

### `edd_customer_email_address_exists`

Checks if a customer email address exists in the database. This function is crucial for validating the presence of a
customer's email address before performing operations on it.

**Parameters**:

- `$customer_email_address_id`: The ID of the customer email address to check.

**Returns**: `true` if the customer email address exists, `false` otherwise.

## Customer Polyfills

### `edd_customer_exists`

Checks if a customer exists in the database by their ID. This function is crucial for verifying the existence of a
customer before performing further operations or queries related to them.

**Parameters**:

- `$customer_id`: The ID of the customer to check.

**Returns**: `true` if the customer exists in the database, `false` otherwise. This function ensures that operations
only proceed with valid customer IDs, thereby avoiding errors and inconsistencies.

### `edd_get_customer_emails`

Retrieves all email addresses associated with a given customer. This function is useful for accessing a customer's
multiple contact emails, which can be essential for communication, marketing, and support purposes.

**Parameters**:

- `$customer_id`: The ID of the customer whose email addresses are to be retrieved.

**Returns**: An array of email addresses associated with the customer. If the customer does not exist or no emails are
associated, an empty array is returned.

### `edd_get_customer_id_by_user_id`

Get the customer ID associated with a user ID. This function facilitates linking user accounts to their corresponding
customer records in the database, enabling personalized interactions and streamlined customer service operations.

**Parameters**:

- `$user_id`: The user ID. If not specified or if 0, the function attempts to use the current logged-in user's ID.
  Default is 0.
- `$use_cache`: Whether to use cache for the lookup. Default is true.

**Returns**: The customer ID or `false` if the customer ID is not found. This return value is crucial for functions that
require a customer ID to proceed with further processing, such as retrieving customer-specific order histories or
personalizing the shopping experience.

## Download Polyfills

### `edd_get_download_file_field`

Retrieves the specified field value for a download file in Easy Digital Downloads. This function is instrumental in
accessing specific metadata about a download file, such as its name or file URL.

**Parameters**:

- `$download_id`: The unique identifier for the downloadable product.
- `$file_id`: The specific file ID within the download to query.
- `$field`: The field whose value is being requested (e.g., 'name', 'file').

**Returns**: The value of the requested field if it exists, null otherwise.

### `edd_get_download_file`

Retrieves metadata for a specific file associated with a download product. This function fetches detailed information
about a given file, aiding in data retrieval and display operations.

**Parameters**:

- `$download_id`: The unique identifier for the downloadable product.
- `$file_id`: The specific file ID within the download to query. If null, metadata for all files is returned.

**Returns**: The file's metadata if found, an empty string if the file key does not exist, and false if no download ID
was passed.

### `edd_get_download_file_name`

Retrieves the name of a file associated with a download in Easy Digital Downloads. This function is useful for obtaining
the display name of a download file for user interfaces.

**Parameters**:

- `$download_id`: The ID of the downloadable product.
- `$file_id`: The specific file ID within the download to query.

**Returns**: The name of the file if available, or false if the download ID is invalid.

## API Request Log Polyfills

### `edd_get_api_request_log_field`

Retrieves a specific field from an API request log object. This function facilitates access to detailed information
about an API request log entry, such as the request URL, response code, or any custom field data stored in log entries.

**Parameters**:

- `$api_request_log_id`: The ID of the API request log entry. Default is `0`.
- `$field`: The specific field to retrieve from the API request log object. Default is an empty string.

**Returns**: The value of the specified field if it exists, or null if the API request log entry does not exist or the
field is not set.

### `edd_api_request_log_exists`

Checks if an API request log entry exists in the database by its ID. This function is essential for verifying the
presence of a log entry before performing operations on it.

**Parameters**:

- `$api_request_log_id`: The ID of the API request log entry to check.

**Returns**: `true` if the API request log entry exists in the database, `false` otherwise.

## File Download Log Polyfills

### `edd_get_file_download_log_field`

Retrieves a specific field from a file download log object. This function allows for detailed access to individual
aspects of a file download log, facilitating analysis and reporting on file downloads.

**Parameters**:

- `$file_download_log_id`: The ID of the file download log entry. Default is `0`.
- `$field`: The specific field to retrieve from the file download log object. Default is an empty string.

**Returns**: The value of the specified field if it exists, or null if the file download log entry does not exist or the
field is not set.

### `edd_file_download_log_exists`

Checks if a file download log entry exists in the database by its ID. This function is crucial for verifying the
presence of a log entry before conducting further analysis or operations related to file downloads.

**Parameters**:

- `$file_download_log_id`: The ID of the file download log entry to check.

**Returns**: `true` if the file download log entry exists in the database, `false` otherwise.

## Log Polyfills

### `edd_get_log_field`

Retrieves a specific field from a log object. This function provides direct access to particular details of a log entry,
facilitating custom analysis and reporting on various log types within EDD.

**Parameters**:

- `$log_id`: The ID of the log entry. Default is `0`.
- `$field`: The specific field to retrieve from the log object. Default is an empty string.

**Returns**: The value of the specified field if it exists, or null if the log entry does not exist or the field is not
set.

### `edd_log_exists`

Checks if a log entry exists in the database by its ID. This function is essential for verifying the existence of a log
entry before performing further operations or queries related to it.

**Parameters**:

- `$log_id`: The ID of the log entry to check.

**Returns**: `true` if the log entry exists in the database, `false` otherwise.

## Notes Polyfills

### `edd_get_note_field`

Retrieves a specific field from a note object. This function is designed to provide direct access to particular details
of a note, aiding in custom data handling and presentation within EDD.

**Parameters**:

- `$note_id`: The ID of the note entry. Default is `0`.
- `$field`: The specific field to retrieve from the note object. Default is an empty string.

**Returns**: The value of the specified field if it exists, or null if the note entry does not exist or the field is not
set.

### `edd_note_exists`

Checks if a note entry exists in the database by its ID. This function is crucial for confirming the presence of a note
before proceeding with operations or analysis related to it.

**Parameters**:

- `$note_id`: The ID of the note entry to check.

**Returns**: `true` if the note entry exists in the database, `false` otherwise.

## Order Address Polyfills

### `edd_get_order_address_field`

Retrieves a specific field from an order address object. This function facilitates accessing detailed information about
an order's address directly, making it easier to handle shipping or billing address data within EDD.

**Parameters**:

- `$order_address_id`: The ID of the order address entry. Default is `0`.
- `$field`: The specific field to retrieve from the order address object. Default is an empty string.

**Returns**: The value of the specified field if it exists, or null if the order address entry does not exist or the
field is not set.

### `edd_order_address_exists`

Checks if an order address entry exists in the database by its ID. This function is crucial for verifying the existence
of an order's address before performing further operations or queries related to it.

**Parameters**:

- `$order_address_id`: The ID of the order address entry to check.

**Returns**: `true` if the order address entry exists in the database, `false` otherwise.

## Order Adjustment Polyfills

### `edd_get_order_adjustment_field`

Retrieves a specific field from an order adjustment object. This function allows for detailed access to individual
aspects of an order adjustment, aiding in custom handling and presentation within EDD.

**Parameters**:

- `$order_adjustment_id`: The ID of the order adjustment entry. Default is `0`.
- `$field`: The specific field to retrieve from the order adjustment object. Default is an empty string.

**Returns**: The value of the specified field if it exists, or null if the order adjustment entry does not exist or the
field is not set.

### `edd_order_adjustment_exists`

Checks if an order adjustment entry exists in the database by its ID. This function is crucial for verifying the
presence of an adjustment before performing further operations or analyses related to it.

**Parameters**:

- `$order_adjustment_id`: The ID of the order adjustment entry to check.

**Returns**: `true` if the order adjustment entry exists in the database, `false` otherwise.

## Order Item Polyfills

### `edd_get_order_item_field`

Retrieves a specific field from an order item object. This function is indispensable for directly accessing particular
details of an order item, such as product ID, quantity, or any custom field.

**Parameters**:

- `$order_item_id`: The ID of the order item entry. Default is `0`.
- `$field`: The specific field to retrieve from the order item object. Default is an empty string.

**Returns**: The value of the specified field if it exists, or null if the order item entry does not exist or the field
is not set.

### `edd_order_item_exists`

Checks if an order item entry exists in the database by its ID. This function ensures that specific order items can be
verified before conducting further operations or analyses related to them.

**Parameters**:

- `$order_item_id`: The ID of the order item entry to check.

**Returns**: `true` if the order item entry exists in the database, `false` otherwise.

### `edd_get_order_item_by_cart_index`

Retrieves an order item from an EDD order by its cart index, facilitating access to specific items within an order based
on their position in the shopping cart.

**Parameters**:

- `$order_id`: The unique identifier of the EDD order.
- `$cart_index`: The cart index of the order item to retrieve.

**Returns**: The order item object if found, or null if not found. This function is particularly useful when the order
structure and item sequence are critical for processing or reporting.

## Order Transaction Polyfills

### `edd_get_order_transaction_field`

Retrieves a specific field from an order transaction object. This function allows for direct access to particular
transaction details, aiding in financial analyses, reporting, and integrations with external accounting or CRM systems.

**Parameters**:

- `$order_transaction_id`: The ID of the order transaction entry. Default is `0`.
- `$field`: The specific field to retrieve from the order transaction object. Default is an empty string.

**Returns**: The value of the specified field if it exists, or null if the order transaction entry does not exist or the
field is not set.

### `edd_order_transaction_exists`

Checks if an order transaction entry exists in the database by its ID. This function is essential for verifying the
presence of a transaction before conducting financial operations, audits, or reconciliation processes related to it.

**Parameters**:

- `$order_transaction_id`: The ID of the order transaction entry to check.

**Returns**: `true` if the order transaction entry exists in the database, `false` otherwise.

## Order Polyfills

### `edd_get_order_field`

Retrieves a specific field from an order object. This function facilitates accessing individual order details directly,
such as status, total amount, customer ID, or any custom field, aiding in order management and reporting.

**Parameters**:

- `$order_id`: The ID of the order entry. Default is `0`.
- `$field`: The specific field to retrieve from the order object. Default is an empty string.

**Returns**: The value of the specified field if it exists, or null if the order entry does not exist or the field is
not set.

### `edd_order_exists`

Checks if an order entry exists in the database by its ID. This function is crucial for verifying the presence of an
order before proceeding with operations or analyses related to it, ensuring data integrity and operational accuracy.

**Parameters**:

- `$order_id`: The ID of the order entry to check.

**Returns**: `true` if the order entry exists in the database, `false` otherwise.

### `edd_order_exists_by_key`

Check if an order with the given payment key exists. This function is useful for validating if a specific order is
already present in the system based on its unique payment key, aiding in preventing duplicate entries and ensuring
accurate order tracking.

**Parameters**:

- `$payment_key`: The payment key of the order to check. Default is an empty string.
- `$use_cache`: Whether to use cache for the lookup. Default is true.

**Returns**: `true` if the order exists, `false` otherwise. This helps in quickly determining the existence of an order
without needing to perform a full database search, especially beneficial for high-traffic sites where performance
optimization is critical.

## Contributions

Contributions to this library are highly appreciated. Raise issues on GitHub or submit pull requests for bug
fixes or new features. Share feedback and suggestions for improvements.

## License: GPLv2 or later

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public
License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.