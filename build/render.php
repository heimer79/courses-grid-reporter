<?php
function render_courses_grid_reporter_block( $attributes ) {
    $endpoint_url = $attributes['endpointUrl'];

    // Fetch the JSON data from the endpoint
    $response = wp_remote_get( esc_url( $endpoint_url ) );
    if ( is_wp_error( $response ) ) {
        error_log('Courses Grid: Error fetching data - ' . $response->get_error_message());
        return '<p>Unable to retrieve courses data.</p>';
    }

    $body = wp_remote_retrieve_body( $response );
    $courses = json_decode( $body, true );
    
    // Log the raw data for debugging
    error_log('Courses Grid: Raw data received - ' . print_r($courses, true));

    if ( !is_array( $courses ) || empty( $courses ) ) {
        error_log('Courses Grid: Invalid or empty data received');
        return '<p>No courses found or invalid data received.</p>';
    }

    // Filter and count available courses
    $available_courses = array_filter($courses, function($course) {
        return isset($course['workflow_state']) && $course['workflow_state'] === 'available';
    });
    $available_count = count($available_courses);

    // Log the count of available courses
    error_log('Courses Grid: Number of available courses - ' . $available_count);

    ob_start();
    ?>
    <div class="courses-grid w-full overflow-x-auto">
        <div class="min-w-full bg-gray-100 text-left text-sm font-semibold text-gray-600 uppercase tracking-wider">
            <div class="grid grid-cols-6 bg-gray-200">
                <div class="p-2 cursor-pointer hover:text-blue-500" onclick="sortTable(0)">ID</div>
                <div class="p-2 cursor-pointer hover:text-blue-500" onclick="sortTable(1)">Name</div>
                <div class="p-2 cursor-pointer hover:text-blue-500" onclick="sortTable(2)">Course Code</div>
                <div class="p-2 cursor-pointer hover:text-blue-500" onclick="sortTable(3)">Workflow State</div>
                <div class="p-2 cursor-pointer hover:text-blue-500" onclick="sortTable(4)">Start Date</div>
                <div class="p-2 cursor-pointer hover:text-blue-500" onclick="sortTable(5)">End Date</div>
            </div>
        </div>
        <div class="bg-white divide-y divide-gray-200">
            <?php foreach ( $courses as $course ) : ?>
                <div class="grid grid-cols-6">
                    <div class="p-2 text-gray-900"><?php echo esc_html( $course['id'] ?? 'N/A' ); ?></div>
                    <div class="p-2 text-gray-900"><?php echo esc_html( $course['name'] ?? 'N/A' ); ?></div>
                    <div class="p-2 text-gray-900"><?php echo esc_html( $course['course_code'] ?? 'N/A' ); ?></div>
                    <div class="p-2 text-gray-900"><?php echo esc_html( $course['workflow_state'] ?? 'N/A' ); ?></div>
                    <div class="p-2 text-gray-900"><?php echo esc_html( $course['start_at'] ?? 'N/A' ); ?></div>
                    <div class="p-2 text-gray-900"><?php echo esc_html( $course['end_at'] ?? 'N/A' ); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <button id="report-button" class="mt-4 p-2 bg-blue-500 text-white">Generate Report</button>

    <script>
    function sortTable(columnIndex) {
        const table = document.querySelector(".courses-grid .bg-white");
        const rows = Array.from(table.children);
        const sortedRows = rows.sort((a, b) => {
            const aText = a.children[columnIndex].innerText.toLowerCase();
            const bText = b.children[columnIndex].innerText.toLowerCase();
            return aText.localeCompare(bText);
        });
        table.innerHTML = '';
        table.append(...sortedRows);
    }

    document.getElementById('report-button').addEventListener('click', function() {
        const availableCoursesCount = <?php echo $available_count; ?>;
        alert(`There are ${availableCoursesCount} courses available.`);
    });
    </script>
    <?php
    return ob_get_clean();
}