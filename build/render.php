<?php
/**
 * Renders the courses grid reporter block.
 *
 * @param array $attributes Block attributes.
 * @return string HTML content for the block.
 */
function render_courses_grid_reporter_block( $attributes ) {
    $endpoint_url = $attributes['endpointUrl'];
    $report_email = isset( $attributes['reportEmail'] ) ? $attributes['reportEmail'] : '';

    // Fetch the JSON data from the specified endpoint.
    $response = wp_remote_get( esc_url_raw( $endpoint_url ) );

    // Check if the API request returned an error.
    if ( is_wp_error( $response ) ) {
        return '<p class="text-red-500">Error fetching data from the API.</p>';
    }

    // Retrieve and decode the response body.
    $body    = wp_remote_retrieve_body( $response );
    $courses = json_decode( $body, true );

    // Validate the decoded data.
    if ( ! is_array( $courses ) || empty( $courses ) ) {
        return '<p class="text-red-500">No courses found or invalid data received.</p>';
    }

    // Filter courses that are in the 'available' workflow state.
    $available_courses = array_filter( $courses, function( $course ) {
        return isset( $course['workflow_state'] ) && 'available' === $course['workflow_state'];
    } );

    // Count the number of available courses.
    $available_count = count( $available_courses );

    ob_start();
    ?>
    <div class="courses-grid w-full overflow-x-auto shadow-md rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-200">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider cursor-pointer" onclick="sortTable(0)">
                        ID <span class="sort-icon">↕</span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider cursor-pointer" onclick="sortTable(1)">
                        Name <span class="sort-icon">↕</span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider cursor-pointer" onclick="sortTable(2)">
                        Course Code <span class="sort-icon">↕</span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider cursor-pointer" onclick="sortTable(3)">
                        Workflow State <span class="sort-icon">↕</span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider cursor-pointer" onclick="sortTable(4)">
                        Start Date <span class="sort-icon">↕</span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-black uppercase tracking-wider cursor-pointer" onclick="sortTable(5)">
                        End Date <span class="sort-icon">↕</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ( $courses as $course ) : ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo esc_html( isset( $course['id'] ) ? $course['id'] : 'N/A' ); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-normal text-sm text-gray-900">
                            <?php echo esc_html( isset( $course['name'] ) ? $course['name'] : 'N/A' ); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-normal text-sm text-gray-900">
                            <?php echo esc_html( isset( $course['course_code'] ) ? $course['course_code'] : 'N/A' ); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo esc_html( isset( $course['workflow_state'] ) ? $course['workflow_state'] : 'N/A' ); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo esc_html( isset( $course['start_at'] ) ? $course['start_at'] : 'N/A' ); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <?php echo esc_html( isset( $course['end_at'] ) ? $course['end_at'] : 'N/A' ); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="mt-4 flex justify-between items-center">
        <p class="text-gray-600">
            <?php printf( 'Total courses: %d', count( $courses ) ); ?>
        </p>
        <button id="report-button" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
            Generate Report
        </button>
    </div>

    <script>
        let currentSortColumn = -1;
        let isAscending = true;

        /**
         * Sorts the table based on the specified column index.
         *
         * @param {number} columnIndex - The index of the column to sort.
         */
        function sortTable(columnIndex) {
            const table = document.querySelector(".courses-grid table");
            const tbody = table.querySelector("tbody");
            const rows = Array.from(tbody.querySelectorAll("tr"));
            const headers = table.querySelectorAll("th");

            // Reset sort icons for all headers.
            headers.forEach(header => {
                header.querySelector('.sort-icon').textContent = '↕';
            });

            // Toggle sort direction if the same column is clicked.
            if (currentSortColumn === columnIndex) {
                isAscending = !isAscending;
            } else {
                isAscending = true;
            }

            currentSortColumn = columnIndex;

            // Update the sort icon for the active header.
            headers[columnIndex].querySelector('.sort-icon').textContent = isAscending ? '↑' : '↓';

            // Perform sorting based on the column data type.
            const sortedRows = rows.sort((a, b) => {
                const aValue = a.children[columnIndex].innerText;
                const bValue = b.children[columnIndex].innerText;

                // Attempt to parse values as dates.
                const aDate = new Date(aValue);
                const bDate = new Date(bValue);

                if (!isNaN(aDate) && !isNaN(bDate)) {
                    return isAscending ? aDate - bDate : bDate - aDate;
                }

                // Attempt to parse values as numbers.
                if (!isNaN(aValue) && !isNaN(bValue)) {
                    return isAscending ? aValue - bValue : bValue - aValue;
                }

                // Default to string comparison.
                return isAscending ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
            });

            // Re-render the sorted rows.
            tbody.innerHTML = '';
            sortedRows.forEach(row => tbody.appendChild(row));
        }

        // Event listener for the report generation button.
        document.getElementById('report-button').addEventListener('click', function() {
            const availableCoursesCount = <?php echo esc_js( $available_count ); ?>;
            const reportEmail = '<?php echo esc_js( $report_email ); ?>';

            // Prepare the data for the AJAX request.
            const data = new URLSearchParams();
            data.append('action', 'send_courses_report');
            data.append('count', availableCoursesCount);
            data.append('email', reportEmail);

            // Send the AJAX request to generate and send the report.
            fetch('<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: data.toString(),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Report sent successfully! There are ${availableCoursesCount} courses available.`);
                } else {
                    alert('Error sending report: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while sending the report: ' + error.message);
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

// Register AJAX actions for logged-in and non-logged-in users.
add_action( 'wp_ajax_send_courses_report', 'send_courses_report' );
add_action( 'wp_ajax_nopriv_send_courses_report', 'send_courses_report' );

/**
 * Handles the AJAX request to send the courses report via email.
 */
function send_courses_report() {
    // Verify and sanitize POST parameters.
    $count = isset( $_POST['count'] ) ? intval( $_POST['count'] ) : 0;
    $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

    // Log the attempt to send the report.
    error_log( "Attempting to send report. Count: $count, Email: $email" );

    // Check if the email address is provided.
    if ( empty( $email ) ) {
        error_log( 'Error: No email address provided.' );
        wp_send_json( array( 'success' => false, 'message' => 'No email address provided.' ) );
        return;
    }

    // Prepare the email content.
    $subject = 'Courses Availability Report';
    $message = sprintf( 'There are currently %d courses available.', $count );
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );

    // Log the email sending attempt.
    error_log( "Sending mail to $email with subject: $subject" );

    // Send the email using WordPress' wp_mail function.
    $sent = wp_mail( $email, $subject, $message, $headers );

    // Check if the email was sent successfully and respond accordingly.
    if ( $sent ) {
        error_log( 'Email sent successfully.' );
        wp_send_json( array( 'success' => true, 'message' => 'Report sent successfully.' ) );
    } else {
        error_log( 'Failed to send email. wp_mail() returned false.' );
        wp_send_json( array( 'success' => false, 'message' => 'Failed to send email. Please check the server logs.' ) );
    }
}
