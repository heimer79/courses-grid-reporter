<?php
function render_courses_grid_reporter_block( $attributes ) {
    $endpoint_url = $attributes['endpointUrl'];

    // Fetch the JSON data from the endpoint
    $response = wp_remote_get( esc_url( $endpoint_url ) );
    if ( is_wp_error( $response ) ) {
        error_log('Courses Grid: Error fetching data - ' . $response->get_error_message());
        return '<p class="text-red-500">Unable to retrieve courses data.</p>';
    }

    $body = wp_remote_retrieve_body( $response );
    $courses = json_decode( $body, true );

    // Log the raw data for debugging
    error_log('Courses Grid: Raw data received - ' . print_r($courses, true));

    if ( !is_array( $courses ) || empty( $courses ) ) {
        error_log('Courses Grid: Invalid or empty data received');
        return '<p class="text-red-500">No courses found or invalid data received.</p>';
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
    <div class="courses-grid w-full overflow-x-auto shadow-md rounded-lg relative">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-100">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer hover:bg-blue-200" onclick="sortTable(0)">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer hover:bg-blue-200" onclick="sortTable(1)">Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer hover:bg-blue-200" onclick="sortTable(2)">Course Code</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer hover:bg-blue-200" onclick="sortTable(3)">Workflow State</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer hover:bg-blue-200" onclick="sortTable(4)">Start Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-800 uppercase tracking-wider cursor-pointer hover:bg-blue-200" onclick="sortTable(5)">End Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ( $courses as $course ) : ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo esc_html( $course['id'] ?? 'N/A' ); ?></td>
                        <td class="px-6 py-4 whitespace-normal text-sm text-gray-900"><?php echo esc_html( $course['name'] ?? 'N/A' ); ?></td>
                        <td class="px-6 py-4 whitespace-normal text-sm text-gray-900"><?php echo esc_html( $course['course_code'] ?? 'N/A' ); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo esc_html( $course['workflow_state'] ?? 'N/A' ); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo esc_html( $course['start_at'] ?? 'N/A' ); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo esc_html( $course['end_at'] ?? 'N/A' ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="absolute bottom-2 right-2 text-2xl text-blue-500 animate-bounce">→</div>
    </div>
    <div class="mt-4 flex justify-center">
        <button id="report-button" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
            Generate Report
        </button>
    </div>

    <script>
    function sortTable(columnIndex) {
        const table = document.querySelector(".courses-grid table");
        const tbody = table.querySelector("tbody");
        const rows = Array.from(tbody.querySelectorAll("tr"));
        const sortedRows = rows.sort((a, b) => {
            const aText = a.children[columnIndex].innerText.toLowerCase();
            const bText = b.children[columnIndex].innerText.toLowerCase();
            return aText.localeCompare(bText);
        });
        tbody.innerHTML = '';
        sortedRows.forEach(row => tbody.appendChild(row));
    }

    document.getElementById('report-button').addEventListener('click', function() {
        const availableCoursesCount = <?php echo $available_count; ?>;
        alert(`There are ${availableCoursesCount} courses available.`);
    });
    </script>
    <?php
    return ob_get_clean();
}